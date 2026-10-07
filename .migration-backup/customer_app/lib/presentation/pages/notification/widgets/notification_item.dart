import 'package:demand/app_constants.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_slidable/flutter_slidable.dart';
import 'package:demand/application/notification/notification_bloc.dart';
import 'package:demand/domain/model/model/blog_model.dart';
import 'package:demand/domain/model/model/order_model.dart';
import 'package:demand/domain/model/model/parcel_order_model.dart';
import 'package:demand/domain/model/model/shop_model.dart';
import 'package:demand/domain/model/response/notification_response.dart';

import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/route/app_route_parcel.dart';
import 'package:demand/presentation/route/app_route_service.dart';
import 'package:demand/presentation/route/app_route_setting.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class NotificationItem extends StatelessWidget {
  final CustomColorSet colors;
  final NotificationModel notification;

  const NotificationItem({
    super.key,
    required this.colors,
    required this.notification,
  });

  @override
  Widget build(BuildContext context) {
    final bloc = context.read<NotificationBloc>();
    return Slidable(
      startActionPane: ActionPane(
        extentRatio: 0.12,
        motion: const ScrollMotion(),
        children: [
          Expanded(
            child: Builder(
              builder: (context) {
                return GestureDetector(
                  onTap: () {
                    Slidable.of(context)?.close();
                    bloc.add(
                      NotificationEvent.readOne(context, id: notification.id),
                    );
                  },
                  child: Container(
                    width: 50.r,
                    height: 72.r,
                    decoration: BoxDecoration(
                      color: colors.textBlack,
                      borderRadius: BorderRadius.only(
                        topRight: Radius.circular(AppConstants.radius.r),
                        bottomRight: Radius.circular(AppConstants.radius.r),
                      ),
                    ),
                    alignment: Alignment.center,
                    child: Icon(
                      FlutterRemix.mail_open_fill,
                      color: colors.textWhite,
                      size: 24.r,
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
      endActionPane: ActionPane(
        extentRatio: 0.12,
        motion: const ScrollMotion(),
        children: [
          Expanded(
            child: Builder(
              builder: (context) {
                return GestureDetector(
                  onTap: () {
                    bloc.add(
                      NotificationEvent.removeItem(
                        context,
                        id: notification.id,
                      ),
                    );
                    Slidable.of(context)?.close();
                  },
                  child: Container(
                    width: 50.r,
                    height: 72.r,
                    decoration: BoxDecoration(
                      color: CustomStyle.red,
                      borderRadius: BorderRadius.only(
                        topLeft: Radius.circular(AppConstants.radius.r),
                        bottomLeft: Radius.circular(AppConstants.radius.r),
                      ),
                    ),
                    alignment: Alignment.center,
                    child: Icon(
                      FlutterRemix.delete_bin_line,
                      color: CustomStyle.white,
                      size: 24.r,
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
      child: ButtonEffectAnimation(
        onTap: () {
          if (notification.data?.type?.toLowerCase() ==
                  ListConstants.newOrder ||
              (notification.data?.type?.toLowerCase() ==
                      ListConstants.statusChanged &&
                  (notification.modelType?.toLowerCase() != 'shop'))) {
            bloc.add(NotificationEvent.readOne(context, id: notification.id));
            AppRoute.goOrderPage(
              context,
              OrderShops(
                id: notification.type == "order"
                    ? notification.orderData?.parentId ??
                          notification.orderData?.id
                    : notification.modelId,
              ),
            );
            return;
          }
          if (notification.data?.type?.toLowerCase() ==
              ListConstants.newParcelOrder) {
            bloc.add(NotificationEvent.readOne(context, id: notification.id));
            AppRouteParcel.goParcelOrderPage(
              context,
              ParcelOrder(id: notification.modelId),
              colors,
            );
            return;
          }
          if (notification.data?.type?.toLowerCase() ==
              ListConstants.newsPublish) {
            bloc.add(NotificationEvent.readOne(context, id: notification.id));
            AppRoute.goBlogBottomSheet(
              context,
              BlogData(id: notification.modelId),
            );
            return;
          }
          if (notification.data?.type?.toLowerCase() ==
              ListConstants.bookingStatusChanged) {
            bloc.add(NotificationEvent.readOne(context, id: notification.id));
            AppRouteService.goBookingPage(
              context,
              shop: ShopData(id: notification.shopId ?? 0),
              id: notification.modelId ?? 0,
            );
            return;
          }
          bloc.add(NotificationEvent.readOne(context, id: notification.id));
          if (notification.data?.type == AppConstants.newMessage &&
              LocalStorage.getUser().id != notification.data?.sender?.id) {
            AppRouteSetting.goChat(
              context,
              senderId: notification.data?.sender?.id ?? 0,
            );
          } else {
            AppRouteSetting.goNotificationBottomSheet(
              context,
              notification: notification,
              colors: colors,
            );
          }
        },
        child: Container(
          margin: EdgeInsets.only(bottom: 8.r, left: 16.r, right: 16.r),
          width: double.infinity,
          padding: EdgeInsets.symmetric(vertical: 20.r, horizontal: 22.r),
          decoration: BoxDecoration(
            color: colors.newBoxColor,
            borderRadius: BorderRadius.circular(10.r),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    TimeService.dateFormatForNotification(
                      notification.createdAt,
                    ),
                    style: CustomStyle.interNormal(
                      color: colors.textHint,
                      size: 12,
                    ),
                  ),
                  if (notification.readAt == null)
                    Container(
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: colors.primary,
                      ),
                      width: 10.r,
                      height: 10.r,
                    ),
                ],
              ),
              10.verticalSpace,
              Text(
                notification.body ?? "",
                style: CustomStyle.interNormal(
                  color: colors.textBlack,
                  size: 16,
                ),
                maxLines: 2,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
