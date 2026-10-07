part of 'review_bloc.dart';

@freezed
abstract class ReviewEvent with _$ReviewEvent {
  const factory ReviewEvent.setReview({required double review}) = SetReview;

  const factory ReviewEvent.sendReview(
    BuildContext context, {
    required String comment,
    int? shopId,
    String? productUuid,
    int? blogId,
    int? orderId,
    int? bookingId,
    required VoidCallback onSuccess,
  }) = SendReview;

  const factory ReviewEvent.fetchReview(
    BuildContext context, {
    int? shopId,
    int? blogId,
    int? driverId,
    int? bookingId,
    int? productId,
  }) = FetchReview;

  const factory ReviewEvent.checkReview(
    BuildContext context, {
    int? bookingId,
  }) = CheckReview;

  const factory ReviewEvent.setImage({required String imagePath}) = SetImage;

  const factory ReviewEvent.selectOfType({required String type}) = SelectOfType;

  const factory ReviewEvent.selectOfTypeFromReview({
    required ReviewModel? review,
  }) = SelectOfTypeFromReview;

  const factory ReviewEvent.fetchReviewList(
    BuildContext context, {
    int? shopId,
    int? blogId,
    int? driverId,
    int? masterId,
    String? productUuid,
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchReviewList;

  const factory ReviewEvent.fetchReviewOptions(
    BuildContext context, {
    int? shopId,
  }) = FetchReviewOptions;
}
