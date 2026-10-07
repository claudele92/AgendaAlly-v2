import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/compare/compare_bloc.dart';
import 'package:demand/domain/model/model/product_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class MainInfo extends StatelessWidget {
  final CustomColorSet colors;
  final ProductData product;

  const MainInfo({super.key, required this.colors, required this.product});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _category(),
        const Divider(),
        _brand(),
        const Divider(),
        BlocBuilder<CompareBloc, CompareState>(
          builder: (context, state) {
            return ListView.builder(
              physics: const NeverScrollableScrollPhysics(),
              shrinkWrap: true,
              itemCount: state.extraGroup.length,
              itemBuilder: (context, index) {
                Set<String> extra = {};
                bool isColor = state.extraGroup[index].type == "color";

                for (
                  int i = 0;
                  i < (state.extraGroup[index].values?.length ?? 0);
                  i++
                ) {
                  if (state.extraGroup[index].values?[i][product.id] != null) {
                    extra.add(
                      state.extraGroup[index].values?[i][product.id] ?? "",
                    );
                  }
                }
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      state.extraGroup[index].translation?.title ?? "",
                      style: CustomStyle.interRegular(color: colors.textHint),
                    ),
                    8.verticalSpace,
                    extra.isNotEmpty
                        ? Wrap(
                            children: extra.map((value) {
                              return value.isNotEmpty
                                  ? Container(
                                      margin: EdgeInsets.all(2.r),
                                      height: 24.r,
                                      width: isColor ? 24.r : 36.r,
                                      decoration: BoxDecoration(
                                        color: isColor
                                            ? AppHelpers.checkIfHex(value)
                                                  ? Color(
                                                      int.parse(
                                                        '0xFF${value.substring(1, 7)}',
                                                      ),
                                                    )
                                                  : colors.primary
                                            : colors.transparent,
                                        border: Border.all(
                                          color: colors.textHint,
                                        ),
                                        borderRadius: BorderRadius.circular(
                                          4.r,
                                        ),
                                      ),
                                      child: isColor
                                          ? null
                                          : Center(
                                              child: Text(
                                                value,
                                                style: CustomStyle.interNormal(
                                                  color: colors.textBlack,
                                                ),
                                              ),
                                            ),
                                    )
                                  : const SizedBox.shrink();
                            }).toList(),
                          )
                        : Text(
                            AppHelpers.getTranslation(TrKeys.noInfo),
                            style: CustomStyle.interNormal(
                              color: colors.textBlack,
                            ),
                          ),
                    const Divider(),
                  ],
                );
              },
            );
          },
        ),
      ],
    );
  }

  Column _brand() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          AppHelpers.getTranslation(TrKeys.brand),
          style: CustomStyle.interRegular(color: colors.textHint),
        ),
        8.verticalSpace,
        Text(
          product.brand?.title ?? AppHelpers.getTranslation(TrKeys.noInfo),
          style: CustomStyle.interNormal(color: colors.textBlack),
        ),
      ],
    );
  }

  Column _category() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          AppHelpers.getTranslation(TrKeys.categories),
          style: CustomStyle.interRegular(color: colors.textHint),
        ),
        8.verticalSpace,
        Text(
          product.category?.translation?.title ?? "",
          style: CustomStyle.interNormal(color: colors.textBlack),
        ),
      ],
    );
  }
}
