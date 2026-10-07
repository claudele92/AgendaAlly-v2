import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/profile/profile_bloc.dart';
import 'package:demand/application/wallet/wallet_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/pages/notification/widgets/notification_shimmer.dart';
import 'package:demand/presentation/pages/transactions/widgets/transaction_item.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:lottie/lottie.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../app_assets.dart';
import 'fill_wallet_screen.dart';
import 'send_price_screen.dart';

class TransactionList extends StatefulWidget {
  const TransactionList({super.key});

  @override
  State<TransactionList> createState() => _TransactionListState();
}

class _TransactionListState extends State<TransactionList> {
  late RefreshController controller;

  @override
  void initState() {
    controller = RefreshController();
    super.initState();
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  void onLoading(RefreshController refreshController) {
    context.read<WalletBloc>().add(
      WalletEvent.fetchTransactions(context, controller: refreshController),
    );
  }

  void onRefresh(RefreshController refreshController) {
    context.read<WalletBloc>().add(
      WalletEvent.fetchTransactions(
        context,
        controller: refreshController,
        isRefresh: true,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => Padding(
        padding: EdgeInsets.only(top: MediaQuery.paddingOf(context).top),
        child: Column(
          children: [
            Row(
              children: [
                16.horizontalSpace,
                Text(
                  AppHelpers.getTranslation(TrKeys.transactions),
                  style: CustomStyle.interNoSemi(
                    color: colors.textBlack,
                    size: 18,
                  ),
                ),
                const Spacer(),
                SecondButton(
                  title: AppHelpers.getTranslation(TrKeys.send),
                  bgColor: colors.primary,
                  titleColor: colors.white,
                  onTap: () {
                    AppHelpers.showCustomModalBottomSheetDrag(
                      context,
                      modal: (c) => MultiBlocProvider(
                        providers: [
                          BlocProvider.value(
                            value: context.read<ProfileBloc>(),
                          ),
                          BlocProvider.value(value: context.read<WalletBloc>()),
                        ],
                        child: SenPriceScreen(colors: colors),
                      ),
                    );
                  },
                ),
                8.horizontalSpace,
                SecondButton(
                  title: AppHelpers.getTranslation(TrKeys.add),
                  bgColor: colors.primary,
                  titleColor: colors.white,
                  onTap: () {
                    AppHelpers.showCustomModalBottomSheetDrag(
                      context,
                      modal: (c) => MultiBlocProvider(
                        providers: [
                          BlocProvider.value(
                            value: context.read<ProfileBloc>(),
                          ),
                          BlocProvider.value(value: context.read<WalletBloc>()),
                        ],
                        child: FillWalletScreen(colors: colors),
                      ),
                    );
                  },
                ),
                16.horizontalSpace,
              ],
            ),
            8.verticalSpace,
            BlocBuilder<WalletBloc, WalletState>(
              builder: (context, state) {
                return state.isTransactionLoading
                    ? const NotificationShimmer()
                    : state.transactions.isNotEmpty
                    ? Expanded(
                        child: SmartRefresher(
                          controller: controller,
                          enablePullUp: true,
                          enablePullDown: true,
                          onLoading: () {
                            onLoading(controller);
                          },
                          onRefresh: () {
                            onRefresh(controller);
                          },
                          child: ListView.builder(
                            padding: EdgeInsets.only(bottom: 100.r),
                            shrinkWrap: true,
                            itemCount: state.transactions.length,
                            itemBuilder: (context, index) {
                              return TransactionItem(
                                colors: colors,
                                transaction: state.transactions[index],
                              );
                            },
                          ),
                        ),
                      )
                    : _empty(context, colors);
              },
            ),
          ],
        ),
      ),
      floatingButtonLocation: FloatingActionButtonLocation.startFloat,
      floatingButton: (colors) => PopButton(colors: colors),
    );
  }

  Widget _empty(BuildContext context, CustomColorSet colors) {
    return Column(
      children: [
        32.verticalSpace,
        Lottie.asset(
          Assets.lottieNotificationEmpty,
          width: MediaQuery.sizeOf(context).width / 1.5,
        ),
        32.verticalSpace,
        Text(
          AppHelpers.getTranslation(TrKeys.yourTransactionsListIsEmpty),
          style: CustomStyle.interNoSemi(color: colors.textBlack, size: 18),
        ),
      ],
    );
  }
}
