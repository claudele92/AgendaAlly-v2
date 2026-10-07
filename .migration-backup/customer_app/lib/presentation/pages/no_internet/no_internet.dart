import 'package:demand/presentation/route/app_route.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';

class NoInternetPage extends StatefulWidget {
  const NoInternetPage({super.key});

  @override
  State<NoInternetPage> createState() => _NoInternetPageState();
}

class _NoInternetPageState extends State<NoInternetPage>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );

    _fadeAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeIn));

    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, 0.3),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeOut));

    _controller.forward();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDarkMode = Theme.of(context).brightness == Brightness.dark;
    final backgroundColor = isDarkMode ? CustomStyle.black : CustomStyle.white;
    final textColor = isDarkMode ? CustomStyle.white : CustomStyle.black;
    final secondaryTextColor = isDarkMode
        ? CustomStyle.white.withValues(alpha: 0.7)
        : CustomStyle.textHint;

    return Scaffold(
      backgroundColor: backgroundColor,
      body: SafeArea(
        child: FadeTransition(
          opacity: _fadeAnimation,
          child: SlideTransition(
            position: _slideAnimation,
            child: Padding(
              padding: EdgeInsets.symmetric(horizontal: 32.r),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Spacer(flex: 2),

                  // Animated WiFi Icon
                  TweenAnimationBuilder<double>(
                    tween: Tween(begin: 0.0, end: 1.0),
                    duration: const Duration(milliseconds: 1200),
                    curve: Curves.elasticOut,
                    builder: (context, value, child) {
                      return Transform.scale(
                        scale: value,
                        child: Container(
                          width: 120.r,
                          height: 120.r,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: CustomStyle.primary.withValues(alpha: 0.1),
                          ),
                          child: Center(
                            child: Icon(
                              Icons.wifi_off_rounded,
                              size: 60.r,
                              color: CustomStyle.primary,
                            ),
                          ),
                        ),
                      );
                    },
                  ),

                  32.verticalSpace,

                  // Title
                  Text(
                    AppHelpers.getTranslation(TrKeys.noInternet),
                    style: CustomStyle.interSemi(color: textColor, size: 24),
                    textAlign: TextAlign.center,
                  ),

                  16.verticalSpace,

                  // Description
                  Text(
                    AppHelpers.getTranslation(TrKeys.checkYourConnection),
                    style: CustomStyle.interRegular(
                      color: secondaryTextColor,
                      size: 14,
                    ),
                    textAlign: TextAlign.center,
                  ),

                  40.verticalSpace,

                  // Connection Tips
                  _buildConnectionTip(
                    icon: Icons.wifi_rounded,
                    text: AppHelpers.getTranslation(TrKeys.turnOnWifi),
                    isDarkMode: isDarkMode,
                  ),
                  12.verticalSpace,
                  _buildConnectionTip(
                    icon: Icons.signal_cellular_alt_rounded,
                    text: AppHelpers.getTranslation(TrKeys.turnOnMobileData),
                    isDarkMode: isDarkMode,
                  ),
                  12.verticalSpace,
                  _buildConnectionTip(
                    icon: Icons.flight_takeoff_rounded,
                    text: AppHelpers.getTranslation(TrKeys.turnOffAirplaneMode),
                    isDarkMode: isDarkMode,
                  ),

                  const Spacer(flex: 2),

                  // Retry Button
                  CustomButton(
                    title: AppHelpers.getTranslation(TrKeys.retry),
                    onTap: () {
                      AppRoute.goMain(context);
                    },
                    bgColor: CustomStyle.primary,
                    titleColor: CustomStyle.white,
                  ),

                  24.verticalSpace,
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildConnectionTip({
    required IconData icon,
    required String text,
    required bool isDarkMode,
  }) {
    final backgroundColor = isDarkMode
        ? CustomStyle.white.withValues(alpha: 0.05)
        : CustomStyle.black.withValues(alpha: 0.03);
    final textColor = isDarkMode
        ? CustomStyle.white.withValues(alpha: 0.8)
        : CustomStyle.textHint;

    return Container(
      padding: EdgeInsets.symmetric(horizontal: 16.r, vertical: 12.r),
      decoration: BoxDecoration(
        color: backgroundColor,
        borderRadius: BorderRadius.circular(12.r),
        border: Border.all(
          color: isDarkMode
              ? CustomStyle.white.withValues(alpha: 0.1)
              : CustomStyle.black.withValues(alpha: 0.05),
          width: 1,
        ),
      ),
      child: Row(
        children: [
          Icon(
            icon,
            size: 20.r,
            color: CustomStyle.primary.withValues(alpha: 0.7),
          ),
          12.horizontalSpace,
          Expanded(
            child: Text(
              text,
              style: CustomStyle.interRegular(color: textColor, size: 13),
            ),
          ),
        ],
      ),
    );
  }
}
