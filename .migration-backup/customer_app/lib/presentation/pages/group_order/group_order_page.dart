import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/app_constants.dart';
import 'package:demand/application/cart/cart_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:share_plus/share_plus.dart';

class GroupOrderPage extends StatefulWidget {
  final CustomColorSet colors;

  const GroupOrderPage({super.key, required this.colors});

  @override
  State<GroupOrderPage> createState() => _GroupOrderPageState();
}

class _GroupOrderPageState extends State<GroupOrderPage> {
  Timer? timer;

  @override
  void initState() {
    if (LocalStorage.getToken().isNotEmpty) {
      timer = Timer.periodic(
        Duration(seconds: AppConstants.timeRefresh.inSeconds),
        (Timer t) {
          context.read<CartBloc>().add(CartEvent.getCart(context));
        },
      );
    }
    super.initState();
  }

  @override
  void deactivate() {
    timer?.cancel();
    super.deactivate();
  }

  @override
  Widget build(BuildContext context) {
    return KeyboardDismisser(
      isLtr: LocalStorage.getLangLtr(),
      child: Container(
        margin: MediaQuery.viewInsetsOf(context),
        width: double.infinity,
        decoration: BoxDecoration(
          color: widget.colors.newBoxColor,
          borderRadius: BorderRadius.only(
            topRight: Radius.circular(24.r),
            topLeft: Radius.circular(24.r),
          ),
        ),
        padding: EdgeInsets.only(left: 16.r, right: 16.r, bottom: 24.r),
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              24.verticalSpace,
              Text(
                AppHelpers.getTranslation(TrKeys.groupOrder),
                style: CustomStyle.interNoSemi(
                  color: widget.colors.textBlack,
                  size: 24,
                ),
              ),
              8.verticalSpace,
              Text(
                AppHelpers.getTranslation(TrKeys.groupOrderText),
                style: CustomStyle.interNormal(color: widget.colors.textBlack),
              ),
              16.verticalSpace,
              BlocBuilder<CartBloc, CartState>(
                builder: (context, state) {
                  return (state.cart?.group ?? false)
                      ? _members(context, state)
                      : CustomButton(
                          isLoading: state.isButtonLoading,
                          title: AppHelpers.getTranslation(TrKeys.start),
                          bgColor: widget.colors.primary,
                          titleColor: widget.colors.white,
                          onTap: () {
                            context.read<CartBloc>().add(
                              CartEvent.startGroupOrder(
                                context,
                                onSuccess: () {
                                  context.read<CartBloc>().add(
                                    CartEvent.createLink(context),
                                  );
                                },
                              ),
                            );
                          },
                        );
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  Column _members(BuildContext context, CartState state) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (LocalStorage.getGroupOrder().id == null)
          CustomTextFormField(
            hint: state.groupOrderLink,
            readOnly: true,
            suffixIcon: IconButton(
              onPressed: () {
                SharePlus.instance.share(
                  ShareParams(
                    text: state.groupOrderLink ?? "",
                    sharePositionOrigin: Rect.fromLTWH(0, 0, 100, 100),
                  ),
                );
              },
              icon: Icon(
                FlutterRemix.share_fill,
                color: widget.colors.textBlack,
              ),
            ),
          ),
        16.verticalSpace,
        Text(
          AppHelpers.getTranslation(TrKeys.members),
          style: CustomStyle.interNormal(color: widget.colors.textBlack),
        ),
        6.verticalSpace,
        ListView.builder(
          physics: const NeverScrollableScrollPhysics(),
          shrinkWrap: true,
          padding: EdgeInsets.zero,
          itemCount: state.cart?.userCarts?.length ?? 0,
          itemBuilder: (context, index) {
            return Padding(
              padding: const EdgeInsets.all(4.0),
              child: Column(
                children: [
                  Row(
                    children: [
                      CustomNetworkImage(
                        url: "",
                        height: 36,
                        width: 36,
                        radius: 18,
                        name: state.cart?.userCarts?[index].name,
                      ),
                      16.horizontalSpace,
                      Text(
                        state.cart?.userCarts?[index].name ?? "",
                        style: CustomStyle.interNormal(
                          color: widget.colors.textBlack,
                          size: 14,
                        ),
                      ),
                      const Spacer(),
                      if (state.cart?.userCarts?[index].userId !=
                              state.cart?.ownerId &&
                          LocalStorage.getGroupOrder().id == null)
                        IconButton(
                          onPressed: () {
                            context.read<CartBloc>().add(
                              CartEvent.deleteUser(
                                context,
                                uuid: state.cart?.userCarts?[index].uuid ?? "",
                                onSuccess: () {
                                  context.read<CartBloc>().add(
                                    CartEvent.getCart(context, refresh: true),
                                  );
                                },
                              ),
                            );
                          },
                          icon: Icon(
                            FlutterRemix.close_line,
                            color: widget.colors.textBlack,
                          ),
                        ),
                    ],
                  ),
                ],
              ),
            );
          },
        ),
        16.verticalSpace,
        CustomButton(
          changeColor: true,
          isLoading: state.isButtonLoading,
          title: LocalStorage.getGroupOrder().id == null
              ? AppHelpers.getTranslation(TrKeys.cancel)
              : AppHelpers.getTranslation(TrKeys.leave),
          bgColor: widget.colors.textBlack,
          titleColor: widget.colors.textWhite,
          onTap: () {
            if (LocalStorage.getGroupOrder().id != null) {
              LocalStorage.deleteGroupOrder();
              return;
            }
            context.read<CartBloc>().add(CartEvent.deleteCart(context));
          },
        ),
      ],
    );
  }
}
