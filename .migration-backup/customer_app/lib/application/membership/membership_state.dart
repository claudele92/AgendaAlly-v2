part of 'membership_bloc.dart';

@freezed
abstract class MembershipState with _$MembershipState {
  const factory MembershipState({
    @Default([]) List<MembershipModel> memberships,
    @Default([]) List<MyMembershipModel> myMemberships,
    @Default(true) bool isLoading,
    @Default(false) bool isButtonLoading,
    @Default(true) bool isPaymentLoading,
    @Default([]) List<PaymentData> payments,
    @Default([]) List<PaymentData> availablePayments,
    num? walletPrice,
    MembershipModel? membership,
    PaymentData? selectedPayment,
  }) = _MembershipState;
}
