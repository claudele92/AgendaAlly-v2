part of 'auth_bloc.dart';

@freezed
abstract class AuthEvent with _$AuthEvent {
  const factory AuthEvent.login(
    BuildContext context, {
    required String phone,
    required String email,
    required String password,
    VoidCallback? onSuccess,
  }) = Login;

  const factory AuthEvent.signUp(
    BuildContext context, {
    required String firstname,
    required String lastname,
    required String email,
    required String phone,
    required String password,
    required String referral,
    VoidCallback? onSuccess,
  }) = SignUp;

  const factory AuthEvent.signUpWithData(
    BuildContext context, {
    required String firstname,
    required String lastname,
    required String phone,
    required String email,
    required String password,
    required String referral,
    VoidCallback? onSuccess,
  }) = SignUpWithData;

  const factory AuthEvent.signUpEmail(
    BuildContext context, {
    required String email,
  }) = SignUpEmail;

  const factory AuthEvent.signUpPhone(
    BuildContext context, {
    required String phone,
  }) = SignUpPhone;

  const factory AuthEvent.forgotPassword(
    BuildContext context, {
    required String phone,
    VoidCallback? onSuccess,
  }) = ForgotPassword;

  const factory AuthEvent.socialSignIn(
    BuildContext context, {
    required IconData type,
    VoidCallback? onSuccess,
  }) = SocialSignIn;

  const factory AuthEvent.setVerificationId({required String id}) =
      SetVerificationId;

  const factory AuthEvent.showPassword() = ShowPassword;

  const factory AuthEvent.showConfirmPassword() = ShowConfirmPassword;

  const factory AuthEvent.switchScreen(AuthType type) = SwitchScreen;

  const factory AuthEvent.checkVerify(
    BuildContext context, {
    required String verifyId,
    required String code,
    required String phone,
    bool? editPhone,
  }) = CheckVerify;

  const factory AuthEvent.checkVerifyEmail(
    BuildContext context, {
    required String code,
  }) = CheckVerifyEmail;

  const factory AuthEvent.checkPhone(
    BuildContext context, {
    required String phone,
    VoidCallback? onSuccess,
  }) = CheckPhone;

  const factory AuthEvent.forgotPasswordConfirm(
    BuildContext context, {
    required String email,
    required String code,
    VoidCallback? onSuccess,
  }) = ForgotPasswordConfirm;

  const factory AuthEvent.forgotPasswordAfter(
    BuildContext context, {
    required String phone,
    required String password,
    VoidCallback? onSuccess,
  }) = ForgotPasswordAfter;

  const factory AuthEvent.loadingChange() = LoadingChange;

  const factory AuthEvent.acceptTerm() = AcceptTerm;
}
