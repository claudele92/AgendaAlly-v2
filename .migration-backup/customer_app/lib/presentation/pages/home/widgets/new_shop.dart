import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class NewShopList extends StatelessWidget {
  final CustomColorSet colors;

  const NewShopList({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<ShopBloc, ShopState>(
      buildWhen: (previous, current) => previous.shopsNew != current.shopsNew,
      builder: (context, state) {
        if (state.shopsNew.isEmpty) {
          return const SizedBox.shrink();
        }

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            24.verticalSpace,
            TitleWidget(
              title: AppHelpers.getTranslation(TrKeys.newShops),
              titleColor: colors.textBlack,
              subTitle: AppHelpers.getTranslation(TrKeys.seeAll),
              onTap: () async {
                await AppRouteShop.goShopListPage(context, isNew: true);
                if (context.mounted) {
                  context.read<ShopBloc>().add(
                    const ShopEvent.updateState(),
                  );
                }
              },
            ),
            16.verticalSpace,
            SizedBox(
              height: 262.r,
              child: ListView.separated(
                padding: EdgeInsets.symmetric(horizontal: 16.r),
                scrollDirection: Axis.horizontal,
                itemCount: state.shopsNew.length,
                separatorBuilder: (_, _) => 8.horizontalSpace,
                itemBuilder: (context, index) {
                  return FadeInAnimation(
                    delay: Duration(milliseconds: index * 50),
                    child: ShopItem(
                      blurUi: true,
                      colors: colors,
                      shop: state.shopsNew[index],
                    ),
                  );
                },
              ),
            ),
          ],
        );
      },
    );
  }
}
