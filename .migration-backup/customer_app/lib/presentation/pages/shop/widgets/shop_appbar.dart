import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/pages/shop/widgets/shop_avatar.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:share_plus/share_plus.dart';
import 'package:demand/domain/model/models.dart';
import 'package:demand/presentation/components/components.dart';

class ShopAppbar extends StatelessWidget {
  final CustomColorSet colors;
  final int shopId;
  final bool innerBoxIsScrolled;
  final Function(List<Galleries> images, int index)? onTapImage;

  const ShopAppbar(
    this.colors, {
    super.key,
    required this.shopId,
    required this.innerBoxIsScrolled,
    this.onTapImage,
  });

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<ShopBloc, ShopState>(
      builder: (context, state) {
        return SliverAppBar(
          expandedHeight: 220.r,
          backgroundColor: colors.textWhite,
          elevation: 0.2,
          floating: false,
          pinned: true,
          centerTitle: false,
          title: innerBoxIsScrolled
              ? Text(
                  state.shop?.translation?.title ?? "",
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  textAlign: TextAlign.center,
                  style: CustomStyle.interNoSemi(
                    color: colors.textBlack,
                    size: 14,
                  ),
                )
              : null,
          leading: SizedBox.shrink(),
          actions: [
            CustomIconButton(
              colors: colors,
              onTap: () {
                if (state.shopLink.isNotEmpty) {
                  SharePlus.instance.share(
                    ShareParams(
                      uri: Uri.tryParse(state.shopLink),
                      subject: state.shop?.translation?.title,
                      sharePositionOrigin: Rect.fromLTWH(0, 0, 100, 100),
                    ),
                  );
                }
              },
              icon: FlutterRemix.share_line,
            ),
            8.horizontalSpace,
            CustomIconButton(
              colors: colors,
              onTap: () {
                LocalStorage.setLikedShopsList(shopId);
                context.read<ShopBloc>().add(const ShopEvent.updateState());
              },
              icon: LocalStorage.getLikedShopsList().contains(shopId)
                  ? FlutterRemix.heart_3_fill
                  : FlutterRemix.heart_3_line,
              iconColor: LocalStorage.getLikedShopsList().contains(shopId)
                  ? colors.primary
                  : colors.textBlack,
            ),
            8.horizontalSpace,
          ],
          flexibleSpace: FlexibleSpaceBar(
            centerTitle: true,
            background: Builder(
              builder: (context) {
                return GestureDetector(
                  onTap: () => onTapImage?.call(state.shopImages, 0),
                  child: ShopAvatar(colors: colors),
                );
                // return  ShopGalleries(
                //   countActive: true,
                //   onTapImage: onTapImage,
                //   galleries: state.shopImages,
                //   height: double.infinity,
                //   width: double.infinity,
                //   img: state.shop?.backgroundImg,
                //   radius: 0,
                // );
              },
            ),
          ),
        );
      },
    );
  }
}
