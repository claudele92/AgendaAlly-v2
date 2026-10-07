import 'package:demand/application/become_seller/become_seller_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

class DocScreen extends StatelessWidget {
  final CustomColorSet colors;

  const DocScreen({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          AppHelpers.getTranslation(TrKeys.uploadDocumentsFor),
          style: CustomStyle.interNormal(color: colors.textBlack),
        ),
        12.verticalSpace,
        Container(
          height: 80.h,
          width: double.infinity,
          decoration: BoxDecoration(
            color: colors.newBoxColor,
            borderRadius: BorderRadius.circular(12.r),
          ),
          child: InkWell(
            onTap: () async {
              await ImgService.getFilePdf((v) {
                context.read<BecomeSellerBloc>().add(
                  BecomeSellerEvent.updateFilePath(file: v),
                );
              });
            },
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  FlutterRemix.upload_cloud_2_line,
                  size: 32.r,
                  color: colors.textHint,
                ),
                8.horizontalSpace,
                Text(
                  AppHelpers.getTranslation(TrKeys.uploadDocuments),
                  style: CustomStyle.interNoSemi(
                    size: 14,
                    color: colors.textHint,
                    letterSpacing: -0.3,
                  ),
                ),
              ],
            ),
          ),
        ),
        16.verticalSpace,
        BlocBuilder<BecomeSellerBloc, BecomeSellerState>(
          builder: (context, state) {
            return Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                ...state.filepath.map(
                  (e) => Container(
                    width: double.infinity,
                    decoration: BoxDecoration(
                      color: CustomStyle.white,
                      borderRadius: BorderRadius.circular(6.r),
                    ),
                    padding: REdgeInsets.symmetric(horizontal: 6, vertical: 6),
                    margin: REdgeInsets.only(bottom: 4),
                    child: Row(
                      children: [
                        Expanded(
                          child: Text(
                            e,
                            style: CustomStyle.interRegular(
                              size: 12.sp,
                              color: colors.textBlack,
                            ),
                          ),
                        ),
                        IconButton(
                          onPressed: () {
                            context.read<BecomeSellerBloc>().add(
                              BecomeSellerEvent.removeFilePath(file: e),
                            );
                          },
                          icon: Icon(
                            FlutterRemix.close_circle_line,
                            size: 21.r,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            );
          },
        ),
      ],
    );
  }
}
