import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/drawing_route/drawing_route_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:demand/domain/model/models.dart';
import 'order_map_view.dart';

class LocationWidget extends StatelessWidget {
  final CustomColorSet colors;
  final OrderShops? order;
  final ParcelOrder? parcel;

  const LocationWidget({
    super.key,
    required this.colors,
    this.order,
    this.parcel,
  });

  @override
  Widget build(BuildContext context) {
    return ButtonEffectAnimation(
      onTap: () {
        AppHelpers.showCustomModalBottomSheet(
          context,
          isDrag: false,
          modal: _buildMapModal(context),
        );
      },
      child: Container(
        margin: EdgeInsets.only(top: 12.r),
        padding: EdgeInsets.symmetric(vertical: 16.r, horizontal: 10.r),
        decoration: BoxDecoration(
          color: colors.backgroundColor,
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        child: Row(
          children: [
            Container(
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: colors.textBlack,
              ),
              padding: EdgeInsets.all(12.r),
              child: Icon(
                FlutterRemix.map_pin_range_fill,
                color: colors.textWhite,
              ),
            ),
            10.horizontalSpace,
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  AppHelpers.getTranslation(TrKeys.deliveryAddress),
                  style: CustomStyle.interSemi(
                    color: colors.textBlack,
                    size: 14,
                  ),
                ),
                SizedBox(
                  width: MediaQuery.sizeOf(context).width - 200.r,
                  child: Text(
                    parcel?.addressTo?.address ??
                        (order?.deliveryType == "point"
                            ? order?.location?.address
                            : order?.address?.location?.address) ??
                        "Location",
                    style: CustomStyle.interRegular(
                      color: colors.textBlack,
                      size: 12,
                    ),
                  ),
                ),
              ],
            ),
            const Spacer(),
            Icon(FlutterRemix.arrow_right_s_line, color: colors.textBlack),
          ],
        ),
      ),
    );
  }

  Widget _buildMapModal(BuildContext context) {
    return BlocProvider(
      create: (context) {
        return DrawingRouteBloc()
          ..add(
            DrawingRouteEvent.getRoutingAll(
              context,
              order: order,
              parcel: parcel,
            ),
          )
          ..add(
            DrawingRouteEvent.setMarkers(
              context,
              colors: colors,
              order: order,
              parcel: parcel,
            ),
          );
      },
      child: OrderMapView(
        colors: colors,
        order: order,
        parcel: parcel,
      ),
    );
  }
}
