import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/category/category_bloc.dart';
import 'package:demand/application/main/main_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';

import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

class CategoryOneList extends StatelessWidget {
  final RefreshController categoryRefresh;
  final CustomColorSet colors;

  const CategoryOneList({
    super.key,
    required this.categoryRefresh,
    required this.colors,
  });

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<CategoryBloc, CategoryState>(
      builder: (context, state) {
        return state.categoriesOfService.isNotEmpty
            ? Column(
                children: [
                  TitleWidget(
                    title: AppHelpers.getTranslation(TrKeys.categories),
                    titleColor: colors.textBlack,
                    subTitle: AppHelpers.getTranslation(TrKeys.seeAll),
                    onTap: () {
                      context.read<MainBloc>().add(
                        const MainEvent.changeIndex(index: 1),
                      );
                    },
                  ),
                  12.verticalSpace,
                  SizedBox(
                    height: 120.r,
                    child: Row(
                      children: [
                        Expanded(
                          child: SmartRefresher(
                            controller: categoryRefresh,
                            scrollDirection: Axis.horizontal,
                            enablePullUp: true,
                            enablePullDown: false,
                            onLoading: () {
                              context.read<CategoryBloc>().add(
                                CategoryEvent.fetchCategory(
                                  context,
                                  controller: categoryRefresh,
                                ),
                              );
                            },
                            child: ListView.builder(
                              key: const PageStorageKey<String>("list"),
                              shrinkWrap: true,
                              padding: EdgeInsets.symmetric(horizontal: 16.r),
                              scrollDirection: Axis.horizontal,
                              itemCount: state.categoriesOfService.length,
                              itemBuilder: (context, index) {
                                return Padding(
                                  padding: EdgeInsets.symmetric(
                                    horizontal: 8.r,
                                  ),
                                  child: ButtonEffectAnimation(
                                    onTap: () {
                                      AppRoute.goSearchMap(
                                        context,
                                        category:
                                            state.categoriesOfService[index],
                                      );
                                    },
                                    child: Column(
                                      children: [
                                        Container(
                                          decoration: BoxDecoration(
                                            borderRadius: BorderRadius.circular(
                                              20.r,
                                            ),
                                            color: colors.newBoxColor,
                                            border: Border.all(
                                              color: colors.textHint,
                                            ),
                                          ),
                                          padding: EdgeInsets.all(16.r),
                                          child: CustomNetworkImage(
                                            url: state
                                                .categoriesOfService[index]
                                                .img,
                                            height: 40,
                                            width: 40,
                                          ),
                                        ),
                                        10.verticalSpace,
                                        SizedBox(
                                          width: 80.r,
                                          child: Text(
                                            state
                                                    .categoriesOfService[index]
                                                    .translation
                                                    ?.title ??
                                                "",
                                            style: CustomStyle.interNormal(
                                              color: colors.textBlack,
                                              size: 12,
                                            ),
                                            maxLines: 2,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              )
            : const SizedBox.shrink();
      },
    );
  }
}
