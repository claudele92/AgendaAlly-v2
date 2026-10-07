import 'package:dartz/dartz.dart';
import 'package:demand/domain/model/model/user_model.dart';
import 'package:demand/domain/model/response/login_response.dart';
import 'package:demand/domain/model/response/profile_response.dart';
import 'package:demand/domain/model/response/register_response.dart';
import 'package:demand/domain/model/response/verify_phone_response.dart';

abstract class AuthInterface {
  Future<Either<bool, dynamic>> checkPhone({required String phone});

  Future<Either<bool, dynamic>> sigUpWithEmail({required String email});

  Future<Either<LoginResponse, dynamic>> login({
    required String email,
    required String phone,
    required String password,
  });

  Future<Either<LoginResponse, dynamic>> loginWithSocial({
    required String email,
    required String displayName,
    required String id,
    required String? img,
  });

  Future<Either<VerifyData, dynamic>> sigUpWithPhone({required UserModel user});

  Future<Either<ProfileResponse, dynamic>> sigUpWithData({
    required UserModel user,
  });

  Future<Either<LoginResponse, dynamic>> forgotPasswordAfter({
    required String phone,
    required String verificationId,
    required String password,
  });

  Future<Either<RegisterResponse, dynamic>> sendOtp({required String phone});

  Future<Either<RegisterResponse, dynamic>> forgotPassword({
    required String phone,
  });

  Future<Either<bool, dynamic>> verifyEmail({
    required String verifyCode,
    required String email,
  });

  Future<Either<VerifyData, dynamic>> verifyPhone({
    String? phone,
    required String verifyCode,
    required String verifyId,
  });

  Future<Either<VerifyData, dynamic>> forgotPasswordConfirm({
    required String verifyCode,
    required String email,
  });

  Future updateSetting();

  Future updateFirebaseToken();

  Future logout();

  Future deleteAccount();
}
