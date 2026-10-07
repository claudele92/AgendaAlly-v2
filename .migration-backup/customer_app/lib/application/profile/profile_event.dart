part of 'profile_bloc.dart';

@freezed
abstract class ProfileEvent with _$ProfileEvent {
  const factory ProfileEvent.updateImagePath({required String? imagePath}) =
      UpdateImagePath;

  const factory ProfileEvent.updateUser(
    BuildContext context, {
    required String firstName,
    required String lastName,
    required String phone,
    required String email,
    VoidCallback? onSuccess,
  }) = UpdateUser;

  const factory ProfileEvent.updatePassword(
    BuildContext context, {
    required String newPassword,
    required String confirmPassword,
    VoidCallback? onSuccess,
  }) = UpdatePassword;

  const factory ProfileEvent.showPassword() = ShowPassword;

  const factory ProfileEvent.showConfirmPassword() = ShowConfirmPassword;

  const factory ProfileEvent.getLanguage(BuildContext context) = GetLanguage;

  const factory ProfileEvent.getCurrency(BuildContext context) = GetCurrency;

  const factory ProfileEvent.getHelps(BuildContext context) = GetHelps;

  const factory ProfileEvent.updateLan() = UpdateLan;

  const factory ProfileEvent.fetchProfile(BuildContext context) = FetchProfile;

  const factory ProfileEvent.fetchReferral(BuildContext context) =
      FetchReferral;

  const factory ProfileEvent.setProgress({
    String? taskId,
    int? index,
    required int progress,
  }) = SetProgress;

  const factory ProfileEvent.fetchDigitalList(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchDigitalList;

  const factory ProfileEvent.getPolicy(BuildContext context) = GetPolicy;

  const factory ProfileEvent.getTerm(BuildContext context) = GetTerm;
}
