import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_svg/flutter_svg.dart';
import 'package:demand/application/category/category_bloc.dart';
import 'package:demand/application/main/main_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/components/sub_category_item.dart';

import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

import '../../../app_assets.dart';

class SubCategoryList extends StatelessWidget {
  final CustomColorSet colors;

  const SubCategoryList({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<CategoryBloc, CategoryState>(
      builder: (context, state) {
        return state.selectCategory != null &&
                (state.selectCategory?.children?.isNotEmpty ?? true)
            ? Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  16.verticalSpace,
                  TitleWidget(
                    title: AppHelpers.getTranslation(TrKeys.categories),
                    titleColor: colors.textBlack,
                    subTitle: AppHelpers.getTranslation(TrKeys.seeAll),
                    onTap: () {
                      context.read<MainBloc>().add(
                        const MainEvent.changeIndex(index: 1),
                      );
                      context.read<CategoryBloc>().add(
                        CategoryEvent.selectCategoryTwo(
                          context,
                          category: state.selectCategory,
                        ),
                      );
                    },
                  ),
                  12.verticalSpace,
                  SizedBox(
                    height: 102.r,
                    child: ListView.builder(
                      padding: EdgeInsets.symmetric(horizontal: 16.r),
                      shrinkWrap: true,
                      scrollDirection: Axis.horizontal,
                      itemCount:
                          (state.selectCategory?.children?.length ?? 0) + 1,
                      itemBuilder: (context, index) {
                        return index == 0
                            ? Container(
                                margin: EdgeInsets.only(right: 8.r),
                                padding: EdgeInsets.all(12.r),
                                width: 50.r,
                                decoration: BoxDecoration(
                                  color: CustomStyle.subCategory,
                                  borderRadius: BorderRadius.circular(
                                    AppConstants.radius.r,
                                  ),
                                ),
                                child: SvgPicture.asset(Assets.svgFilter),
                              )
                            : Padding(
                                padding: EdgeInsets.only(right: 8.r),
                                child: SubCategoryItem(
                                  categoryData: state
                                      .selectCategory
                                      ?.children?[index - 1],
                                ),
                              );
                      },
                    ),
                  ),
                ],
              )
            : const SizedBox.shrink();
      },
    );
  }
}
