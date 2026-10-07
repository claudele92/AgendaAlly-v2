import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:demand/application/map/map_bloc.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/domain/model/model/location_model.dart';
import 'package:demand/domain/model/response/categories_paginate_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/pages/search/widgets/search_widget.dart';
import 'package:demand/presentation/route/app_route_shop.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

class SearchMapPage extends StatefulWidget {
  final LocationModel? location;
  final List<int>? categoryIds;
  final CategoryData? category;

  const SearchMapPage({
    super.key,
    this.location,
    this.category,
    this.categoryIds,
  });

  @override
  State<SearchMapPage> createState() => _SearchMapPageState();
}

class _SearchMapPageState extends State<SearchMapPage> {
  CameraPosition? _cameraPosition;
  late RefreshController shopsRefresh;
  final ImageCropperForMarker image = ImageCropperForMarker();
  final Delayed _delayed = Delayed(milliseconds: 500);
  CategoryData? _selectedCategory;
  int? _selectedSubCategoryId;
  late final LatLng _initialPosition;

  @override
  void initState() {
    super.initState();
    shopsRefresh = RefreshController();
    _initialPosition = widget.location == null
        ? LatLng(
            AppHelpers.getInitialLatitude(),
            AppHelpers.getInitialLongitude(),
          )
        : LatLng(
            widget.location?.latitude ?? AppHelpers.getInitialLatitude(),
            widget.location?.longitude ?? AppHelpers.getInitialLongitude(),
          );

    if (widget.category != null) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _loadCategoryChildren();
      });
    }
  }

  void _loadCategoryChildren() {
    _selectedCategory = widget.category;
  }

  void _onCategorySelected(int? categoryId) {
    if (_selectedSubCategoryId == categoryId) return; // Prevent duplicate calls

    setState(() {
      _selectedSubCategoryId = categoryId;
    });

    _fetchShopsWithDelay(categoryId);
  }

  void _fetchShopsWithDelay(int? categoryId) {
    _delayed.run(() {
      if (!mounted) return;

      context.read<ShopBloc>().add(
        ShopEvent.fetchNearShops(
          context,
          isRefresh: true,
          onSuccess: () {},
          categoryIds: _getCategoryIds(),
          location: _getCurrentLocation(),
        ),
      );
    });
  }

  LocationModel _getCurrentLocation() {
    return LocationModel(
      latitude: _cameraPosition?.target.latitude ?? AppHelpers.getInitialLatitude(),
      longitude: _cameraPosition?.target.longitude ?? AppHelpers.getInitialLongitude(),
    );
  }

  @override
  void dispose() {
    shopsRefresh.dispose();
    _delayed.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => Stack(
        children: [
          _map(context),
          SearchWidget(colors: colors),
          _shops(colors),
        ],
      ),
      floatingButtonLocation: FloatingActionButtonLocation.startFloat,
      floatingButton: (colors) => PopButton(colors: colors),
    );
  }

  Widget _map(BuildContext context) {
    return BlocBuilder<MapBloc, MapState>(
      builder: (context, state) {
        return GoogleMap(
          tiltGesturesEnabled: false,
          myLocationEnabled: true,
          myLocationButtonEnabled: false,
          onTap: (location) {
            context.read<MapBloc>().add(
              MapEvent.goToMyLocation(location: location),
            );
          },
          zoomControlsEnabled: false,
          initialCameraPosition: CameraPosition(
            bearing: 0,
            target: _initialPosition,
            tilt: 0,
            zoom: 17,
          ),
          markers: state.markers,
          onMapCreated: (controller) {
            context.read<MapBloc>().add(MapEvent.setMapController(controller));
          },
          onCameraIdle: () {
            _delayed.run(() {
              if (!mounted) return;
              context.read<ShopBloc>().add(
                ShopEvent.fetchNearShops(
                  context,
                  isRefresh: true,
                  onSuccess: () {},
                  categoryIds: _getCategoryIds(),
                  location: _getCurrentLocation(),
                ),
              );
            });
          },
          onCameraMove: (cameraPosition) {
            _cameraPosition = cameraPosition;
          },
        );
      },
    );
  }

  DraggableScrollableSheet _shops(CustomColorSet colors) {
    return DraggableScrollableSheet(
      initialChildSize: 0.4,
      maxChildSize: 0.85,
      minChildSize: 0.1,
      snap: true,
      builder: (BuildContext context, ScrollController scrollController) {
        return Container(
          width: MediaQuery.sizeOf(context).width,
          decoration: BoxDecoration(
            color: colors.backgroundColor,
            borderRadius: BorderRadius.only(
              topRight: Radius.circular(12.r),
              topLeft: Radius.circular(12.r),
            ),
            boxShadow: [
              BoxShadow(
                color: colors.backgroundColor.withValues(alpha: 0.25),
                blurRadius: 40,
                offset: const Offset(0, -2),
              ),
            ],
          ),
          child: SmartRefresher(
            controller: shopsRefresh,
            enablePullDown: false,
            enablePullUp: true,
            onLoading: () {
              if (!mounted) return;
              context.read<ShopBloc>().add(
                ShopEvent.fetchNearShops(
                  context,
                  controller: shopsRefresh,
                  location: _getCurrentLocation(),
                  categoryIds: _getCategoryIds(),
                  onSuccess: () {},
                ),
              );
            },
            child: ListView(
              controller: scrollController,
              padding: EdgeInsets.only(
                top: 8.h,
                bottom: MediaQuery.paddingOf(context).bottom + 16.h,
                left: 16.w,
                right: 16.w,
              ),
              children: [
                Container(
                  height: 4.r,
                  margin: EdgeInsets.symmetric(
                    horizontal: MediaQuery.sizeOf(context).width / 2 - 56.r,
                  ),
                  decoration: BoxDecoration(
                    color: colors.icon,
                    borderRadius: BorderRadius.circular(100.r),
                  ),
                ),
                16.verticalSpace,
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        AppHelpers.getTranslation(TrKeys.salonsNearYou),
                        style: CustomStyle.interNoSemi(
                          color: colors.textBlack,
                          size: 18,
                        ),
                      ),
                    ),
                    BlocBuilder<ShopBloc, ShopState>(
                      builder: (context, state) {
                        return ButtonEffectAnimation(
                          onTap: () {
                            AppRouteShop.goFilterShopBottomSheet(
                              context,
                              colors: colors,
                              filter: state.filter,
                              isNear: true,
                              location: _getCurrentLocation(),
                            );
                          },
                          child: Container(
                            height: 46.r,
                            width: 46.r,
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(10.r),
                              border: Border.all(color: colors.icon),
                            ),
                            child: Badge(
                              smallSize: 8,
                              isLabelVisible:
                                  state.filter?.type == TrKeys.nearByShop,
                              padding: REdgeInsets.all(2),
                              child: Icon(
                                FlutterRemix.filter_2_line,
                                color: colors.textBlack,
                              ),
                            ),
                          ),
                        );
                      },
                    ),
                  ],
                ),
                8.verticalSpace,
                if (_selectedCategory?.children != null &&
                    _selectedCategory!.children!.isNotEmpty)
                  _buildCategoryChips(colors),
                BlocConsumer<ShopBloc, ShopState>(
                  listenWhen: (previous, current) =>
                      previous.nearShops != current.nearShops,
                  listener: (BuildContext context, ShopState state) async {
                    if (!mounted) return;

                    final markers = await _generateMarkers(state.nearShops);

                    if (context.mounted) {
                      context.read<MapBloc>().add(
                        MapEvent.setMarker(markers: markers),
                      );
                    }
                  },
                  buildWhen: (previous, current) =>
                      previous.nearShops != current.nearShops ||
                      previous.isLoading != current.isLoading,
                  builder: (context, state) {
                    if (state.isLoading && state.nearShops.isEmpty) {
                      return Center(
                        child: Padding(
                          padding: EdgeInsets.all(32.r),
                          child: CircularProgressIndicator(
                            color: colors.primary,
                          ),
                        ),
                      );
                    }

                    if (state.nearShops.isEmpty && !state.isLoading) {
                      return _buildEmptyState(colors);
                    }

                    return ListView.separated(
                      physics: const NeverScrollableScrollPhysics(),
                      padding: EdgeInsets.only(bottom: 90.r),
                      shrinkWrap: true,
                      itemCount: state.nearShops.length,
                      separatorBuilder: (_, _) => 8.verticalSpace,
                      itemBuilder: (context, index) {
                        return ShopItem(
                          shop: state.nearShops[index],
                          colors: colors,
                        );
                      },
                    );
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildCategoryChips(CustomColorSet colors) {
    if (_selectedCategory?.children == null ||
        _selectedCategory!.children!.isEmpty) {
      return const SizedBox.shrink();
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          height: 48.h,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            padding: EdgeInsets.symmetric(vertical: 4.h),
            itemCount: _selectedCategory!.children!.length + 1,
            separatorBuilder: (_, _) => 8.horizontalSpace,
            itemBuilder: (context, index) {
              if (index == 0) {
                return _buildCategoryChip(
                  label: AppHelpers.getTranslation(TrKeys.all),
                  isSelected: _selectedSubCategoryId == null,
                  onTap: () => _onCategorySelected(null),
                  colors: colors,
                );
              }
              final child = _selectedCategory!.children![index - 1];
              final isSelected = _selectedSubCategoryId == child.id;

              return _buildCategoryChip(
                label: child.translation?.title ?? '',
                isSelected: isSelected,
                onTap: () => _onCategorySelected(child.id),
                colors: colors,
              );
            },
          ),
        ),
        16.verticalSpace,
      ],
    );
  }

  Widget _buildCategoryChip({
    required String label,
    required bool isSelected,
    required VoidCallback onTap,
    required CustomColorSet colors,
  }) {
    return ButtonEffectAnimation(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: EdgeInsets.symmetric(horizontal: 16.w, vertical: 10.h),
        decoration: BoxDecoration(
          color: isSelected ? colors.primary : colors.newBoxColor,
          borderRadius: BorderRadius.circular(20.r),
          border: Border.all(
            color: isSelected
                ? colors.primary
                : colors.icon.withValues(alpha: 0.2),
            width: 1,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: colors.primary.withValues(alpha: 0.2),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (isSelected) ...[
              Icon(Icons.check_circle, size: 16.r, color: colors.white),
              6.horizontalSpace,
            ],
            Text(
              label,
              style: CustomStyle.interSemi(
                color: isSelected ? colors.white : colors.textBlack,
                size: 13,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }

  Future<Set<Marker>> _generateMarkers(List<dynamic> shops) async {
    final Set<Marker> markers = {};

    for (var shop in shops) {
      try {
        final marker = Marker(
          onTap: () {},
          infoWindow: InfoWindow(
            title: shop.translation?.title,
            snippet: shop.translation?.description,
          ),
          markerId: MarkerId(shop.id.toString()),
          position: LatLng(
            shop.location?.latitude ?? AppHelpers.getInitialLatitude(),
            shop.location?.longitude ?? AppHelpers.getInitialLongitude(),
          ),
          icon: await image.getMarkerBitmap(
            90,
            text: shop.ratingAvg?.toStringAsFixed(1),
          ),
        );
        markers.add(marker);
      } catch (e) {
        debugPrint('Error creating marker: $e');
      }
    }

    return markers;
  }

  Widget _buildEmptyState(CustomColorSet colors) {
    return Center(
      child: Padding(
        padding: EdgeInsets.all(32.r),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              Icons.store_outlined,
              size: 64.r,
              color: colors.icon,
            ),
            16.verticalSpace,
            Text(
              AppHelpers.getTranslation(TrKeys.noShopsFound),
              style: CustomStyle.interSemi(
                color: colors.textBlack,
                size: 16,
              ),
              textAlign: TextAlign.center,
            ),
            8.verticalSpace,
            Text(
              AppHelpers.getTranslation(TrKeys.tryDifferentLocation),
              style: CustomStyle.interRegular(
                color: colors.textHint,
                size: 14,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  List<int>? _getCategoryIds() {
    return _selectedSubCategoryId != null
        ? [_selectedSubCategoryId!]
        : widget.categoryIds;
  }
}
