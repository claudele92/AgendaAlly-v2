import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/category/category_bloc.dart';
import 'package:demand/domain/model/model/location_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:osm_nominatim/osm_nominatim.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../app_assets.dart';

class MainSearchPage extends StatefulWidget {
  const MainSearchPage({super.key});

  @override
  State<MainSearchPage> createState() => _MainSearchPageState();
}

class _MainSearchPageState extends State<MainSearchPage> {
  late TextEditingController placeController;
  late TextEditingController dateController;
  late TextEditingController timeController;
  late RefreshController serviceController;
  Place? place;

  @override
  void initState() {
    placeController = TextEditingController();
    dateController = TextEditingController();
    timeController = TextEditingController();
    serviceController = RefreshController();
    super.initState();
  }

  @override
  void dispose() {
    placeController.dispose();
    dateController.dispose();
    timeController.dispose();
    serviceController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(
          image: DecorationImage(
            image: AssetImage(Assets.imagesSearchBg),
            fit: BoxFit.cover,
          ),
        ),
        child: SafeArea(
          bottom: false,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _appbar(context, colors),
              Expanded(
                child: SmartRefresher(
                  controller: serviceController,
                  enablePullUp: true,
                  onRefresh: () {
                    context.read<CategoryBloc>().add(
                      CategoryEvent.fetchCategoryOfService(
                        context,
                        isRefresh: true,
                        controller: serviceController,
                      ),
                    );
                  },
                  onLoading: () {
                    context.read<CategoryBloc>().add(
                      CategoryEvent.fetchCategoryOfService(
                        context,
                        controller: serviceController,
                      ),
                    );
                  },
                  child: ListView(
                    shrinkWrap: true,
                    children: [
                      24.verticalSpace,
                      Padding(
                        padding: EdgeInsets.symmetric(horizontal: 36.r),
                        child: Text(
                          AppHelpers.getTranslation(TrKeys.findTheBest),
                          textAlign: TextAlign.center,
                          style: CustomStyle.interNoSemi(
                            color: colors.textBlack,
                            size: 32,
                          ),
                        ),
                      ),
                      24.verticalSpace,
                      _searchInput(colors, context),
                      _services(colors),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  BlocBuilder<CategoryBloc, CategoryState> _services(CustomColorSet colors) {
    return BlocBuilder<CategoryBloc, CategoryState>(
      builder: (context, state) {
        return state.categoriesOfService.isNotEmpty
            ? Padding(
                padding: EdgeInsets.symmetric(horizontal: 16.r),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    16.verticalSpace,
                    Text(
                      AppHelpers.getTranslation(TrKeys.service),
                      style: CustomStyle.interNoSemi(
                        color: colors.textBlack,
                        size: 22,
                      ),
                    ),
                    12.verticalSpace,
                    GridView.builder(
                      padding: EdgeInsets.only(bottom: 100.r),
                      physics: const NeverScrollableScrollPhysics(),
                      shrinkWrap: true,
                      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                        childAspectRatio: 0.8.r,
                        crossAxisCount: 2,
                        mainAxisExtent: 134.r,
                      ),
                      itemCount: state.categoriesOfService.length,
                      itemBuilder: (context, index) {
                        final category = state.categoriesOfService[index];
                        return ButtonEffectAnimation(
                          onTap: () {
                            AppRoute.goSearchMap(
                              context,
                              category: category,
                              location: LocationModel(
                                longitude:
                                    (place?.lon ??
                                        LocalStorage.getLatLong()?.longitude) ??
                                    AppHelpers.getInitialLongitude(),
                                latitude:
                                    (place?.lat ??
                                        LocalStorage.getLatLong()?.latitude) ??
                                    AppHelpers.getInitialLatitude(),
                              ),
                            );
                          },
                          child: Container(
                            margin: EdgeInsets.all(8.r),
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(10.r),
                              color: ListConstants.serviceColors[(index % 11)],
                              image: DecorationImage(
                                image: AssetImage(
                                  "assets/images/service${(index % 11) + 1}.png",
                                ),
                                fit: BoxFit.cover,
                              ),
                            ),
                            child: Stack(
                              children: [
                                Positioned(
                                  bottom: 0,
                                  right: 10,
                                  child: CustomNetworkImage(
                                    url: category.img ?? "",
                                    fit: BoxFit.contain,
                                    height: 86,
                                    width: 96,
                                    radius: 10,
                                  ),
                                ),
                                Padding(
                                  padding: EdgeInsets.all(20.r),
                                  child: Text(
                                    category.translation?.title ?? "",
                                    style: CustomStyle.interNoSemi(
                                      color: colors.textBlack,
                                      size: 16,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  ],
                ),
              )
            : const SizedBox.shrink();
      },
    );
  }

  Widget _searchInput(CustomColorSet colors, BuildContext context) {
    return Container(
      margin: EdgeInsets.symmetric(horizontal: 16.r),
      padding: EdgeInsets.all(12.r),
      decoration: BoxDecoration(
        color: colors.textWhite,
        borderRadius: BorderRadius.circular(AppConstants.radius.r),
      ),
      child: Column(
        children: [
          CustomTextFormField(
            hint: AppHelpers.getTranslation(TrKeys.serviceBeauty),
            readOnly: true,
            onTap: () {
              AppRoute.goSearchPage(context);
            },
            prefixIcon: Icon(
              FlutterRemix.search_2_line,
              color: colors.textBlack,
            ),
          ),
          10.verticalSpace,
          CustomTextFormField(
            controller: placeController,
            hint: AppHelpers.getTranslation(TrKeys.where),
            readOnly: true,
            onTap: () async {
              final placeMap = await AppRoute.goSearchLocation(context);
              place = placeMap;
              placeController.text = placeMap.displayName;
            },
            prefixIcon: Icon(
              FlutterRemix.map_pin_line,
              color: colors.textBlack,
            ),
          ),
          10.verticalSpace,
          CustomTextFormField(
            controller: dateController,
            hint: AppHelpers.getTranslation(TrKeys.date),
            readOnly: true,
            onTap: () async {
              final DateTime? time = await AppRoute.goSelectDateSearch(
                context,
                colors: colors,
                dateTime: DateTime.now(),
              );
              if (time != null) {
                dateController.text = TimeService.dateFormatDMY(time);
              }
            },
            prefixIcon: Icon(
              FlutterRemix.calendar_2_line,
              color: colors.textBlack,
            ),
          ),
          10.verticalSpace,
          CustomTextFormField(
            controller: timeController,
            hint: AppHelpers.getTranslation(TrKeys.time),
            readOnly: true,
            prefixIcon: Icon(FlutterRemix.time_line, color: colors.textBlack),
            onTap: () async {
              final List list = await AppRoute.goSelectTime(context);
              timeController.text = "${list.first} - ${list.last}";
            },
          ),
          10.verticalSpace,
          CustomButton(
            title: AppHelpers.getTranslation(TrKeys.search),
            bgColor: colors.textBlack,
            titleColor: colors.textWhite,
            onTap: () {
              if (place == null) {
                AppRoute.goSearchMap(
                  context,
                  location: LocationModel(
                    longitude: place!.lon,
                    latitude: place!.lat,
                  ),
                );
              }
            },
          ),
        ],
      ),
    );
  }

  Widget _appbar(BuildContext context, CustomColorSet colors) {
    return Row(
      children: [
        IconButton(
          onPressed: () {
            Scaffold.of(context).openDrawer();
          },
          icon: Icon(FlutterRemix.menu_line, color: colors.textBlack),
        ),
      ],
    );
  }
}
