import 'package:dartz/dartz.dart';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';

import '../../domain/di/dependency_manager.dart';
import '../../domain/interface/auth.dart';
import '../../domain/model/model/user_model.dart';
import '../../domain/model/response/login_response.dart';
import '../../domain/model/response/profile_response.dart';
import '../../domain/model/response/register_response.dart';
import '../../domain/model/response/verify_phone_response.dart';
import '../firebase/firebase_service.dart';
import '../local_storage/local_storage.dart';
import '../service/services.dart';
import '../session/session_coordinator.dart';
import '../service/http_service.dart';

class AuthRepository implements AuthInterface {
  AuthRepository({HttpService? http, Future<String> Function()? pushToken})
    : _http = http,
      _pushToken = pushToken;
  final HttpService? _http;
  final Future<String> Function()? _pushToken;
  Future<dynamic>? _logoutInFlight;
  HttpService get transport => _http ?? dioHttp;

  @override
  Future<Either<LoginResponse, dynamic>> login({
    required String email,
    required String phone,
    required String password,
  }) async {
    dynamic data;
    if (AppHelpers.checkPhone(phone)) {
      data = {'phone': AppHelpers.phoneDeFormat(phone), 'password': password};
    } else {
      data = {"email": email, 'password': password};
    }

    try {
      final client = transport.client(requireAuth: false);
      final response = await client.post('/api/v1/auth/login', data: data);
      return left(LoginResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('Authentication request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<LoginResponse, dynamic>> loginWithSocial({
    required String email,
    required String displayName,
    required String id,
    required String? img,
  }) async {
    final data = {
      'email': email,
      'name': displayName,
      'id': id,
      if (img != null) 'img': img,
    };
    try {
      final client = transport.client(requireAuth: false);
      final response = await client.post(
        '/api/v1/auth/google/callback',
        data: data,
      );
      return left(LoginResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('Social authentication request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future updateFirebaseToken() async {
    try {
      final String token;
      try {
        token = await FirebaseService.getFcmToken();
      } catch (e) {
        return;
      }
      final data = {'firebase_token': token};

      final client = transport.client(requireAuth: true);
      await client.post(
        '/api/v1/dashboard/user/profile/firebase/token/update',
        data: data,
      );
    } catch (e) {
      debugPrint('Push registration request failed.');
    }
  }

  @override
  Future<Either<VerifyData, dynamic>> sigUpWithPhone({
    required UserModel user,
  }) async {
    try {
      final client = transport.client(requireAuth: false);
      final res = await client.post(
        '/api/v1/auth/verify/phone',
        data: user.toJson(),
      );
      return left(VerifyPhoneResponse.fromJson(res.data).data ?? VerifyData());
    } catch (e) {
      debugPrint('Phone authentication request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future logout() => _logoutInFlight ??= _performLogout().whenComplete(() {
    _logoutInFlight = null;
  });

  Future<dynamic> _performLogout() async {
    final session = SessionCoordinator.instance;
    final bearer = session.token;
    session.logoutPending = true;
    session.logoutMessage = null;
    // Block local use immediately, including when optional Firebase/transport
    // work fails. The snapshot is used only for this bounded revocation call.
    await session.invalidate();
    try {
      final client = transport.client(requireAuth: false, forLogout: true);
      Object? data;
      try {
        final fcm = await (_pushToken?.call() ?? FirebaseService.getFcmToken())
            .timeout(const Duration(seconds: 3));
        data = {"firebase_token": fcm};
      } catch (_) {}
      await client.post(
        '/api/v1/auth/logout',
        data: data,
        options: Options(
          headers: {if (bearer.isNotEmpty) 'Authorization': 'Bearer $bearer'},
        ),
      );
      if (session.cleanupFailed) {
        session.logoutMessage =
            'Server sign-out confirmed. Device cleanup requires retry.';
        return right(session.logoutMessage);
      }
      session.logoutMessage = 'Device signed out. Server sign-out confirmed.';
      return left(true);
    } catch (e) {
      if (session.cleanupFailed) {
        session.logoutMessage =
            'Device cleanup is incomplete. Customer access is blocked.';
        return right(
          'Device sign-out could not finish. Customer access is blocked. Server revocation was not confirmed.',
        );
      }
      session.logoutMessage =
          'Device signed out. Server sign-out was not confirmed.';
      return right(
        'Local session cleared. Server sign-out could not be confirmed.',
      );
    } finally {
      session.logoutPending = false;
      session.publishInvalidation();
    }
  }

  @override
  Future deleteAccount() async {
    try {
      final client = transport.client(requireAuth: true);
      await client.delete('/api/v1/dashboard/user/profile/delete');
      await LocalStorage.clear();
      return left(true);
    } catch (e) {
      debugPrint('Account deletion request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<bool, dynamic>> checkPhone({required String phone}) async {
    final data = {'phone': AppHelpers.phoneDeFormat(phone)};
    try {
      final client = transport.client(requireAuth: false);
      final response = await client.post(
        '/api/v1/auth/check/phone',
        data: data,
      );
      return left(response.data?["data"]?["exist"] ?? false);
    } catch (e) {
      debugPrint('Phone check request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<LoginResponse, dynamic>> forgotPasswordAfter({
    required String phone,
    required String verificationId,
    required String password,
  }) async {
    final data = {
      'phone': AppHelpers.phoneDeFormat(phone),
      "id": verificationId,
      "type": "firebase",
      "password": password,
    };
    try {
      final client = transport.client(requireAuth: false);
      final res = await client.post(
        '/api/v1/auth/forgot/password/confirm',
        data: data,
      );
      return left(LoginResponse.fromJson(res.data));
    } catch (e) {
      debugPrint('Password reset request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<RegisterResponse, dynamic>> sendOtp({
    required String phone,
  }) async {
    final data = {'phone': AppHelpers.phoneDeFormat(phone)};
    try {
      final client = transport.client(requireAuth: false);
      final response = await client.post('/api/v1/auth/register', data: data);
      return left(RegisterResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('Verification request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<RegisterResponse, dynamic>> forgotPassword({
    required String phone,
  }) async {
    final data = {
      if (AppValidators.isEmail(phone)) "email": phone,
      if (!AppValidators.isEmail(phone))
        "phone": AppHelpers.phoneDeFormat(phone),
    };
    try {
      final client = transport.client(requireAuth: false);
      final response = await client.post(
        AppValidators.isEmail(phone)
            ? '/api/v1/auth/forgot/email-password'
            : '/api/v1/auth/forgot/password',
        data: data,
      );
      return left(RegisterResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('Password recovery request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future updateSetting() async {
    try {
      final client = transport.client(requireAuth: true);
      client.put(
        '/api/v1/dashboard/user/profile/lang/update',
        queryParameters: {'lang': LocalStorage.getLanguage()?.locale},
      );
      client.put(
        '/api/v1/dashboard/user/profile/currency/update',
        queryParameters: {
          'currency_id': LocalStorage.getSelectedCurrency()?.id,
        },
      );
      return left(true);
    } catch (e) {
      debugPrint('Account settings request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<bool, dynamic>> sigUpWithEmail({required String email}) async {
    final data = {'email': email};
    try {
      final client = transport.client(requireAuth: false);
      await client.post('/api/v1/auth/register', data: data);
      return left(true);
    } catch (e) {
      debugPrint('Registration request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<bool, dynamic>> verifyEmail({
    required String verifyCode,
    required String email,
  }) async {
    final generation = SessionCoordinator.instance.generation;
    try {
      final client = transport.client(requireAuth: false);
      final res = await client.post(
        '/api/v1/auth/verify/email',
        data: {'email': email.trim(), 'otp': verifyCode},
      );
      final accepted = await LocalStorage.setToken(
        res.data["data"]["access_token"],
        expectedGeneration: generation,
      );
      return accepted
          ? left(true)
          : right('Session changed. Please sign in again.');
    } catch (e) {
      debugPrint('Email verification request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<VerifyData, dynamic>> forgotPasswordConfirm({
    required String verifyCode,
    required String email,
  }) async {
    try {
      final client = transport.client(requireAuth: false);
      final response = await client.post(
        '/api/v1/auth/forgot/email-password/verify',
        data: {'email': email.trim(), 'otp': verifyCode},
      );
      return left(VerifyData.fromJson(response.data["data"]));
    } catch (e) {
      debugPrint('Reset verification request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<ProfileResponse, dynamic>> sigUpWithData({
    required UserModel user,
  }) async {
    try {
      final client = transport.client(requireAuth: true);
      final response = await client.put(
        '/api/v1/dashboard/user/profile/update',
        data: user.toJson(),
      );
      return left(ProfileResponse.fromJson(response.data));
    } catch (e) {
      debugPrint('Profile update request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }

  @override
  Future<Either<VerifyData, dynamic>> verifyPhone({
    String? phone,
    required String verifyCode,
    required String verifyId,
  }) async {
    try {
      final client = transport.client(requireAuth: false);
      final res = await client.post(
        '/api/v1/auth/verify/phone',
        data: {
          "verifyId": verifyId,
          "verifyCode": verifyCode,
          "phone": AppHelpers.phoneDeFormat(phone),
        },
      );
      return left(VerifyPhoneResponse.fromJson(res.data).data ?? VerifyData());
    } catch (e) {
      debugPrint('Phone verification request failed.');
      return right(AppHelpers.errorHandler(e));
    }
  }
}
