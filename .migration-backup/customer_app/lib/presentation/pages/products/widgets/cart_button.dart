import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/svg.dart';
import 'package:demand/application/cart/cart_bloc.dart';
import 'package:demand/application/main/main_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';

import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

import '../../../../application/products/product_bloc.dart';
import '../../../app_assets.dart';

class CartButton extends StatelessWidget {
  final CustomColorSet colors;

  const CartButton({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return ButtonEffectAnimation(
      onTap: () {
        Navigator.popUntil(context, (route) {
          return route.isFirst;
        });
        context.read<MainBloc>().add(const MainEvent.changeIndex(index: 4));
        context.read<CartBloc>().add(
          CartEvent.insertCart(
            context,
            onSuccess: () {
              if (LocalStorage.getToken().isNotEmpty) {
                context.read<CartBloc>().add(
                  CartEvent.calculateCartWithCoupon(context),
                );
              }

              context.read<ProductBloc>().add(const ProductEvent.updateState());
            },
          ),
        );
      },
      child: BlurWrap(
        radius: BorderRadius.circular(100.r),
        child: Container(
          padding: EdgeInsets.symmetric(horizontal: 8.r, vertical: 2.r),
          decoration: BoxDecoration(
            color: colors.bottomBarColor,
            borderRadius: BorderRadius.circular(100.r),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              16.horizontalSpace,
              SvgPicture.asset(Assets.svgSelectBag, height: 14.r),
              8.horizontalSpace,
              Text(
                AppHelpers.getTranslation(TrKeys.goToCart),
                style: CustomStyle.interNormal(color: colors.white, size: 16),
              ),
              16.horizontalSpace,
              Container(
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: colors.primary,
                ),
                padding: EdgeInsets.all(12.r),
                child: Text(
                  LocalStorage.getCartList()
                      .where((element) => element.count > 0)
                      .length
                      .toString(),
                  style: CustomStyle.interNormal(color: colors.white, size: 20),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
