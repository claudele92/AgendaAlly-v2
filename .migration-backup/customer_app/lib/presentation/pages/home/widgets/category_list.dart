import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/category/category_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';

import 'package:demand/presentation/components/shimmer/category_shimmer.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

class CategoryList extends StatelessWidget {
  final RefreshController categoryRefresh;
  final CustomColorSet colors;
  final bool onlyCategory;

  const CategoryList({
    super.key,
    required this.categoryRefresh,
    required this.colors,
    this.onlyCategory = false,
  });

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<CategoryBloc, CategoryState>(
      builder: (context, state) {
        return state.categories.isNotEmpty || state.isLoadingCategory
            ? Column(
                children: [
                  SizedBox(
                    height: 40.r,
                    child: Row(
                      children: [
                        if (state.categories.isNotEmpty)
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
                              child: onlyCategory
                                  ? ListView.builder(
                                      key: const PageStorageKey<String>("list"),
                                      shrinkWrap: true,
                                      padding: EdgeInsets.symmetric(
                                        horizontal: 16.r,
                                      ),
                                      scrollDirection: Axis.horizontal,
                                      itemCount: state.categories.length,
                                      itemBuilder: (context, index) {
                                        return SecondButton(
                                          title:
                                              state
                                                  .categories[index]
                                                  .translation
                                                  ?.title ??
                                              "",
                                          bgColor:
                                              state.selectCategoryTwo?.id ==
                                                  state.categories[index].id
                                              ? colors.categoryColor
                                              : CustomStyle.transparent,
                                          titleColor:
                                              state.selectCategoryTwo?.id !=
                                                  state.categories[index].id
                                              ? colors.categoryTitleColor
                                              : colors.white,
                                          onTap: () {
                                            if (state.categories[index].id !=
                                                state.selectCategoryTwo?.id) {
                                              context.read<CategoryBloc>().add(
                                                CategoryEvent.selectCategoryTwo(
                                                  context,
                                                  category:
                                                      state.categories[index],
                                                ),
                                              );
                                            }
                                          },
                                        );
                                      },
                                    )
                                  : ListView.builder(
                                      shrinkWrap: true,
                                      padding: EdgeInsets.symmetric(
                                        horizontal: 16.r,
                                      ),
                                      scrollDirection: Axis.horizontal,
                                      itemCount: state.categories.length + 1,
                                      itemBuilder: (context, index) {
                                        return index == 0
                                            ? SecondButton(
                                                title:
                                                    AppHelpers.getTranslation(
                                                      TrKeys.all,
                                                    ),
                                                bgColor:
                                                    state.selectCategory?.id ==
                                                        null
                                                    ? colors.categoryColor
                                                    : CustomStyle.transparent,
                                                titleColor:
                                                    state.selectCategory?.id !=
                                                        null
                                                    ? colors.categoryTitleColor
                                                    : colors.white,
                                                onTap: () {
                                                  context.read<CategoryBloc>().add(
                                                    CategoryEvent.selectCategory(
                                                      context,
                                                    ),
                                                  );
                                                },
                                              )
                                            : SecondButton(
                                                title:
                                                    state
                                                        .categories[index - 1]
                                                        .translation
                                                        ?.title ??
                                                    "",
                                                bgColor:
                                                    state.selectCategory?.id ==
                                                        state
                                                            .categories[index -
                                                                1]
                                                            .id
                                                    ? colors.categoryColor
                                                    : CustomStyle.transparent,
                                                titleColor:
                                                    state.selectCategory?.id !=
                                                        state
                                                            .categories[index -
                                                                1]
                                                            .id
                                                    ? colors.categoryTitleColor
                                                    : colors.white,
                                                onTap: () {
                                                  if (state
                                                          .categories[index - 1]
                                                          .id !=
                                                      state
                                                          .selectCategory
                                                          ?.id) {
                                                    context.read<CategoryBloc>().add(
                                                      CategoryEvent.selectCategory(
                                                        context,
                                                        category: state
                                                            .categories[index - 1],
                                                      ),
                                                    );
                                                  }
                                                },
                                              );
                                      },
                                    ),
                            ),
                          ),
                        if (state.isLoadingCategory) const CategoryShimmer(),
                      ],
                    ),
                  ),
                  8.verticalSpace,
                  const Divider(color: CustomStyle.dividerColor),
                  8.verticalSpace,
                ],
              )
            : const SizedBox.shrink();
      },
    );
  }
}
