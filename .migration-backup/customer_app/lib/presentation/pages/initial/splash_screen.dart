import 'dart:io';

import 'package:demand/presentation/route/app_route_setting.dart';
import 'package:device_info_plus/device_info_plus.dart';
import 'package:flutter/material.dart';
import 'package:flutter_native_splash/flutter_native_splash.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/app_assets.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/infrastructure/session/session_coordinator.dart';

class SplashPage extends StatefulWidget {
  const SplashPage({super.key});

  @override
  State<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends State<SplashPage> {
  bool _validationFailed = false;
  @override
  void initState() {
    getDeviceInfo();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _navigateToNextScreen();
    });
    super.initState();
  }

  Future<void> _navigateToNextScreen() async {
    FlutterNativeSplash.remove();
    if (LocalStorage.getToken().isNotEmpty) {
      final generation = SessionCoordinator.instance.generation;
      final profile = await userRepository.getProfileDetails(context);
      if (!mounted || !SessionCoordinator.instance.current(generation)) return;
      if (profile.isRight()) {
        // Offline/forbidden is not confirmed revocation. Do not show private
        // cached pages until validation succeeds, and do not turn 403 into logout.
        setState(() => _validationFailed = true);
        return;
      }
    }
    if (LocalStorage.getUiType() == null) {
      AppRouteSetting.goSelectUIType(context, hasBackButton: true);
      return;
    }

    if (LocalStorage.getLocation() == null) {
      AppRouteSetting.goLocationAccess(context);

      return;
    }
    AppRoute.goMain(context);
  }

  Future<void> getDeviceInfo() async {
    DeviceInfoPlugin deviceInfo = DeviceInfoPlugin();
    if (Platform.isAndroid) {
      AndroidDeviceInfo androidInfo = await deviceInfo.androidInfo;
      debugPrint('==> ${androidInfo.model}');
    } else if (Platform.isIOS) {
      IosDeviceInfo iosInfo = await deviceInfo.iosInfo;
      debugPrint('==> ${iosInfo.modelName}');
      debugPrint('==> ${iosInfo.identifierForVendor}');
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_validationFailed) {
      return Scaffold(
        body: SafeArea(
          child: Center(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text('Unable to validate your session. Please retry.'),
                TextButton(
                  onPressed: () {
                    setState(() => _validationFailed = false);
                    _navigateToNextScreen();
                  },
                  child: const Text('Retry'),
                ),
              ],
            ),
          ),
        ),
      );
    }
    return Image.asset(Assets.imagesSplash, fit: BoxFit.cover);
  }
}
