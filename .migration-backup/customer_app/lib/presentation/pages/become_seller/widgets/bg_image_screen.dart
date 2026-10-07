import 'dart:io';

import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/become_seller/become_seller_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class BgImageScreen extends StatelessWidget {
  final CustomColorSet colors;

  const BgImageScreen({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          AppHelpers.getTranslation(TrKeys.backgroundImage),
          style: CustomStyle.interNormal(color: colors.textBlack),
        ),
        16.verticalSpace,
        Stack(
          children: [
            BlocBuilder<BecomeSellerBloc, BecomeSellerState>(
              buildWhen: (p, n) {
                return p.bgPath != n.bgPath;
              },
              builder: (context, state) {
                return state.bgPath == null
                    ? CustomNetworkImage(
                        url: "",
                        height: 130.r,
                        width: double.infinity,
                        radius: 10,
                      )
                    : Container(
                        height: 130.r,
                        width: double.infinity,
                        padding: EdgeInsets.all(8.r),
                        decoration: BoxDecoration(
                          image: DecorationImage(
                            fit: BoxFit.cover,
                            image: FileImage(File(state.bgPath ?? "")),
                          ),
                          borderRadius: BorderRadius.circular(
                            AppConstants.radius.r,
                          ),
                          color: colors.newBoxColor,
                        ),
                      );
              },
            ),
            Positioned(
              bottom: 16.r,
              right: 16.r,
              child: ButtonEffectAnimation(
                onTap: () {
                  AppHelpers.openDialogImagePicker(
                    context,
                    openCamera: () async {
                      String? titleImg = await ImgService.getCamera();
                      if (context.mounted && (titleImg != null)) {
                        context.read<BecomeSellerBloc>().add(
                          BecomeSellerEvent.updateBgPath(imagePath: titleImg),
                        );
                        Navigator.pop(context);
                      }
                    },
                    openGallery: () async {
                      String? titleImg = await ImgService.getGallery();
                      if (context.mounted && (titleImg != null)) {
                        context.read<BecomeSellerBloc>().add(
                          BecomeSellerEvent.updateBgPath(imagePath: titleImg),
                        );
                        Navigator.pop(context);
                      }
                    },
                  );
                },
                child: Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(28.r),
                    color: colors.backgroundColor,
                  ),
                  padding: EdgeInsets.symmetric(
                    vertical: 10.r,
                    horizontal: 20.r,
                  ),
                  child: Text(
                    AppHelpers.getTranslation(TrKeys.uploadPhoto),
                    style: CustomStyle.interNormal(color: colors.textBlack),
                  ),
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }
}
