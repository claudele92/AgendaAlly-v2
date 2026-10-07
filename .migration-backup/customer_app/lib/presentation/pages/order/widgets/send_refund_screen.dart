import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/order/order_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class SendRefundScreen extends StatefulWidget {
  final int orderId;
  final CustomColorSet colors;

  const SendRefundScreen({
    super.key,
    required this.colors,
    required this.orderId,
  });

  @override
  State<SendRefundScreen> createState() => _SendRefundScreenState();
}

class _SendRefundScreenState extends State<SendRefundScreen> {
  late GlobalKey<FormState> form = GlobalKey<FormState>();
  late TextEditingController controller;

  @override
  void initState() {
    controller = TextEditingController();
    super.initState();
  }

  @override
  void deactivate() {
    controller.dispose();

    super.deactivate();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(16.r),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(8.r),
        color: widget.colors.backgroundColor,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            AppHelpers.getTranslation(TrKeys.whyDoYouWantToRefund),
            style: CustomStyle.interNormal(
              color: widget.colors.textBlack,
              size: 20,
            ),
          ),
          16.verticalSpace,
          Form(
            key: form,
            child: CustomTextFormField(
              controller: controller,
              validation: AppValidators.isNotEmptyValidator,
              hint: AppHelpers.getTranslation(TrKeys.reason),
            ),
          ),
          24.verticalSpace,
          BlocBuilder<OrderBloc, OrderState>(
            builder: (context, state) {
              return CustomButton(
                isLoading: state.isButtonLoading,
                title: AppHelpers.getTranslation(TrKeys.send),
                bgColor: widget.colors.primary,
                titleColor: widget.colors.white,
                onTap: () {
                  if (form.currentState?.validate() ?? false) {
                    context.read<OrderBloc>().add(
                      OrderEvent.refundOrder(
                        context,
                        id: widget.orderId,
                        reason: controller.text,
                        onSuccess: () {
                          Navigator.pop(context);
                        },
                      ),
                    );
                  }
                },
              );
            },
          ),
        ],
      ),
    );
  }
}
