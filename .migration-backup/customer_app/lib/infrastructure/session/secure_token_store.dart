import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'session_coordinator.dart';

class SecureTokenStore implements TokenStore {
  const SecureTokenStore();

  static const _key = 'agendaally_customer_bearer';
  static const androidOptions = AndroidOptions(
    encryptedSharedPreferences: true,
    resetOnError: false,
    sharedPreferencesName: 'AgendaAllyCustomerSecureStorage',
    preferencesKeyPrefix: 'agendaally.customer.',
  );
  static const iosOptions = IOSOptions(
    accountName: 'agendaally.customer.session',
    accessibility: KeychainAccessibility.unlocked_this_device,
    synchronizable: false,
  );
  static const _storage = FlutterSecureStorage(
    aOptions: androidOptions,
    iOptions: iosOptions,
  );

  @override
  Future<String?> read() => _storage.read(key: _key);
  @override
  Future<void> write(String token) => _storage.write(key: _key, value: token);
  @override
  Future<void> delete() => _storage.delete(key: _key);
}
