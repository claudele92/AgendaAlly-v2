import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/product_detail/product_detail_bloc.dart';
import 'package:demand/domain/model/model/product_model.dart';
import 'package:demand/domain/model/model/review_data.dart';
import 'package:demand/domain/model/model/typed_extra.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/extras/color_one_extras.dart';
import 'package:demand/presentation/components/extras/image_extras.dart';
import 'package:demand/presentation/components/extras/text_one_extras.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:provider/provider.dart';

class ProductExtrasOne extends StatelessWidget {
  final List<TypedExtra> types;
  final List<Stocks> stocks;
  final Stocks? selectStock;
  final List<Galleries> list;
  final CustomColorSet colors;

  const ProductExtrasOne({
    super.key,
    required this.types,
    required this.colors,
    required this.stocks,
    required this.list,
    required this.selectStock,
  });

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      padding: EdgeInsets.symmetric(horizontal: 16.r),
      physics: const NeverScrollableScrollPhysics(),
      shrinkWrap: true,
      itemCount: types.length,
      itemBuilder: (context, index) {
        final TypedExtra typedExtra = types[index];
        return Container(
          padding: REdgeInsets.symmetric(vertical: 8),
          margin: REdgeInsets.only(top: 8),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                "${typedExtra.title} : ${AppHelpers.getNameColor(typedExtra.uiExtras.firstWhere((element) => element.isSelected == true, orElse: () => UiExtra("", false, index)).value)}",
                style: CustomStyle.interNoSemi(
                  size: 16,
                  color: colors.textBlack,
                  letterSpacing: -0.4,
                ),
              ),
              16.verticalSpace,
              typedExtra.type == ExtrasType.text
                  ? TextOneExtras(
                      list: list,
                      selectStock: selectStock,
                      uiExtras: typedExtra.uiExtras,
                      groupIndex: typedExtra.groupIndex,
                      onUpdate: (uiExtra) {
                        context.read<ProductDetailBloc>().add(
                          ProductDetailEvent.updateSelectedIndexes(
                            context,
                            typedExtra.groupIndex,
                            uiExtra.index,
                          ),
                        );
                      },
                    )
                  : typedExtra.type == ExtrasType.color
                  ? ColorOneExtras(
                      stocks: stocks,
                      selectStock: selectStock,
                      uiExtras: typedExtra.uiExtras,
                      groupIndex: typedExtra.groupIndex,
                      onUpdate: (uiExtra) {
                        context.read<ProductDetailBloc>().add(
                          ProductDetailEvent.updateSelectedIndexes(
                            context,
                            typedExtra.groupIndex,
                            uiExtra.index,
                          ),
                        );
                      },
                    )
                  : typedExtra.type == ExtrasType.image
                  ? ImageExtras(
                      selectStock: selectStock,
                      uiExtras: typedExtra.uiExtras,
                      groupIndex: typedExtra.groupIndex,
                      updateImage: (s) {},
                      onUpdate: (uiExtra) {
                        context.read<ProductDetailBloc>().add(
                          ProductDetailEvent.updateSelectedIndexes(
                            context,
                            typedExtra.groupIndex,
                            uiExtra.index,
                          ),
                        );
                      },
                    )
                  : const SizedBox(),
            ],
          ),
        );
      },
    );
  }
}
