import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/parcel/parcel_bloc.dart';
import 'package:demand/domain/model/response/parcel_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class TypeItem extends StatelessWidget {
  final CustomColorSet colors;
  final TypeModel? type;
  final int index;
  final int selectIndex;

  const TypeItem({
    super.key,
    required this.colors,
    required this.type,
    required this.index,
    required this.selectIndex,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () {
        if (index == selectIndex) {
          AppHelpers.showCustomModalBottomSheet(
            context,
            modal: Container(
              decoration: BoxDecoration(
                color: colors.backgroundColor,
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(12.r),
                  topRight: Radius.circular(12.r),
                ),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  8.verticalSpace,
                  Center(
                    child: Container(
                      height: 4.h,
                      width: 48.w,
                      decoration: BoxDecoration(
                        color: colors.textHint,
                        borderRadius: BorderRadius.circular(40.r),
                      ),
                    ),
                  ),
                  18.verticalSpace,
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16.r),
                    child: Text(
                      type?.type ?? "",
                      style: CustomStyle.interSemi(
                        size: 16,
                        color: colors.textBlack,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  14.verticalSpace,
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16.r),
                    child: Text(
                      "${AppHelpers.getTranslation(TrKeys.upTo)} ${(type?.maxG ?? 1) / 1000} ${AppHelpers.getTranslation(TrKeys.kg)}, ${(type?.maxHeight ?? 1)}X${(type?.maxWidth ?? 1)} ${AppHelpers.getTranslation(TrKeys.sm)}",
                      style: CustomStyle.interRegular(
                        size: 14,
                        color: colors.textBlack,
                      ),
                    ),
                  ),
                  20.verticalSpace,
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16.r),
                    child: CustomNetworkImage(
                      url: type?.img ?? "",
                      height: 210,
                      width: double.infinity,
                      radius: 10,
                    ),
                  ),
                  20.verticalSpace,
                  Padding(
                    padding: EdgeInsets.symmetric(horizontal: 16.r),
                    child: CustomButton(
                      title: AppHelpers.getTranslation(TrKeys.gotIt),
                      onTap: () {
                        Navigator.pop(context);
                      },
                      bgColor: colors.primary,
                      titleColor: colors.white,
                    ),
                  ),
                  32.verticalSpace,
                ],
              ),
            ),
          );
          return;
        }
        context.read<ParcelBloc>().add(
          ParcelEvent.selectType(context, index: index),
        );
      },
      child: ButtonEffectAnimation(
        child: Container(
          foregroundDecoration: BoxDecoration(
            color: selectIndex == index
                ? colors.transparent
                : colors.backgroundColor.withValues(alpha: 0.8),
          ),
          margin: EdgeInsets.all(8.r),
          decoration: BoxDecoration(
            border: Border.all(
              color: selectIndex == index ? colors.primary : colors.transparent,
            ),
            color: colors.backgroundColor,
            borderRadius: BorderRadius.circular(10.r),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              CustomNetworkImage(
                url: type?.img ?? "",
                height: 70,
                width: 70,
                radius: 8,
              ),
              8.verticalSpace,
              Text(
                type?.type ?? "",
                style: CustomStyle.interSemi(size: 16, color: colors.textBlack),
                maxLines: 1,
              ),
              8.verticalSpace,
              Text(
                "${AppHelpers.getTranslation(TrKeys.upTo)} ${(type?.maxG ?? 1) / 1000} ${AppHelpers.getTranslation(TrKeys.kg)}",
                style: CustomStyle.interSemi(size: 13, color: colors.textHint),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
