part of 'notification_bloc.dart';

@freezed
abstract class NotificationEvent with _$NotificationEvent {
  const factory NotificationEvent.fetchNotification(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchNotification;

  const factory NotificationEvent.readAll(BuildContext context) = ReadAll;

  const factory NotificationEvent.readOne(
    BuildContext context, {
    required int? id,
  }) = ReadOne;

  const factory NotificationEvent.removeItem(
    BuildContext context, {
    required int? id,
  }) = RemoveItem;

  const factory NotificationEvent.fetchCount(BuildContext context) = FetchCount;
}
