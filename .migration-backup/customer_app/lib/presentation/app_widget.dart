import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:demand/infrastructure/service/language_meneger.dart';
import 'package:flutter/material.dart';
import 'package:flutter_displaymode/flutter_displaymode.dart';
import 'package:flutter_isolate/flutter_isolate.dart';
import 'package:flutter_native_splash/flutter_native_splash.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/domain/di/dependency_manager.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/infrastructure/repository/settings_repository.dart';
import 'package:provider/provider.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';
import 'pages/initial/splash_screen.dart';
import 'style/style.dart';
import 'style/theme/theme.dart';
import 'session_boundary.dart';
import 'session_recovery_page.dart';
import '../infrastructure/session/session_coordinator.dart';
import 'pages/auth/auth_page.dart';
import '../application/auth/auth_bloc.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

@pragma('vm:entry-point')
Future<int> getOtherTranslation(int arg) async {
  final settingsRepository = SettingsRepository();
  final res = await settingsRepository.getLanguages(arg: true);
  res.fold((l) {
    l.data?.forEach((e) async {
      final translations = await settingsRepository.getMobileTranslations(
        lang: e.locale,
      );
      translations.fold((d) {
        LocalStorage.setOtherTranslations(
          translations: d.data,
          key: e.id.toString(),
        );
      }, (r) => null);
    });
  }, (r) => null);
  return 0;
}

class AppWidget extends StatefulWidget {
  const AppWidget({super.key});

  @override
  State<AppWidget> createState() => _AppWidgetState();
}

class _AppWidgetState extends State<AppWidget> {
  Future isolate() {
    return FlutterIsolate.spawn(getOtherTranslation, 0);
  }

  @override
  void initState() {
    if (LocalStorage.getTranslations().isNotEmpty) {
      fetchSettingNoAwait();
    }
    isolate();
    FlutterDisplayMode.setHighRefreshRate();
    super.initState();
  }

  Future fetchSetting() async {
    final connect = await Connectivity().checkConnectivity();
    if (connect.contains(ConnectivityResult.wifi) ||
        connect.contains(ConnectivityResult.mobile)) {
      settingsRepository.getGlobalSettings();
      final res = await settingsRepository.getLanguages();
      res.fold((l) {
        if (l.data?.isEmpty ?? true) {
          return;
        }
        LanguageManager.getDeviceLocale(l.data ?? []);
      }, (r) {});
      await settingsRepository.getMobileTranslations();
      if (LocalStorage.getSelectedCurrency() == null) {
        settingsRepository.getCurrencies();
      }
    }
  }

  Future fetchSettingNoAwait() async {
    settingsRepository.getGlobalSettings();
    settingsRepository.getLanguages();
    settingsRepository.getMobileTranslations();
    if (LocalStorage.getSelectedCurrency() == null) {
      settingsRepository.getCurrencies();
    }
  }

  @override
  Widget build(BuildContext context) {
    return FutureBuilder(
      future: Future.wait({
        AppTheme.create,
        if (LocalStorage.getTranslations().isEmpty) fetchSetting(),
      }),
      builder: (context, AsyncSnapshot<List<dynamic>> snapshot) {
        if (snapshot.hasData) {
          FlutterNativeSplash.remove();
          final AppTheme theme = snapshot.data?[0];
          return ScreenUtilInit(
            useInheritedMediaQuery: true,
            designSize: const Size(375, 812),
            builder: (context, child) {
              return RefreshConfiguration(
                footerBuilder: () => const ClassicFooter(
                  idleIcon: SizedBox.shrink(),
                  idleText: "",
                  noDataText: "",
                ),
                headerBuilder: () => WaterDropHeader(
                  waterDropColor: CustomStyle.primary,
                  idleIcon: Icon(
                    FlutterRemix.plane_fill,
                    size: 15,
                    color: CustomStyle.white,
                  ),
                ),
                child: ChangeNotifierProvider(
                  create: (BuildContext context) => theme,
                  child: SessionBoundary(
                    builder: (context, epoch) => MaterialApp(
                      theme: ThemeData(useMaterial3: false),
                      title: AppHelpers.getAppName(),
                      builder: (context, child) => ScrollConfiguration(
                        behavior: MyBehavior(),
                        child: child!,
                      ),
                      debugShowCheckedModeBanner: false,
                      home: SessionCoordinator.instance.cleanupFailed
                          ? const SessionRecoveryPage()
                          : SessionCoordinator.instance.logoutPending
                          ? const SessionLogoutPendingPage()
                          : SessionCoordinator.instance.identityPending
                          ? const SessionIdentityPendingPage()
                          : epoch == 0
                          ? SplashPage()
                          : BlocProvider(
                              create: (_) => AuthBloc(),
                              child: const AuthPage(),
                            ),
                    ),
                  ),
                ),
              );
            },
          );
        }
        return const SizedBox.shrink();
      },
    );
  }
}

class MyBehavior extends ScrollBehavior {
  @override
  Widget buildOverscrollIndicator(
    BuildContext context,
    Widget child,
    ScrollableDetails details,
  ) => child;
}
