import 'dart:async';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:demand/application/checkout/checkout_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

class DeliveryTipScreen extends StatefulWidget {
  final BuildContext context;
  final CustomColorSet colors;

  const DeliveryTipScreen({
    super.key,
    required this.context,
    required this.colors,
  });

  @override
  State<DeliveryTipScreen> createState() => _DeliveryTipScreenState();
}

class _DeliveryTipScreenState extends State<DeliveryTipScreen> {
  List<int> tips = [3, 5, 10, -1];
  TextEditingController controller = TextEditingController();
  Timer timer = Timer(const Duration(seconds: 1), () {});

  @override
  void initState() {
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return BlocProvider.value(
      value: BlocProvider.of<CheckoutBloc>(widget.context),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 15),
        child: BlocBuilder<CheckoutBloc, CheckoutState>(
          builder: (context, state) {
            controller = TextEditingController(text: state.tips.toString());
            return Padding(
              padding: EdgeInsets.only(
                bottom: MediaQuery.viewInsetsOf(context).bottom,
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    AppHelpers.getTranslation(TrKeys.wouldYouLikeToAddATip),
                    style: CustomStyle.interNormal(
                      color: widget.colors.textBlack,
                    ),
                  ),
                  12.verticalSpace,
                  ...List.generate(tips.length, (index) {
                    final tip = tips[index];
                    bool selected = index == tips.indexOf(state.tips);
                    if (tips.length - 1 == index) {
                      selected = (!tips.contains(state.tips));
                    }
                    return Padding(
                      padding: const EdgeInsets.symmetric(vertical: 3),
                      child: CustomButton(
                        title: tip == -1
                            ? AppHelpers.getTranslation(TrKeys.custom)
                            : AppHelpers.numberFormat(tip),
                        bgColor: selected
                            ? CustomStyle.primary
                            : Colors.transparent,
                        titleColor: selected
                            ? widget.colors.textWhite
                            : widget.colors.textBlack,
                        borderColor: selected
                            ? CustomStyle.primary
                            : widget.colors.textBlack,
                        onTap: () {
                          if (!selected) {
                            context.read<CheckoutBloc>().add(
                              CheckoutEvent.setDeliveryTip(
                                tips: tip != -1 ? tip : 0,
                              ),
                            );
                          }
                        },
                      ),
                    );
                  }),
                  if (!tips.contains(state.tips))
                    Padding(
                      padding: REdgeInsets.only(top: 5),
                      child: CustomTextFormField(
                        controller: controller,
                        inputType: TextInputType.number,
                        prefixIcon:
                            LocalStorage.getSelectedCurrency()?.position ==
                                'after'
                            ? null
                            : SizedBox(
                                height: 50,
                                width: 50,
                                child: Center(
                                  child: Text(
                                    LocalStorage.getSelectedCurrency()
                                            ?.symbol ??
                                        '',
                                    style: CustomStyle.interNormal(
                                      color: widget.colors.textBlack,
                                    ),
                                  ),
                                ),
                              ),
                        suffixIcon:
                            LocalStorage.getSelectedCurrency()?.position ==
                                'before'
                            ? null
                            : SizedBox(
                                height: 50,
                                width: 50,
                                child: Center(
                                  child: Text(
                                    LocalStorage.getSelectedCurrency()
                                            ?.symbol ??
                                        '',
                                    style: CustomStyle.interNormal(
                                      color: widget.colors.textBlack,
                                    ),
                                  ),
                                ),
                              ),
                        onChanged: (p0) {
                          timer.cancel();

                          timer = Timer(const Duration(milliseconds: 500), () {
                            context.read<CheckoutBloc>().add(
                              CheckoutEvent.setDeliveryTip(
                                tips: int.tryParse(p0) ?? 0,
                              ),
                            );
                          });
                        },
                      ),
                    ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }
}
