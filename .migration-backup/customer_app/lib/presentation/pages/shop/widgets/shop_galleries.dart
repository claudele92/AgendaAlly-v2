import 'package:demand/app_constants.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:smooth_page_indicator/smooth_page_indicator.dart';
import 'package:demand/domain/model/models.dart';

import '../../../components/components.dart';

class ShopGalleries extends StatelessWidget {
  final double height;
  final double width;
  final String? img;
  final List<Galleries> galleries;
  final double radius;
  final bool countActive;
  final Function(List<Galleries> images, int index)? onTapImage;

  const ShopGalleries({
    super.key,
    required this.galleries,
    required this.height,
    required this.width,
    required this.img,
    this.radius = AppConstants.radiusMax,
    this.countActive = false,
    this.onTapImage,
  });

  @override
  Widget build(BuildContext context) {
    if (galleries.length < 2) {
      return CustomNetworkImage(
        url: galleries.isNotEmpty ? galleries.first.path : img,
        preview: galleries.isNotEmpty ? galleries.first.preview : null,
        height: height,
        width: width,
        borderRadius: BorderRadius.circular(radius.r),
      );
    } else {
      if (onTapImage != null) {
        return GestureDetector(
          onTap: () => onTapImage?.call(galleries, 0),
          child: _MultiImages(height, width, galleries, radius, countActive),
        );
      }

      return _MultiImages(height, width, galleries, radius, countActive);
    }
  }
}

class _MultiImages extends StatefulWidget {
  final double height;
  final double width;
  final double radius;
  final List<Galleries> galleries;
  final bool countActive;

  const _MultiImages(
    this.height,
    this.width,
    this.galleries,
    this.radius,
    this.countActive,
  );

  @override
  State<_MultiImages> createState() => _MultiImagesState();
}

class _MultiImagesState extends State<_MultiImages> {
  late PageController pageController;
  int currentIndex = 0;

  @override
  void initState() {
    pageController = PageController();
    super.initState();
  }

  @override
  void dispose() {
    pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        SizedBox(
          height: widget.height.r,
          width: widget.width.r,
          child: ClipRRect(
            borderRadius: BorderRadius.circular(widget.radius.r),
            child: PageView.builder(
              itemCount: widget.galleries.length,
              scrollDirection: Axis.horizontal,
              controller: pageController,
              onPageChanged: (index) {
                if (widget.countActive) {
                  currentIndex = index;
                  setState(() {});
                }
              },
              itemBuilder: (context, index) {
                return CustomNetworkImage(
                  url: widget.galleries[index].path,
                  preview: widget.galleries[index].preview,
                  height: widget.height,
                  width: double.infinity,
                  borderRadius: BorderRadius.zero,
                );
              },
            ),
          ),
        ),
        Positioned(
          bottom: 6.r,
          left: 0,
          right: 0,
          child: Container(
            height: 4.r,
            width: widget.width.r,
            alignment: Alignment.bottomCenter,
            child: SmoothPageIndicator(
              controller: pageController,
              count: widget.galleries.length,
              effect: ScrollingDotsEffect(
                dotWidth: 6.r,
                strokeWidth: 6.r,
                spacing: 6.r,
                activeDotScale: 1,
                radius: 6.r,
                dotHeight: 6.r,
                activeDotColor: CustomStyle.white,
                paintStyle: PaintingStyle.fill,
              ),
              onDotClicked: (index) {},
            ),
          ),
        ),
        if (widget.countActive)
          Positioned(
            right: 16.r,
            bottom: 16.r,
            child: Container(
              width: 40.r,
              height: 20.r,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: CustomStyle.black60,
                borderRadius: BorderRadius.circular(6.r),
              ),
              child: Text(
                '${currentIndex + 1}/${widget.galleries.length}',
                style: CustomStyle.interNoSemi(
                  color: CustomStyle.white,
                  size: 12,
                ),
              ),
            ),
          ),
      ],
    );
  }
}
