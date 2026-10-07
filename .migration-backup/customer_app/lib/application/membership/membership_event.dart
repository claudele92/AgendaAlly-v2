part of 'membership_bloc.dart';

@freezed
abstract class MembershipEvent with _$MembershipEvent {
  const factory MembershipEvent.setWalletPrice({num? price}) = SetWalletPrice;

  const factory MembershipEvent.fetchMembership(
    BuildContext context, {
    bool? isRefresh,
    int? shopId,
    RefreshController? controller,
  }) = FetchMembership;

  const factory MembershipEvent.fetchMembershipDetails(
    BuildContext context, {
    int? shopId,
    int? id,
    String? shopSlug,
    bool? enable,
  }) = FetchMembershipDetails;

  const factory MembershipEvent.selectPayment({required PaymentData payment}) =
      SelectPayment;

  const factory MembershipEvent.fetchPayments(BuildContext context) =
      FetchPayments;

  const factory MembershipEvent.fetchWebView(
    BuildContext context, {
    required MembershipModel? membership,
    required VoidCallback? onSuccess,
  }) = FetchWebView;

  const factory MembershipEvent.createTransaction(
    BuildContext context, {
    required int membershipId,
    required VoidCallback? onSuccess,
  }) = CreateTransaction;

  const factory MembershipEvent.myMemberships(
    BuildContext context, {
    bool? isRefresh,
    int? serviceId,
    int? shopId,
    RefreshController? controller,
  }) = MyMemberships;
}
