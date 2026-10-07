import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:geolocator/geolocator.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/infrastructure/service/helper.dart';
import 'package:demand/infrastructure/service/tr_keys.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/domain/model/model/location_model.dart';
import 'package:demand/presentation/components/components.dart';

class LocationPermissionPage extends StatefulWidget {
  const LocationPermissionPage({super.key});

  @override
  State<LocationPermissionPage> createState() => _LocationPermissionPageState();
}

class _LocationPermissionPageState extends State<LocationPermissionPage> {
  bool _isLoading = false;

  Future<void> _requestLocationPermission() async {
    setState(() {
      _isLoading = true;
    });

    try {
      // Location xizmatini tekshirish
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        _showLocationServiceDialog();
        setState(() {
          _isLoading = false;
        });
        return;
      }

      // Permission tekshirish
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          _showPermissionDeniedDialog();
          setState(() {
            _isLoading = false;
          });
          return;
        }
      }

      if (permission == LocationPermission.deniedForever) {
        _showPermissionDeniedForeverDialog();
        setState(() {
          _isLoading = false;
        });
        return;
      }

      // Location olish
      Position position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
        ),
      );

      // LocationModel yaratish
      final locationModel = LocationModel(
        latitude: position.latitude,
        longitude: position.longitude,
      );

      // Local storage'ga saqlash
      await LocalStorage.setLocation(locationModel);

      // Home page'ga o'tish
      if (mounted) {
        AppRoute.goMain(context);
      }
    } catch (e) {
      debugPrint('Location error: $e');
      _showErrorDialog();
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _showLocationServiceDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(AppHelpers.getTranslation(TrKeys.locationServiceDisabled)),
        content: Text(
          AppHelpers.getTranslation(TrKeys.pleaseEnableLocationService),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              _skipLocation();
            },
            child: Text(AppHelpers.getTranslation(TrKeys.skipLocation)),
          ),
          TextButton(
            onPressed: () async {
              Navigator.pop(context);
              await Geolocator.openLocationSettings();
            },
            child: Text(AppHelpers.getTranslation(TrKeys.settings)),
          ),
        ],
      ),
    );
  }

  void _showPermissionDeniedDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(AppHelpers.getTranslation(TrKeys.permissionDenied)),
        content: Text(
          AppHelpers.getTranslation(TrKeys.locationPermissionDenied),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              _skipLocation();
            },
            child: Text(AppHelpers.getTranslation(TrKeys.skipLocation)),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              _requestLocationPermission();
            },
            child: Text(AppHelpers.getTranslation(TrKeys.tryAgainBtn)),
          ),
        ],
      ),
    );
  }

  void _showPermissionDeniedForeverDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(AppHelpers.getTranslation(TrKeys.permissionDeniedForever)),
        content: Text(
          AppHelpers.getTranslation(TrKeys.locationPermissionDeniedForever),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              _skipLocation();
            },
            child: Text(AppHelpers.getTranslation(TrKeys.skipLocation)),
          ),
          TextButton(
            onPressed: () async {
              Navigator.pop(context);
              await Geolocator.openAppSettings();
            },
            child: Text(AppHelpers.getTranslation(TrKeys.settings)),
          ),
        ],
      ),
    );
  }

  void _showErrorDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(AppHelpers.getTranslation(TrKeys.errorOccurred)),
        content: Text(AppHelpers.getTranslation(TrKeys.errorGettingLocation)),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              _skipLocation();
            },
            child: Text(AppHelpers.getTranslation(TrKeys.skipLocation)),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              _requestLocationPermission();
            },
            child: Text(AppHelpers.getTranslation(TrKeys.tryAgainBtn)),
          ),
        ],
      ),
    );
  }

  void _skipLocation() {
    // Default location (Toshkent markaziy) ni saqlash
    final defaultLocation = LocationModel(
      latitude: 41.2995,
      longitude: 69.2401,
    );
    LocalStorage.setLocation(defaultLocation);
    AppRoute.goMain(context);
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      bgColor: Colors.transparent,
      body: (colors) => Container(
        width: double.infinity,
        padding: EdgeInsets.symmetric(horizontal: 24.w),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [colors.primary, colors.primary.withValues(alpha: 0.7)],
          ),
        ),
        child: SafeArea(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Location icon
              Container(
                width: 120.r,
                height: 120.r,
                decoration: BoxDecoration(
                  color: colors.backgroundColor,
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: colors.textBlack.withValues(alpha: 0.1),
                      blurRadius: 20,
                      offset: const Offset(0, 10),
                    ),
                  ],
                ),
                child: Icon(
                  Icons.location_on,
                  size: 60.r,
                  color: colors.primary,
                ),
              ),

              SizedBox(height: 40.h),

              // Title
              Text(
                AppHelpers.getTranslation(TrKeys.determineYourLocation),
                style: CustomStyle.interSemi(
                  size: 24,
                  color: CustomStyle.white,
                ),
                textAlign: TextAlign.center,
              ),

              SizedBox(height: 16.h),

              // Description
              Text(
                AppHelpers.getTranslation(
                  TrKeys.weNeedYourLocationToFindNearbyShops,
                ),
                style: CustomStyle.interRegular(
                  size: 16,
                  color: CustomStyle.white.withValues(alpha: 0.9),
                ),
                textAlign: TextAlign.center,
              ),

              SizedBox(height: 48.h),

              // Allow button
              SizedBox(
                width: double.infinity,
                height: 54.h,
                child: ElevatedButton(
                  onPressed: _isLoading ? null : _requestLocationPermission,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: colors.backgroundColor,
                    foregroundColor: colors.primary,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12.r),
                    ),
                    elevation: 0,
                  ),
                  child: _isLoading
                      ? SizedBox(
                          width: 24.r,
                          height: 24.r,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(
                              colors.primary,
                            ),
                          ),
                        )
                      : Text(
                          AppHelpers.getTranslation(TrKeys.determineLocation),
                          style: CustomStyle.interSemi(
                            size: 16,
                            color: colors.primary,
                          ),
                        ),
                ),
              ),

              SizedBox(height: 16.h),

              // Skip button
              TextButton(
                onPressed: _isLoading ? null : _skipLocation,
                child: Text(
                  AppHelpers.getTranslation(TrKeys.skipLocation),
                  style: CustomStyle.interNormal(
                    size: 16,
                    color: CustomStyle.white,
                  ),
                ),
              ),

              SizedBox(height: 24.h),

              // Info text
              Text(
                AppHelpers.getTranslation(
                  TrKeys.yourLocationIsUsedOnlyToShowNearbyShops,
                ),
                style: CustomStyle.interRegular(
                  size: 12,
                  color: CustomStyle.white.withValues(alpha: 0.7),
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
