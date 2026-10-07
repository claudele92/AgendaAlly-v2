import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:demand/application/cart/cart_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class CouponWidget extends StatefulWidget {
  final CustomColorSet colors;
  final int shopId;

  const CouponWidget({super.key, required this.colors, required this.shopId});

  @override
  State<CouponWidget> createState() => _CouponWidgetState();
}

class _CouponWidgetState extends State<CouponWidget> {
  final Delayed _delayed = Delayed(milliseconds: 700);

  @override
  Widget build(BuildContext context) {
    return CustomTextFormField(
      prefixIcon: Icon(
        FlutterRemix.coupon_3_fill,
        color: widget.colors.textBlack,
      ),
      hint: AppHelpers.getTranslation(TrKeys.coupon),
      onChanged: (text) {
        _delayed.run(() {
          context.read<CartBloc>().add(
            CartEvent.checkCoupon(
              context,
              coupon: text.trim(),
              shopId: widget.shopId,
            ),
          );
        });
      },
      suffixIcon: BlocBuilder<CartBloc, CartState>(
        buildWhen: (p, n) {
          return p.coupons != n.coupons;
        },
        builder: (context, state) {
          return Icon(
            FlutterRemix.check_double_line,
            color: state.coupons.containsKey(widget.shopId)
                ? widget.colors.primary
                : widget.colors.textHint,
          );
        },
      ),
    );
  }
}
