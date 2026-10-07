import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:demand/application/checkout/checkout_bloc.dart';
import 'package:demand/domain/model/response/delivery_point_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class SelectDeliveryPoint extends StatelessWidget {
  final List<DeliveryPoint> list;

  const SelectDeliveryPoint({super.key, required this.list});

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      appBar: (colors) => AppBar(
        title: Text(
          AppHelpers.getTranslation(TrKeys.selectPickupZone),
          style: CustomStyle.interNormal(color: colors.textBlack),
        ),
        backgroundColor: colors.backgroundColor,
        iconTheme: IconThemeData(color: colors.textBlack),
      ),
      body: (colors) => GoogleMap(
        tiltGesturesEnabled: false,
        myLocationButtonEnabled: false,
        zoomControlsEnabled: false,
        markers: list
            .map(
              (e) => Marker(
                onTap: () {
                  AppHelpers.showCustomModalBottomSheet(
                    context,
                    modal: _showPoint(context, colors, e),
                  );
                },
                markerId: MarkerId(e.id.toString()),
                position: LatLng(
                  e.location?.latitude ?? AppHelpers.getInitialLatitude(),
                  e.location?.longitude ?? AppHelpers.getInitialLongitude(),
                ),
              ),
            )
            .toSet(),
        initialCameraPosition: CameraPosition(
          bearing: 0,
          target: LatLng(
            list.first.location?.latitude ?? AppHelpers.getInitialLatitude(),
            list.first.location?.longitude ?? AppHelpers.getInitialLongitude(),
          ),
          tilt: 0,
          zoom: 12,
        ),
        zoomGesturesEnabled: true,
      ),
    );
  }

  KeyboardDismisser _showPoint(
    BuildContext context,
    CustomColorSet colors,
    DeliveryPoint point,
  ) {
    return KeyboardDismisser(
      isLtr: LocalStorage.getLangLtr(),
      child: BlurWrap(
        radius: BorderRadius.only(
          topRight: Radius.circular(24.r),
          topLeft: Radius.circular(24.r),
        ),
        child: Container(
          margin: MediaQuery.viewInsetsOf(context),
          width: double.infinity,
          decoration: BoxDecoration(
            color: colors.newBoxColor,
            borderRadius: BorderRadius.only(
              topRight: Radius.circular(24.r),
              topLeft: Radius.circular(24.r),
            ),
          ),
          padding: EdgeInsets.all(16.r),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              8.verticalSpace,
              Text(
                point.translation?.title ?? "",
                style: CustomStyle.interNormal(
                  color: colors.textBlack,
                  size: 20,
                ),
              ),
              8.verticalSpace,
              Text(
                point.address?.address ?? "",
                style: CustomStyle.interRegular(
                  color: colors.textHint,
                  size: 16,
                ),
              ),
              8.verticalSpace,
              Row(
                children: [
                  Icon(FlutterRemix.shirt_line, color: colors.textBlack),
                  6.horizontalSpace,
                  Text(
                    "${point.fittingRooms ?? 0} ${AppHelpers.getTranslation(TrKeys.fittingRooms)}",
                    style: CustomStyle.interRegular(
                      color: colors.textBlack,
                      size: 16,
                    ),
                  ),
                ],
              ),
              24.verticalSpace,
              ListView.builder(
                shrinkWrap: true,
                itemCount: point.workingDays?.length,
                itemBuilder: (context, index) {
                  return Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Row(
                      children: [
                        Text(
                          point.workingDays?[index].day?.toUpperCase() ?? "",
                          style: CustomStyle.interNormal(
                            color: colors.textBlack,
                          ),
                        ),
                        const Spacer(),
                        Text(
                          "${point.workingDays?[index].from} - ${point.workingDays?[index].to}",
                          style: CustomStyle.interNormal(
                            color: colors.textBlack,
                          ),
                        ),
                      ],
                    ),
                  );
                },
              ),
              16.verticalSpace,
              CustomButton(
                title: AppHelpers.getTranslation(TrKeys.save),
                bgColor: colors.primary,
                titleColor: colors.white,
                onTap: () {
                  context.read<CheckoutBloc>().add(
                    CheckoutEvent.selectPoint(point: point),
                  );
                  Navigator.pop(context);
                  Navigator.pop(context);
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}
