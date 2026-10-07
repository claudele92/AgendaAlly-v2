import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

class NewShopListPage extends StatefulWidget {
  const NewShopListPage({super.key});

  @override
  State<NewShopListPage> createState() => _NewShopListPageState();
}

class _NewShopListPageState extends State<NewShopListPage> {
  late RefreshController controller;

  @override
  void initState() {
    controller = RefreshController();
    super.initState();
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => SafeArea(
        bottom: false,
        child: Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.r),
          child: Column(
            children: [
              Row(
                children: [
                  const Spacer(),
                  Text(
                    AppHelpers.getTranslation(TrKeys.newShops),
                    style: CustomStyle.interNoSemi(
                      color: colors.textBlack,
                      size: 22,
                    ),
                  ),
                  const Spacer(),
                  BlocBuilder<ShopBloc, ShopState>(
                    builder: (context, state) {
                      return ButtonEffectAnimation(
                        onTap: () {
                          AppRouteShop.goFilterShopBottomSheet(
                            context,
                            colors: colors,
                            filter: state.filter,
                            isNear: false,
                            isNew: true,
                          );
                        },
                        child: Container(
                          height: 46.r,
                          width: 46.r,
                          alignment: Alignment.center,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(10.r),
                            border: Border.all(color: colors.icon),
                          ),
                          child: Badge(
                            smallSize: 8,
                            isLabelVisible:
                                state.filter?.type == TrKeys.newShops,
                            padding: REdgeInsets.all(2),
                            child: Icon(
                              FlutterRemix.filter_2_line,
                              color: colors.textBlack,
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                ],
              ),
              24.verticalSpace,
              BlocBuilder<ShopBloc, ShopState>(
                builder: (context, state) {
                  return Expanded(
                    child: SmartRefresher(
                      controller: controller,
                      enablePullDown: true,
                      enablePullUp: true,
                      onLoading: () {
                        context.read<ShopBloc>().add(
                          ShopEvent.fetchNewShops(
                            context,
                            controller: controller,
                          ),
                        );
                      },
                      onRefresh: () {
                        context.read<ShopBloc>().add(
                          ShopEvent.fetchNewShops(
                            context,
                            controller: controller,
                            isRefresh: true,
                          ),
                        );
                      },
                      child: state.isLoading
                          ? const Loading()
                          : ListView.builder(
                              padding: EdgeInsets.only(bottom: 60.r),
                              shrinkWrap: true,
                              itemCount: state.shopsNew.length,
                              itemBuilder: (context, index) {
                                return Padding(
                                  padding: EdgeInsets.only(bottom: 8.r),
                                  child: ShopItem(
                                    shop: state.shopsNew[index],
                                    colors: colors,
                                  ),
                                );
                              },
                            ),
                    ),
                  );
                },
              ),
            ],
          ),
        ),
      ),
      floatingButtonLocation: FloatingActionButtonLocation.startFloat,
      floatingButton: (colors) => PopButton(colors: colors),
    );
  }
}
