import 'dart:async';
import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:demand/infrastructure/session/session_coordinator.dart';
import 'package:demand/infrastructure/session/secure_token_store.dart';
import 'package:demand/infrastructure/service/http_service.dart';
import 'package:demand/infrastructure/service/safe_http_diagnostics.dart';
import 'package:demand/presentation/session_boundary.dart';
import 'package:demand/presentation/session_recovery_page.dart';

class MemoryTokens implements TokenStore {
  String? value;
  bool failRead = false, failWrite = false, failDelete = false;
  Completer<void>? writing;
  @override
  Future<String?> read() async {
    if (failRead) throw StateError('Synthetic storage failure');
    return value;
  }

  @override
  Future<void> write(String token) async {
    if (failWrite) throw StateError('Synthetic storage failure');
    if (writing != null) await writing!.future;
    value = token;
  }

  @override
  Future<void> delete() async {
    if (failDelete) throw StateError('Synthetic storage failure');
    value = null;
  }
}

class FixtureAdapter implements HttpClientAdapter {
  FixtureAdapter(this.respond);
  final Future<ResponseBody> Function(RequestOptions) respond;
  @override
  Future<ResponseBody> fetch(
    RequestOptions options,
    Stream<Uint8List>? stream,
    Future<void>? cancelFuture,
  ) => respond(options);
  @override
  void close({bool force = false}) {}
}

ResponseBody jsonResponse(Object body, {int status = 200}) =>
    ResponseBody.fromString(
      jsonEncode(body),
      status,
      headers: {
        Headers.contentTypeHeader: ['application/json'],
      },
    );

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  late MemoryTokens tokens;
  late SessionCoordinator session;
  late bool tombstone;
  late List<String> cache;

  setUp(() async {
    tokens = MemoryTokens();
    tombstone = false;
    cache = [];
    session = SessionCoordinator(
      store: tokens,
      clearCustomerState: () async => cache.clear(),
      setInvalidated: (value) async => tombstone = value,
      isInvalidated: () => tombstone,
    );
    SessionCoordinator.instance = session;
    await session.initialize(legacyTokenPresent: false);
  });

  Future<void> signIn([String token = 'synthetic-customer-A-bearer']) async {
    expect(
      await session.establish(token, expectedGeneration: session.generation),
      isTrue,
    );
  }

  test(
    'late SDK identity is cleared after logout and cannot overlap a new Customer',
    () async {
      final provider = Completer<String>();
      final pending = session.runIdentity(() => provider.future);
      await session.invalidate();
      expect(session.identityPending, isTrue);
      expect(
        await session.establish(
          'synthetic-B',
          expectedGeneration: session.generation,
        ),
        isFalse,
      );
      cache.add('synthetic-late-SDK-identity');
      provider.complete('synthetic-provider-result');
      await pending;
      expect(cache, isEmpty);
      expect(session.identityPending, isFalse);
      expect(session.token, isEmpty);
      await signIn('synthetic-B');
      expect(session.token, 'synthetic-B');
    },
  );

  test(
    'secure authority writes, reads and deletes; memory is cleared immediately',
    () async {
      await signIn();
      expect(tokens.value, session.token);
      final pending = session.invalidate();
      expect(session.token, isEmpty);
      await pending;
      expect(tokens.value, isNull);
      expect(tombstone, isTrue);
    },
  );
  test('legacy plaintext session is invalidated rather than copied', () async {
    tokens.value = 'synthetic-old-secure-value';
    cache.add('customer-A-wallet');
    await session.initialize(legacyTokenPresent: true);
    expect(tokens.value, isNull);
    expect(session.token, isEmpty);
    expect(cache, isEmpty);
    expect(tombstone, isTrue);
  });
  test(
    'restart reloads a valid secure token, but not an invalidated token',
    () async {
      tokens.value = 'synthetic-valid-restart';
      await session.initialize(legacyTokenPresent: false);
      expect(session.token, tokens.value);
      tombstone = true;
      await session.initialize(legacyTokenPresent: false);
      expect(session.token, isEmpty);
      expect(tokens.value, isNull);
    },
  );
  test('secure write failure fails closed', () async {
    tokens.failWrite = true;
    expect(
      await session.establish(
        'synthetic-new',
        expectedGeneration: session.generation,
      ),
      isFalse,
    );
    expect(session.token, isEmpty);
    expect(tombstone, isTrue);
  });
  test(
    'failed secure deletion preserves restart tombstone and blocks reuse',
    () async {
      await signIn();
      tokens.failDelete = true;
      await session.invalidate();
      expect(session.token, isEmpty);
      expect(session.cleanupFailed, isTrue);
      expect(tombstone, isTrue);
      expect(
        await session.establish(
          'synthetic-B',
          expectedGeneration: session.generation,
        ),
        isFalse,
      );
      await session.initialize(legacyTokenPresent: false);
      expect(session.token, isEmpty);
    },
  );
  test('storage read error does not restore a session', () async {
    tokens.failRead = true;
    await session.initialize(legacyTokenPresent: false);
    expect(session.token, isEmpty);
    expect(tombstone, isTrue);
  });
  test(
    'logout racing a secure write cannot resurrect the credential',
    () async {
      tokens.writing = Completer<void>();
      final write = session.establish(
        'synthetic-racing',
        expectedGeneration: session.generation,
      );
      await Future<void>.delayed(Duration.zero);
      final clear = session.invalidate();
      tokens.writing!.complete();
      expect(await write, isFalse);
      await clear;
      expect(tokens.value, isNull);
      expect(session.token, isEmpty);
    },
  );
  test(
    'account switching requires clearing A and cannot reuse an old intent',
    () async {
      await signIn();
      cache.add('customer-A-private-state');
      final old = session.generation;
      expect(
        await session.establish('synthetic-B', expectedGeneration: old),
        isFalse,
      );
      await session.invalidate();
      expect(
        await session.establish('synthetic-stale', expectedGeneration: old),
        isFalse,
      );
      await signIn('synthetic-customer-B-bearer');
      expect(cache, isEmpty);
      expect(session.token, 'synthetic-customer-B-bearer');
    },
  );
  test('401 invalidates token and Customer state centrally', () async {
    await signIn();
    cache.add('wallet-A');
    final dio = HttpService(
      session: session,
      adapter: FixtureAdapter(
        (_) async => jsonResponse({'message': 'Unauthenticated.'}, status: 401),
      ),
    ).client(requireAuth: true);
    await expectLater(dio.get('/owned/profile'), throwsA(isA<DioException>()));
    expect(tokens.value, isNull);
    expect(cache, isEmpty);
    expect(session.token, isEmpty);
  });
  test('403 preserves the authenticated session', () async {
    await signIn();
    final original = session.token;
    final dio = HttpService(
      session: session,
      adapter: FixtureAdapter(
        (_) async => jsonResponse({'message': 'Forbidden.'}, status: 403),
      ),
    ).client(requireAuth: true);
    await expectLater(dio.get('/forbidden'), throwsA(isA<DioException>()));
    expect(session.token, original);
    expect(tokens.value, original);
  });
  test(
    'public login 401 does not destroy another authenticated session',
    () async {
      await signIn();
      final original = session.token;
      final dio = HttpService(
        session: session,
        adapter: FixtureAdapter(
          (_) async =>
              jsonResponse({'message': 'Invalid credentials.'}, status: 401),
        ),
      ).client();
      await expectLater(dio.post('/auth/login'), throwsA(isA<DioException>()));
      expect(session.token, original);
    },
  );
  test('late A response is rejected after B signs in', () async {
    await signIn();
    final response = Completer<ResponseBody>();
    final dio = HttpService(
      session: session,
      adapter: FixtureAdapter((_) => response.future),
    ).client(requireAuth: true);
    final pending = dio.get('/owned/profile');
    final assertion = expectLater(pending, throwsA(isA<DioException>()));
    await Future<void>.delayed(Duration.zero);
    await session.invalidate();
    await signIn('synthetic-B');
    response.complete(jsonResponse({'private_user': 'A'}));
    await assertion;
    expect(session.token, 'synthetic-B');
  });
  test('late A 401 cannot log B out', () async {
    await signIn();
    final response = Completer<ResponseBody>();
    final dio = HttpService(
      session: session,
      adapter: FixtureAdapter((_) => response.future),
    ).client(requireAuth: true);
    final assertion = expectLater(
      dio.get('/owned/profile'),
      throwsA(isA<DioException>()),
    );
    await Future<void>.delayed(Duration.zero);
    await session.invalidate();
    await signIn('synthetic-B');
    response.complete(
      jsonResponse({'message': 'Unauthenticated.'}, status: 401),
    );
    await assertion;
    expect(session.token, 'synthetic-B');
  });
  test(
    'HTTP metadata and error stringification never expose synthetic sentinels',
    () async {
      const sentinels = [
        'SYNTH_PASSWORD_714',
        'SYNTH_OTP_815',
        'SYNTH_BEARER_916',
        'SYNTH_AUTHORIZATION_117',
        'SYNTH_SIGNED_URL_218',
        'SYNTH_PRIVATE_RESPONSE_319',
      ];
      final logs = <String>[];
      final dio = HttpService(
        session: session,
        diagnostics: SafeHttpDiagnostics(enabled: true, sink: logs.add),
        adapter: FixtureAdapter(
          (_) async => jsonResponse({
            'token': sentinels[2],
            'message': sentinels[5],
          }, status: 422),
        ),
      ).client();
      try {
        await dio.post(
          '/auth/${sentinels[4]}',
          queryParameters: {'signed': sentinels[4]},
          data: {'password': sentinels[0], 'otp': sentinels[1]},
          options: Options(headers: {'Authorization': sentinels[3]}),
        );
      } catch (error) {
        logs.add(error.toString());
      }
      expect(logs, contains('HTTP request POST'));
      expect(logs, contains('HTTP error POST 422'));
      for (final sentinel in sentinels) {
        expect(logs.join('\n').contains(sentinel), isFalse);
      }
    },
  );
  test(
    'diagnostics are off by default; no response body or URL is logged on success',
    () async {
      final logs = <String>[];
      final dio = HttpService(
        session: session,
        diagnostics: SafeHttpDiagnostics(sink: logs.add),
        adapter: FixtureAdapter(
          (_) async => jsonResponse({'token': 'synthetic-private'}),
        ),
      ).client();
      await dio.get('/signed/synthetic-private');
      expect(logs, isEmpty);
    },
  );
  test(
    'Android configuration is isolated/encrypted; plugin method plumbing writes reads deletes',
    () async {
      // This checks declared Android configuration and Linux mock plumbing.
      // dart:io Platform remains Linux; this is not Android native acceptance.
      final options = SecureTokenStore.androidOptions.params;
      expect(options['encryptedSharedPreferences'], 'true');
      expect(options['resetOnError'], 'false');
      expect(
        options['sharedPreferencesName'],
        'AgendaAllyCustomerSecureStorage',
      );
      final calls = <MethodCall>[];
      const channel = MethodChannel(
        'plugins.it_nomads.com/flutter_secure_storage',
      );
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(channel, (call) async {
            calls.add(call);
            return call.method == 'read' ? 'synthetic-native' : null;
          });
      try {
        const store = SecureTokenStore();
        await store.write('synthetic-native');
        expect(await store.read(), 'synthetic-native');
        await store.delete();
        expect(calls.map((c) => c.method), ['write', 'read', 'delete']);
        for (final call in calls) {
          expect(call.arguments['key'], 'agendaally_customer_bearer');
        }
      } finally {
        TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
            .setMockMethodCallHandler(channel, null);
      }
    },
  );
  test(
    'iOS configuration requests non-synchronizing this-device Keychain accessibility',
    () {
      final options = SecureTokenStore.iosOptions.params;
      expect(options['accessibility'], 'unlocked_this_device');
      expect(options['synchronizable'], 'false');
      expect(options['accountName'], 'agendaally.customer.session');
    },
  );
  testWidgets(
    'actual session boundary discards previous Navigator route state',
    (tester) async {
      await tester.runAsync(() => signIn());
      await tester.pumpWidget(
        SessionBoundary(
          builder: (_, epoch) => MaterialApp(
            home: Scaffold(
              body: Text(epoch == 0 ? 'Customer A page' : 'Fresh login'),
            ),
          ),
        ),
      );
      expect(find.text('Customer A page'), findsOneWidget);
      await tester.runAsync(() => session.invalidate());
      await tester.pump();
      expect(find.text('Customer A page'), findsNothing);
      expect(find.text('Fresh login'), findsOneWidget);
    },
  );
  testWidgets('failed cleanup exposes only the actual blocked recovery page', (
    tester,
  ) async {
    await tester.runAsync(() => signIn());
    await tester.pumpWidget(
      SessionBoundary(
        builder: (_, epoch) => MaterialApp(
          home: session.cleanupFailed
              ? const SessionRecoveryPage()
              : const Scaffold(body: Text('Customer routes')),
        ),
      ),
    );
    tokens.failDelete = true;
    await tester.runAsync(() => session.invalidate());
    await tester.pump();
    expect(find.text('Customer routes'), findsNothing);
    expect(
      find.text(
        'Device sign-out could not finish. Customer access is blocked.',
      ),
      findsOneWidget,
    );
    expect(find.text('Retry device cleanup'), findsOneWidget);
  });
}
