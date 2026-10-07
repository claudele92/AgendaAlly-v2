import 'dart:async';
import 'package:flutter_test/flutter_test.dart';
import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/infrastructure/local_storage/storage_keys.dart';
import 'package:demand/infrastructure/session/session_coordinator.dart';
import 'package:demand/infrastructure/service/http_service.dart';
import 'package:demand/infrastructure/repository/auth_repository.dart';
import 'session_boundary_test.dart'
    show MemoryTokens, FixtureAdapter, jsonResponse;

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  late MemoryTokens tokens;
  late AuthRepository repository;
  late List<RequestOptions> requests;
  late int status;
  test(
    'existing identity cleanup failure blocks startup and cannot admit a new Customer',
    () async {
      await LocalStorage.init(
        tokenStore: tokens,
        clearIdentity: () async =>
            throw StateError('Synthetic identity cleanup failure'),
      );
      final session = SessionCoordinator.instance;
      expect(session.cleanupFailed, isTrue);
      expect(
        await session.establish(
          'synthetic-B',
          expectedGeneration: session.generation,
        ),
        isFalse,
      );
      expect(LocalStorage.getToken(), isEmpty);
    },
  );
  setUp(() async {
    SharedPreferences.setMockInitialValues({'theme_mode': 'dark'});
    tokens = MemoryTokens();
    await LocalStorage.init(tokenStore: tokens, clearIdentity: () async {});
    requests = [];
    status = 200;
    repository = AuthRepository(
      pushToken: () async => 'synthetic-fcm',
      http: HttpService(
        adapter: FixtureAdapter((request) async {
          requests.add(request);
          return jsonResponse({
            'status': status == 200,
            'message': status == 200 ? 'Success' : 'Invalid or expired code',
            'data': {
              'access_token': 'synthetic-issued',
              'token': 'synthetic-issued',
              'user': {'id': 7, 'email': 'synthetic@example.invalid'},
            },
          }, status: status);
        }),
      ),
    );
  });
  test('login credentials are request body only', () async {
    final result = await repository.login(
      email: 'synthetic@example.invalid',
      phone: '',
      password: 'synthetic-password',
    );
    expect(result.isLeft(), isTrue);
    final request = requests.single;
    expect(request.path, '/api/v1/auth/login');
    expect(request.queryParameters, isEmpty);
    expect(request.data, {
      'email': 'synthetic@example.invalid',
      'password': 'synthetic-password',
    });
    expect(request.uri.toString().contains('synthetic-password'), isFalse);
  });
  test(
    'email registration uses body and ordinary verification binds email plus six-digit otp',
    () async {
      await repository.sigUpWithEmail(email: 'synthetic@example.invalid');
      expect(requests.single.data, {'email': 'synthetic@example.invalid'});
      requests.clear();
      final result = await repository.verifyEmail(
        email: 'synthetic@example.invalid',
        verifyCode: '123456',
      );
      expect(result.isLeft(), isTrue);
      expect(requests.single.method, 'POST');
      expect(requests.single.path, '/api/v1/auth/verify/email');
      expect(requests.single.queryParameters, isEmpty);
      expect(requests.single.data, {
        'email': 'synthetic@example.invalid',
        'otp': '123456',
      });
      expect(LocalStorage.getToken(), 'synthetic-issued');
      expect(LocalStorage.local!.containsKey(StorageKeys.keyToken), isFalse);
    },
  );
  for (final code in [400, 404, 422, 429]) {
    test(
      'invalid/expired/rate-limited verification response $code does not establish a session',
      () async {
        status = code;
        final result = await repository.verifyEmail(
          email: 'synthetic@example.invalid',
          verifyCode: '123456',
        );
        expect(result.isRight(), isTrue);
        expect(LocalStorage.getToken(), isEmpty);
        expect(tokens.value, isNull);
      },
    );
  }
  test(
    'recovery and reset verification keep recipient and code out of URL',
    () async {
      await repository.forgotPassword(phone: 'synthetic@example.invalid');
      expect(requests.last.path, '/api/v1/auth/forgot/email-password');
      expect(requests.last.queryParameters, isEmpty);
      expect(requests.last.data, {'email': 'synthetic@example.invalid'});
      await repository.forgotPasswordConfirm(
        email: 'synthetic@example.invalid',
        verifyCode: '654321',
      );
      expect(requests.last.path, '/api/v1/auth/forgot/email-password/verify');
      expect(requests.last.queryParameters, isEmpty);
      expect(requests.last.data, {
        'email': 'synthetic@example.invalid',
        'otp': '654321',
      });
      expect(requests.last.uri.toString().contains('654321'), isFalse);
    },
  );
  test(
    'social identity and phone verification transport are body-only without activation',
    () async {
      await repository.loginWithSocial(
        email: 'synthetic@example.invalid',
        displayName: 'Fixture',
        id: 'synthetic-provider-token',
        img: null,
      );
      expect(requests.last.queryParameters, isEmpty);
      expect(requests.last.data['id'], 'synthetic-provider-token');
      await repository.verifyPhone(
        phone: '+1234567890',
        verifyId: 'synthetic-id',
        verifyCode: '654321',
      );
      expect(requests.last.queryParameters, isEmpty);
      expect(requests.last.data['verifyCode'], '654321');
    },
  );
  test(
    'success logout revokes captured bearer and clears local state and preferences narrowly',
    () async {
      await LocalStorage.setToken(
        'synthetic-A',
        expectedGeneration: SessionCoordinator.instance.generation,
      );
      await LocalStorage.local!.setString(StorageKeys.keyUser, '{"id":7}');
      await LocalStorage.local!.setString(StorageKeys.keyGroupUser, '{"id":8}');
      final result = await repository.logout();
      expect(result.isLeft(), isTrue);
      expect(requests.single.path, '/api/v1/auth/logout');
      expect(requests.single.headers['Authorization'], 'Bearer synthetic-A');
      expect(LocalStorage.getToken(), isEmpty);
      expect(tokens.value, isNull);
      expect(LocalStorage.local!.containsKey(StorageKeys.keyUser), isFalse);
      expect(
        LocalStorage.local!.containsKey(StorageKeys.keyGroupUser),
        isFalse,
      );
      expect(LocalStorage.local!.getString('theme_mode'), 'dark');
    },
  );
  test(
    'timeout logout clears local credentials without claiming server revocation',
    () async {
      await LocalStorage.setToken(
        'synthetic-A',
        expectedGeneration: SessionCoordinator.instance.generation,
      );
      final offline = AuthRepository(
        pushToken: () async => 'synthetic-fcm',
        http: HttpService(
          adapter: FixtureAdapter(
            (options) async => throw DioException(
              requestOptions: options,
              type: DioExceptionType.connectionTimeout,
            ),
          ),
        ),
      );
      final result = await offline.logout();
      expect(result.isRight(), isTrue);
      expect(LocalStorage.getToken(), isEmpty);
      expect(tokens.value, isNull);
    },
  );
  test('already-invalid bearer logout remains locally idempotent', () async {
    await repository.logout();
    await repository.logout();
    expect(LocalStorage.getToken(), isEmpty);
    expect(tokens.value, isNull);
  });
  test(
    'pending logout discards private views immediately, deduplicates and blocks new establishment',
    () async {
      final session = SessionCoordinator.instance;
      await LocalStorage.setToken(
        'synthetic-A',
        expectedGeneration: session.generation,
      );
      final entered = Completer<void>();
      final response = Completer<ResponseBody>();
      final events = <int>[];
      final subscription = session.invalidations.listen(events.add);
      final pendingRepository = AuthRepository(
        pushToken: () async => 'synthetic-fcm',
        http: HttpService(
          adapter: FixtureAdapter((_) {
            entered.complete();
            return response.future;
          }),
        ),
      );
      final first = pendingRepository.logout();
      final second = pendingRepository.logout();
      await entered.future;
      expect(identical(first, second), isTrue);
      expect(events, isNotEmpty);
      expect(session.token, isEmpty);
      expect(session.logoutPending, isTrue);
      expect(
        await session.establish(
          'synthetic-B',
          expectedGeneration: session.generation,
        ),
        isFalse,
      );
      response.complete(jsonResponse({'status': true}));
      expect((await first).isLeft(), isTrue);
      await second;
      expect(session.logoutPending, isFalse);
      expect(
        session.logoutMessage,
        'Device signed out. Server sign-out confirmed.',
      );
      await subscription.cancel();
    },
  );
  test(
    'failed local erasure blocks Customer access and is not reported as complete logout',
    () async {
      await LocalStorage.setToken(
        'synthetic-A',
        expectedGeneration: SessionCoordinator.instance.generation,
      );
      tokens.failDelete = true;
      final result = await repository.logout();
      expect(result.isRight(), isTrue);
      expect(LocalStorage.getToken(), isEmpty);
      expect(SessionCoordinator.instance.cleanupFailed, isTrue);
      expect(requests.single.path, '/api/v1/auth/logout');
      expect(
        SessionCoordinator.instance.logoutMessage,
        'Server sign-out confirmed. Device cleanup requires retry.',
      );
      expect(
        SessionCoordinator.instance.current(
          SessionCoordinator.instance.generation,
        ),
        isFalse,
      );
      expect(
        LocalStorage.local!.getBool('customer_session_invalidated'),
        isTrue,
      );
    },
  );
  test(
    'legacy SharedPreferences token is deleted and never copied as ongoing authority',
    () async {
      SharedPreferences.setMockInitialValues({
        StorageKeys.keyToken: 'synthetic-legacy',
        StorageKeys.keyUser: '{"id":7}',
        'theme_mode': 'dark',
      });
      await LocalStorage.init(tokenStore: tokens, clearIdentity: () async {});
      expect(LocalStorage.local!.containsKey(StorageKeys.keyToken), isFalse);
      expect(LocalStorage.local!.containsKey(StorageKeys.keyUser), isFalse);
      expect(LocalStorage.getToken(), isEmpty);
      expect(tokens.value, isNull);
      expect(LocalStorage.local!.getString('theme_mode'), 'dark');
    },
  );
  test(
    'public background-isolate transport does not require an initialized Customer session',
    () async {
      SessionCoordinator.currentInstance = null;
      final http = HttpService(
        adapter: FixtureAdapter((_) async => jsonResponse({'settings': []})),
      );
      final response = await http.client().get('/public/translations');
      expect(response.statusCode, 200);
      await expectLater(
        http.client(requireAuth: true).get('/owned/profile'),
        throwsA(isA<DioException>()),
      );
    },
  );
}
