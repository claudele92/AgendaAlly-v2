import 'dart:async';

abstract interface class TokenStore {
  Future<String?> read();
  Future<void> write(String token);
  Future<void> delete();
}

/// Only the secure store is persistent token authority. The synchronous token
/// is an in-process mirror for the existing native UI, never a preference.
class SessionCoordinator {
  static SessionCoordinator? currentInstance;
  static SessionCoordinator get instance => currentInstance!;
  static set instance(SessionCoordinator value) => currentInstance = value;

  SessionCoordinator({
    required this.store,
    required this.clearCustomerState,
    required this.setInvalidated,
    required this.isInvalidated,
  });

  final TokenStore store;
  final Future<void> Function() clearCustomerState;
  final Future<void> Function(bool) setInvalidated;
  final bool Function() isInvalidated;
  final _changes = StreamController<int>.broadcast(sync: true);
  Future<void> _tail = Future.value();
  String _token = '';
  int _generation = 0;
  bool _ready = false;
  bool _ending = false;
  bool cleanupFailed = false;
  bool logoutPending = false;
  String? logoutMessage;
  int _identityPending = 0;
  bool get identityPending => _identityPending != 0;

  String get token => _ready && !_ending ? _token : '';
  int get generation => _generation;
  Stream<int> get invalidations => _changes.stream;
  bool current(int generation) =>
      _ready && !_ending && !cleanupFailed && generation == _generation;

  Future<void> _serial(Future<void> Function() action) {
    final next = _tail.then((_) => action());
    _tail = next.catchError((Object _) {});
    return next;
  }

  Future<void> initialize({required bool legacyTokenPresent}) async {
    if (_ready) _generation++;
    _ready = false;
    _token = '';
    try {
      // Old plaintext credentials are intentionally invalidated, not copied.
      // The customer must sign in again after this security migration.
      if (legacyTokenPresent || isInvalidated()) {
        await setInvalidated(true);
        await clearCustomerState();
        await store.delete();
      } else {
        _token = await store.read() ?? '';
        if (_token.isEmpty) await clearCustomerState();
      }
      _ready = true;
    } catch (_) {
      _token = '';
      _ready = true;
      await invalidate();
    }
  }

  Future<bool> establish(
    String value, {
    required int expectedGeneration,
  }) async {
    if (value.isEmpty ||
        logoutPending ||
        identityPending ||
        cleanupFailed ||
        !current(expectedGeneration) ||
        _token.isNotEmpty) {
      return false;
    }
    var accepted = false;
    try {
      await _serial(() async {
        if (!current(expectedGeneration)) return;
        await store.write(value);
        if (!current(expectedGeneration)) return;
        await setInvalidated(false);
        if (!current(expectedGeneration)) return;
        _token = value;
        accepted = true;
      });
    } catch (_) {
      await invalidate();
    }
    return accepted;
  }

  /// In-memory invalidation happens before any awaited operation. A durable,
  /// non-secret tombstone prevents reuse on restart if secure deletion fails.
  Future<void> invalidate({bool notify = true}) {
    _token = '';
    _generation++;
    _ending = true;
    final epoch = _generation;
    return _serial(() async {
      cleanupFailed = false;
      for (final action in <Future<void> Function()>[
        () => setInvalidated(true),
        clearCustomerState,
        store.delete,
      ]) {
        try {
          await action();
        } catch (_) {
          cleanupFailed = true;
        }
      }
      if (_generation == epoch) {
        _ending = false;
        if (notify) _changes.add(epoch);
      }
    });
  }

  void publishInvalidation() => _changes.add(_generation);

  /// An SDK sign-in can complete after local sign-out. Until it settles, no
  /// new Customer token may be committed, so cleanup cannot sign another
  /// established Customer out. Clear the late identity before releasing it.
  Future<T> runIdentity<T>(Future<T> Function() action) async {
    final epoch = _generation;
    _identityPending++;
    try {
      return await action();
    } finally {
      if (!current(epoch)) {
        await _serial(() async {
          try {
            await clearCustomerState();
          } catch (_) {
            cleanupFailed = true;
          }
        });
      }
      _identityPending--;
      if (epoch != _generation || cleanupFailed) publishInvalidation();
    }
  }
}
