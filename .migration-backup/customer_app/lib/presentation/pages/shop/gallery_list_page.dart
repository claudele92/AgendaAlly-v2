import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/presentation/components/common_app_bar.dart';
import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_staggered_grid_view/flutter_staggered_grid_view.dart';
import 'package:share_plus/share_plus.dart';
import 'package:demand/domain/model/models.dart';
import '../../../infrastructure/local_storage/local_storage.dart';
import '../../components/components.dart';

class GalleryListPage extends StatelessWidget {
  final List<Galleries> gallery;
  final int? shopId;

  const GalleryListPage({super.key, required this.gallery, this.shopId});

  static Route route(
    BuildContext context, {
    required List<Galleries> list,
    int? shopId,
  }) {
    return MaterialPageRoute(
      builder: (_) => MultiBlocProvider(
        providers: [BlocProvider.value(value: context.read<ShopBloc>())],
        child: GalleryListPage(gallery: list, shopId: shopId),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) {
        return Column(
          children: [
            BlocBuilder<ShopBloc, ShopState>(
              builder: (context, state) {
                return CommonAppBar(
                  color: colors.backgroundColor,
                  bottomPadding: 8,
                  height: 68,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      ButtonEffectAnimation(
                        onTap: () => Navigator.pop(context),
                        child: Container(
                          margin: REdgeInsets.only(left: 12),
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: colors.textWhite,
                          ),
                          child: Icon(
                            FlutterRemix.arrow_left_s_line,
                            color: colors.textBlack,
                            size: 24.r,
                          ),
                        ),
                      ),
                      12.horizontalSpace,
                      Flexible(
                        child: Container(
                          height: 52.r,
                          width: double.infinity,
                          padding: REdgeInsets.all(6),
                          alignment: Alignment.centerLeft,
                          child: Text(
                            state.shop?.translation?.title ?? "",
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            textAlign: TextAlign.center,
                            style: CustomStyle.interNoSemi(
                              color: colors.textBlack,
                              size: 14,
                            ),
                          ),
                        ),
                      ),
                      8.horizontalSpace,
                      Row(
                        children: [
                          CustomIconButton(
                            colors: colors,
                            onTap: () {
                              if (state.shopLink.isNotEmpty) {
                                SharePlus.instance.share(
                                  ShareParams(
                                    text: state.shopLink,
                                    sharePositionOrigin: Rect.fromLTWH(
                                      0,
                                      0,
                                      100,
                                      100,
                                    ),
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
                              context.read<ShopBloc>().add(
                                const ShopEvent.updateState(),
                              );
                            },
                            icon:
                                LocalStorage.getLikedShopsList().contains(
                                  shopId,
                                )
                                ? FlutterRemix.heart_3_fill
                                : FlutterRemix.heart_3_line,
                            iconColor:
                                LocalStorage.getLikedShopsList().contains(
                                  shopId,
                                )
                                ? colors.primary
                                : colors.textBlack,
                          ),
                          8.horizontalSpace,
                        ],
                      ),
                    ],
                  ),
                );
              },
            ),
            Expanded(
              child: GridView.custom(
                padding: EdgeInsets.zero,
                physics: const BouncingScrollPhysics(),
                gridDelegate: SliverQuiltedGridDelegate(
                  crossAxisCount: 2,
                  mainAxisSpacing: 6,
                  crossAxisSpacing: 6,
                  repeatPattern: QuiltedGridRepeatPattern.same,
                  pattern: [
                    QuiltedGridTile(1, 2),
                    QuiltedGridTile(1, 1),
                    QuiltedGridTile(1, 1),
                  ],
                ),
                childrenDelegate: SliverChildBuilderDelegate(
                  childCount: gallery.length,
                  (context, index) => GestureDetector(
                    onTap: () {
                      AppRouteShop.goGalleryPage(
                        context,
                        list: gallery,
                        index: index,
                      );
                    },
                    child: Hero(
                      tag: "image:$index",
                      child: CustomNetworkImage(
                        url: gallery[index].path,
                        height: double.infinity,
                        width: double.infinity,
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ],
        );
      },
    );
  }
}
