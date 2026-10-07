import 'package:demand/app_constants.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/route/app_route_setting.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/infrastructure/service/helper.dart';
import 'package:demand/infrastructure/service/tr_keys.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/app_assets.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class UISelectionPage extends StatefulWidget {
  final bool hasBackButton;

  const UISelectionPage({super.key, required this.hasBackButton});

  @override
  State<UISelectionPage> createState() => _UISelectionPageState();
}

class _UISelectionPageState extends State<UISelectionPage> {
  int _selectedUI = 0;
  late PageController _pageController;

  final List<String> _uiImages = [
    Assets.imagesUiOne,
    Assets.imagesUiTwo,
    Assets.imagesUiThree,
    Assets.imagesUiFour,
  ];

  @override
  void initState() {
    _selectedUI = LocalStorage.getUiType() ?? 0;
    _pageController = PageController(
      viewportFraction: 0.72,
      initialPage: LocalStorage.getUiType() ?? 0,
    );
    super.initState();
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  void _continue() {
    LocalStorage.setUiType(_selectedUI);
    if (widget.hasBackButton) {
      AppRoute.goMain(context);
      return;
    }
    AppRouteSetting.goLocationAccess(context);
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => SafeArea(
        child: Column(
          children: [
            Padding(
              padding: REdgeInsets.symmetric(horizontal: 24),
              child: Column(
                children: [
                  16.verticalSpace,
                  Text(
                    AppHelpers.getTranslation(TrKeys.selectInterfaceType),
                    style: CustomStyle.interNoSemi(
                      size: 24,
                      color: colors.textBlack,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  6.verticalSpace,
                  Text(
                    AppHelpers.getTranslation(TrKeys.selectYourFavoriteDesign),
                    style: CustomStyle.interRegular(
                      size: 15,
                      color: colors.textHint,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
            Expanded(
              child: PageView.builder(
                controller: _pageController,
                onPageChanged: (index) {
                  setState(() {
                    _selectedUI = index;
                  });
                },
                itemCount: _uiImages.length,
                itemBuilder: (context, index) {
                  return AnimatedBuilder(
                    animation: _pageController,
                    builder: (context, child) {
                      double value = 1.0;
                      if (_pageController.position.haveDimensions) {
                        value = _pageController.page! - index;
                        value = (1 - (value.abs() * 0.3)).clamp(0.7, 1.0);
                      }
                      return Center(
                        child: SizedBox(
                          height: Curves.easeInOut.transform(value) * 620.h,
                          child: child,
                        ),
                      );
                    },
                    child: _buildScreenshotCard(index, colors),
                  );
                },
              ),
            ),
            12.verticalSpace,
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(
                _uiImages.length,
                (index) => AnimatedContainer(
                  duration: const Duration(milliseconds: 300),
                  margin: EdgeInsets.symmetric(horizontal: 4.w),
                  width: _selectedUI == index ? 32.w : 8.w,
                  height: 8.h,
                  decoration: BoxDecoration(
                    color: _selectedUI == index
                        ? colors.primary
                        : colors.primary.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(AppConstants.radius.r),
                  ),
                ),
              ),
            ),
            12.verticalSpace,
            Padding(
              padding: REdgeInsets.symmetric(horizontal: 24),
              child: Column(
                children: [
                  Row(
                    spacing: 12.r,
                    children: [
                      if (widget.hasBackButton) PopButton(colors: colors),
                      Expanded(
                        child: CustomButton(
                          title: AppHelpers.getTranslation(TrKeys.save),
                          bgColor: colors.primary,
                          titleColor: colors.textWhite,
                          onTap: _continue,
                        ),
                      ),
                    ],
                  ),
                  SizedBox(height: 12.h),
                  Text(
                    AppHelpers.getTranslation(
                      TrKeys.youCanChangeItLaterInSettings,
                    ),
                    style: CustomStyle.interRegular(
                      size: 13,
                      color: colors.textHint,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildScreenshotCard(int index, CustomColorSet colors) {
    final isSelected = _selectedUI == index;

    return GestureDetector(
      onTap: () {
        _pageController.animateToPage(
          index,
          duration: const Duration(milliseconds: 400),
          curve: Curves.easeInOutCubic,
        );
      },
      child: Container(
        margin: REdgeInsets.symmetric(horizontal: 12, vertical: 18),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(24.r),
          boxShadow: [
            BoxShadow(
              color: isSelected
                  ? colors.primary.withValues(alpha: 0.3)
                  : colors.textBlack.withValues(alpha: 0.1),
              blurRadius: isSelected ? 30 : 20,
              offset: Offset(0, isSelected ? 15 : 10),
              spreadRadius: isSelected ? 2 : 0,
            ),
          ],
        ),
        child: Stack(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(AppConstants.radiusMax.r),
              child: Image.asset(
                _uiImages[index],
                fit: BoxFit.cover,
                width: double.infinity,
                height: double.infinity,
                alignment: Alignment.topCenter,
              ),
            ),
            if (isSelected)
              Positioned.fill(
                child: Container(
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(
                      AppConstants.radiusMax.r,
                    ),
                    border: Border.all(color: colors.primary, width: 3),
                  ),
                ),
              ),
            if (isSelected)
              Positioned(
                top: 16.r,
                right: 16.r,
                child: Container(
                  width: 36.r,
                  height: 36.r,
                  decoration: BoxDecoration(
                    color: colors.primary,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: colors.primary.withValues(alpha: 0.4),
                        blurRadius: 12,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Icon(
                    Icons.check_rounded,
                    color: CustomStyle.white,
                    size: 21.r,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
