import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:share_plus/share_plus.dart';

import '../../../app_assets.dart';

class ShareAndLike extends StatelessWidget {
  final CustomColorSet colors;
  final int shopId;
  final bool likeButton;

  const ShareAndLike({
    super.key,
    required this.colors,
    required this.shopId,
    this.likeButton = true,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.only(
        top: MediaQuery.paddingOf(context).top + 4,
        left: 14.r,
        right: 14.r,
      ),
      child: Row(
        children: [
          const Spacer(),
          Container(
            padding: EdgeInsets.all(8.r),
            decoration: BoxDecoration(
              color: colors.white.withValues(alpha: 0.8),
              borderRadius: BorderRadius.circular(10.r),
            ),
            child: BlocBuilder<ShopBloc, ShopState>(
              builder: (context, state) {
                return ButtonEffectAnimation(
                  onTap: () {
                    if (state.shopLink.isNotEmpty) {
                      SharePlus.instance.share(
                        ShareParams(
                          text: state.shopLink,
                          sharePositionOrigin: Rect.fromLTWH(0, 0, 100, 100),
                        ),
                      );
                    }
                  },
                  child: SvgPicture.asset(
                    Assets.svgShare,
                    width: 26.r,
                    height: 26.r,
                  ),
                );
              },
            ),
          ),
          10.horizontalSpace,
          if (likeButton)
            Container(
              padding: EdgeInsets.all(8.r),
              decoration: BoxDecoration(
                color: colors.white.withValues(alpha: 0.8),
                borderRadius: BorderRadius.circular(10.r),
              ),
              child: BlocBuilder<ShopBloc, ShopState>(
                builder: (context, state) {
                  return ButtonEffectAnimation(
                    onTap: () {
                      LocalStorage.setLikedShopsList(shopId);
                      context.read<ShopBloc>().add(
                        const ShopEvent.updateState(),
                      );
                    },
                    child: SvgPicture.asset(
                      LocalStorage.getLikedShopsList().contains(shopId)
                          ? Assets.svgLikeButtom
                          : Assets.svgUnlike,
                      width: 26.r,
                      height: 26.r,
                    ),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }
}
