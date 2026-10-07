part of 'wallet_bloc.dart';

@freezed
abstract class WalletEvent with _$WalletEvent {
  const factory WalletEvent.fetchTransactions(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchTransactions;

  const factory WalletEvent.fetchPayments(BuildContext context) = FetchPayments;

  const factory WalletEvent.selectPayment({required int index}) = SelectPayment;

  const factory WalletEvent.searchUser(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
    required String search,
  }) = SearchUser;

  const factory WalletEvent.fillWallet(
    BuildContext context, {
    required int walletId,
    required VoidCallback onSuccess,
    required VoidCallback onFailure,
    required CustomColorSet colors,
    required String price,
  }) = FillWallet;

  const factory WalletEvent.sendWallet(
    BuildContext context, {
    required String price,
    required VoidCallback onSuccess,
    required String uuid,
  }) = SendWallet;
}
