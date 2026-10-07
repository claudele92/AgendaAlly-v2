import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/filter/filter_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:provider/provider.dart';

class RangePrice extends StatefulWidget {
  final CustomColorSet colors;
  final FilterState state;

  const RangePrice({super.key, required this.colors, required this.state});

  @override
  State<RangePrice> createState() => _RangePriceState();
}

class _RangePriceState extends State<RangePrice> {
  final _delayed = Delayed(milliseconds: 700);

  bool changeColor(int i) {
    return ((((widget.state.rangeValues?.start ?? 0) -
                        (widget.state.filterPrices?.min ?? 0)) /
                    (((widget.state.filterPrices?.max ?? 0) -
                            (widget.state.filterPrices?.min ?? 0)) /
                        30))
                .round() <=
            i) &&
        (((widget.state.rangeValues?.end ?? 100) /
                    ((widget.state.filterPrices?.max ?? 0) / 30))
                .round() >=
            i);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(16.r),
      decoration: BoxDecoration(
        color: widget.colors.newBoxColor,
        borderRadius: BorderRadius.circular(AppConstants.radius.r),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            AppHelpers.getTranslation(TrKeys.priceRange),
            style: CustomStyle.interNoSemi(
              size: 16,
              color: widget.colors.textBlack,
            ),
          ),
          Text(
            "${AppHelpers.numberFormat(decimalDigits: 0, widget.state.rangeValues?.start)} ${AppHelpers.getTranslation(TrKeys.to).toLowerCase()} ${AppHelpers.numberFormat(widget.state.rangeValues?.end, decimalDigits: 0)} ${AppHelpers.getTranslation(TrKeys.selected).toLowerCase()}",
            style: CustomStyle.interNormal(
              size: 14,
              color: widget.colors.textHint,
            ),
          ),
          18.verticalSpace,
          Column(
            children: [
              Padding(
                padding: EdgeInsets.symmetric(horizontal: 16.r),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    for (int i = 0; i < widget.state.prices.length; i++)
                      Container(
                        width: 8.w,
                        height: 100.h / widget.state.prices[i],
                        decoration: BoxDecoration(
                          color: changeColor(i)
                              ? CustomStyle.primary
                              : widget.colors.backgroundColor,
                          borderRadius: BorderRadius.circular(48.r),
                        ),
                      ),
                  ],
                ),
              ),
              8.verticalSpace,
              RangeSlider(
                activeColor: widget.colors.textBlack,
                inactiveColor: widget.colors.textWhite,
                min: (widget.state.filterPrices?.min ?? 0).toDouble(),
                max: (widget.state.filterPrices?.max ?? 100).toDouble(),
                values: widget.state.rangeValues ?? const RangeValues(0, 100),
                onChanged: (value) {
                  context.read<FilterBloc>().add(
                    FilterEvent.setRangePrice(
                      price: RangeValues(value.start, value.end),
                    ),
                  );

                  _delayed.run(() {
                    context.read<FilterBloc>().add(
                      FilterEvent.fetchExtras(context, isPrice: false),
                    );
                  });
                },
              ),
            ],
          ),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                AppHelpers.numberFormat(widget.state.rangeValues?.start),
                style: CustomStyle.interNormal(
                  size: 14,
                  color: widget.colors.textBlack,
                ),
              ),
              Text(
                AppHelpers.numberFormat(widget.state.rangeValues?.end),
                style: CustomStyle.interNormal(
                  size: 12.sp,
                  color: widget.colors.textBlack,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
