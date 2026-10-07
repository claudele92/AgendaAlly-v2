import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/profile/profile_bloc.dart';
import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/infrastructure/service/services.dart';

import 'package:demand/presentation/components/components.dart';

import 'package:demand/presentation/pages/my_digital_files/digital_item.dart';
import 'package:demand/presentation/pages/notification/widgets/notification_shimmer.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:lottie/lottie.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../app_assets.dart';

class MyDigitalList extends StatefulWidget {
  const MyDigitalList({super.key});

  @override
  State<MyDigitalList> createState() => _MyDigitalListState();
}

class _MyDigitalListState extends State<MyDigitalList> {
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
    context.read<ProfileBloc>().add(
      ProfileEvent.fetchDigitalList(context, controller: refreshController),
    );
  }

  void onRefresh(RefreshController refreshController) {
    context.read<ProfileBloc>().add(
      ProfileEvent.fetchDigitalList(
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
            Text(
              AppHelpers.getTranslation(TrKeys.myDigitalList),
              style: CustomStyle.interNoSemi(color: colors.textBlack, size: 18),
            ),
            8.verticalSpace,
            BlocBuilder<ProfileBloc, ProfileState>(
              builder: (context, state) {
                return state.isDigitalLoading
                    ? const NotificationShimmer()
                    : state.digitalList.isNotEmpty
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
                            padding: EdgeInsets.only(
                              bottom: 100.r,
                              left: 16.r,
                              right: 16.r,
                            ),
                            shrinkWrap: true,
                            itemCount: state.digitalList.length,
                            itemBuilder: (context, index) {
                              return DigitalItem(
                                product: state.digitalList[index],
                                colors: colors,
                                download: () async {
                                  final res = await userRepository
                                      .getDigitalProduct(
                                        id: state.digitalList[index].id ?? 0,
                                      );
                                  res.fold((l) {
                                    Future.delayed(
                                      const Duration(seconds: 2),
                                      () {
                                        if (context.mounted) {
                                          AppHelpers.errorSnackBar(
                                            context,
                                            message: AppHelpers.getTranslation(
                                              TrKeys
                                                  .youCheckYourFileProductIsDownloaded,
                                            ),
                                          );
                                        }
                                      },
                                    );
                                  }, (r) => null);
                                },
                                progress: null,
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
    return SizedBox(
      width: MediaQuery.sizeOf(context).width,
      child: Column(
        children: [
          32.verticalSpace,
          Lottie.asset(
            Assets.lottieNotificationEmpty,
            width: MediaQuery.sizeOf(context).width / 1.5,
          ),
          32.verticalSpace,
          Text(
            AppHelpers.getTranslation(TrKeys.yourDigitalListIsEmpty),
            style: CustomStyle.interNoSemi(color: colors.textBlack, size: 18),
          ),
        ],
      ),
    );
  }
}
