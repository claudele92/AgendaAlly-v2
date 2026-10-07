import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../infrastructure/service/services.dart';
import '../style/style.dart';

class AuthTabBar extends StatefulWidget {
  final bool isScrollable;
  final TabController tabController;
  final List<AuthTab> tabs;

  const AuthTabBar({
    super.key,
    required this.tabController,
    required this.tabs,
    this.isScrollable = false,
  });

  @override
  State<AuthTabBar> createState() => _AuthTabBarState();
}

class _AuthTabBarState extends State<AuthTabBar> {
  @override
  void initState() {
    super.initState();
    widget.tabController.addListener(() {
      if (mounted) setState(() {});
    });
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(6.r),
      height: 50.h,
      decoration: BoxDecoration(
        color: CustomStyle.whiteWithOpacity,
        borderRadius: BorderRadius.circular(10.r),
        boxShadow: [
          BoxShadow(
            color: CustomStyle.black.withValues(alpha: 0.05),
            blurRadius: 2,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      child: TabBar(
        isScrollable: widget.isScrollable,
        controller: widget.tabController,
        indicator: BoxDecoration(
          borderRadius: BorderRadius.circular(8.r),
          color: CustomStyle.black,
        ),
        labelColor: CustomStyle.white,
        unselectedLabelColor: CustomStyle.white.withValues(alpha: 0.5),
        unselectedLabelStyle: CustomStyle.interNormal(
          size: 12.sp,
          color: CustomStyle.black.withValues(alpha: 0.5),
        ),
        labelStyle: CustomStyle.interSemi(
          size: 12.sp,
          color: CustomStyle.white,
        ),
        tabs: widget.tabs
            .map(
              (tab) => Tab(
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      tab.icon,
                      size: 18.r,
                      color:
                          widget.tabController.index == widget.tabs.indexOf(tab)
                          ? CustomStyle.white
                          : CustomStyle.white.withValues(alpha: 0.5),
                    ),
                    8.horizontalSpace,
                    Text(AppHelpers.getTranslation(tab.text)),
                  ],
                ),
              ),
            )
            .toList(),
      ),
    );
  }
}

class AuthTab {
  final String text;
  final IconData icon;

  const AuthTab({required this.text, required this.icon});
}
