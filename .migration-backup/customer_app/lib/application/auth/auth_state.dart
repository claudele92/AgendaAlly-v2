part of 'auth_bloc.dart';

@freezed
abstract class AuthState with _$AuthState {
  const factory AuthState({
    @Default(false) bool isLoading,
    @Default(false) bool isShowPassword,
    @Default(false) bool isShowConfirmPassword,
    @Default(AuthType.login) AuthType screenType,
    @Default(false) bool isError,
    @Default(false) bool isReset,
    @Default(false) bool isAcceptTerm,
    @Default("") String verificationId,
    @Default('05:00') String timerText,
  }) = _AuthState;
}
