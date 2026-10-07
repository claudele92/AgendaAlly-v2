import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/application/checkout/checkout_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/pages/checkout/widget/address_item.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class AddressListService extends StatelessWidget {
  final CustomColorSet colors;
  final ScrollController controller;

  const AddressListService({
    super.key,
    required this.colors,
    required this.controller,
  });

  @override
  Widget build(BuildContext context) {
    return KeyboardDismisser(
      isLtr: LocalStorage.getLangLtr(),
      child: Container(
        width: MediaQuery.sizeOf(context).width,
        height: 400,
        decoration: BoxDecoration(
          color: colors.newBoxColor,
          borderRadius: BorderRadius.only(
            topRight: Radius.circular(24.r),
            topLeft: Radius.circular(24.r),
          ),
        ),
        padding: EdgeInsets.only(left: 16.r, right: 16.r),
        child: BlocBuilder<CheckoutBloc, CheckoutState>(
          builder: (context, state) {
            return ListView(
              controller: controller,
              children: [
                8.verticalSpace,
                Container(
                  height: 4.r,
                  margin: EdgeInsets.symmetric(
                    horizontal: MediaQuery.sizeOf(context).width / 2 - 48.r,
                  ),
                  decoration: BoxDecoration(
                    color: colors.icon,
                    borderRadius: BorderRadius.circular(100.r),
                  ),
                ),
                16.verticalSpace,
                Text(
                  AppHelpers.getTranslation(TrKeys.addressList),
                  style: CustomStyle.interNoSemi(
                    color: colors.textBlack,
                    size: 22,
                  ),
                ),
                24.verticalSpace,
                if (state.address.isNotEmpty)
                  Row(
                    children: [
                      Text(
                        AppHelpers.getTranslation(TrKeys.addressBilling),
                        style: CustomStyle.interNormal(color: colors.textBlack),
                      ),
                      const Spacer(),
                      ButtonEffectAnimation(
                        onTap: () async {
                          await AppRoute.goAddEditAddress(context);
                          if (context.mounted) {
                            context.read<CheckoutBloc>().add(
                              CheckoutEvent.fetchUserAddress(
                                context,
                                isRefresh: true,
                              ),
                            );
                          }
                        },
                        child: Text(
                          AppHelpers.getTranslation(TrKeys.addAddress),
                          style: CustomStyle.interRegular(
                            color: colors.primary,
                            size: 14,
                          ),
                        ),
                      ),
                    ],
                  ),
                24.verticalSpace,
                if (state.address.isEmpty)
                  Column(
                    children: [
                      Text(
                        AppHelpers.getTranslation(
                          TrKeys.thereAreNoYourDelivery,
                        ),
                        style: CustomStyle.interNoSemi(
                          color: colors.textBlack,
                          size: 16,
                        ),
                        textAlign: TextAlign.center,
                      ),
                      16.verticalSpace,
                      CustomButton(
                        title: AppHelpers.getTranslation(TrKeys.addAddress),
                        bgColor: colors.primary,
                        titleColor: colors.white,
                        onTap: () async {
                          if (LocalStorage.getToken().isNotEmpty) {
                            await AppRoute.goAddEditAddress(context);
                            if (context.mounted) {
                              context.read<CheckoutBloc>().add(
                                CheckoutEvent.fetchUserAddress(
                                  context,
                                  isRefresh: true,
                                ),
                              );
                            }
                          } else {
                            await AppRoute.goLogin(context);
                          }
                        },
                      ),
                    ],
                  ),
                ListView.builder(
                  padding: EdgeInsets.zero,
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: state.address.length,
                  itemBuilder: (context, index) {
                    return AddressItem(
                      colors: colors,
                      address: state.address[index],
                      active: state.selectAddress == index,
                      onTap: () {
                        context.read<CheckoutBloc>().add(
                          CheckoutEvent.selectAddress(index: index),
                        );

                        context.read<BookingBloc>().add(
                          BookingEvent.selectAddress(
                            address: state.address[index],
                          ),
                        );
                        Navigator.pop(context);
                      },
                      delete: () {
                        context.read<CheckoutBloc>().add(
                          CheckoutEvent.deleteAddress(
                            context,
                            index: index,
                            addressId: state.address[index].id ?? 0,
                          ),
                        );
                      },
                      edit: () async {
                        await AppRoute.goAddEditAddress(
                          context,
                          address: state.address[index],
                        );
                        if (context.mounted) {
                          context.read<CheckoutBloc>().add(
                            CheckoutEvent.fetchUserAddress(
                              context,
                              isRefresh: true,
                            ),
                          );
                        }
                      },
                    );
                  },
                ),
                16.verticalSpace,
              ],
            );
          },
        ),
      ),
    );
  }
}
