import 'package:cached_network_image/cached_network_image.dart';
import 'package:demand/app_constants.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';

class CustomImage extends StatelessWidget {
  final double? width;
  final double? height;
  final double? shimmerWidth;
  final double? shimmerHeight;
  final String? imageUrl;
  final String? name;
  final EdgeInsetsGeometry? margin;
  final BorderRadius borderRadius;
  final GestureTapCallback? onTap;
  final BoxFit? fit;
  final Color? color;
  final List<BoxShadow>? boxShadow;
  final bool enabledZoom;
  final String heroTag;
  final Widget Function(String url, dynamic error)? errorWidget;

  const CustomImage({
    super.key,
    this.width,
    this.height,
    this.shimmerWidth,
    this.shimmerHeight,
    required this.imageUrl,
    this.name,
    this.margin,
    this.borderRadius = BorderRadius.zero,
    this.onTap,
    this.fit,
    this.color,
    this.boxShadow,
    this.enabledZoom = false,
    this.heroTag = "",
    this.errorWidget,
  });

  @override
  Widget build(BuildContext context) {
    return _ImageHero(
      heroTag: heroTag,
      child: GestureDetector(
        onTap: onTap,
        behavior: HitTestBehavior.opaque,
        child: InteractiveViewer(
          scaleEnabled: enabledZoom,
          child: Container(
            width: width,
            height: height,
            margin: margin,
            decoration: BoxDecoration(
              borderRadius: borderRadius,
              boxShadow: boxShadow,
            ),
            child: ClipRRect(
              borderRadius: borderRadius,
              child: Builder(
                builder: (context) {
                  if ((imageUrl ?? "").isEmpty) {
                    if (errorWidget != null) return errorWidget!("", "");
                    if ((name ?? "").isNotEmpty) {
                      return Container(
                        width: width,
                        height: height,
                        margin: margin,
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          color: CustomStyle.primary,
                          borderRadius: borderRadius,
                        ),
                        child: Text(
                          name![0],
                          style: const TextStyle(
                            color: CustomStyle.white,
                            fontSize: 18,
                          ),
                        ),
                      );
                    }
                    return Container(
                      width: width,
                      height: height,
                      margin: margin,
                      decoration: BoxDecoration(
                        color: Colors.grey.withValues(alpha: 0.2),
                        borderRadius: borderRadius,
                      ),
                    );
                  }
                  if (imageUrl!.contains("http")) {
                    return CachedNetworkImage(
                      imageUrl: imageUrl!,
                      width: width,
                      height: height,
                      fit: fit ?? BoxFit.contain,
                      color: color,
                      maxHeightDiskCache: AppConstants.cacheSize,
                      maxWidthDiskCache: AppConstants.cacheSize,
                      memCacheHeight: AppConstants.cacheSize,
                      memCacheWidth: AppConstants.cacheSize,
                      progressIndicatorBuilder: (context, url, progress) {
                        return Shimmer.fromColors(
                          baseColor: Colors.grey.withValues(alpha: 0.8),
                          highlightColor: Colors.grey.withValues(alpha: 0.2),
                          child: Container(
                            width: shimmerWidth ?? width,
                            height: shimmerHeight ?? height,
                            decoration: BoxDecoration(
                              color: Colors.grey.withValues(alpha: 0.2),
                              borderRadius: borderRadius,
                            ),
                          ),
                        );
                      },
                      errorWidget: (context, url, error) {
                        if (errorWidget != null) {
                          return errorWidget!(url, error);
                        }
                        return Container(
                          width: width,
                          height: height,
                          margin: margin,
                          decoration: BoxDecoration(
                            color: Colors.grey.withValues(alpha: 0.2),
                            borderRadius: borderRadius,
                          ),
                        );
                      },
                    );
                  }
                  return Container(
                    width: width,
                    height: height,
                    margin: margin,
                    decoration: BoxDecoration(
                      color: color,
                      borderRadius: borderRadius,
                      image: DecorationImage(
                        image: AssetImage(imageUrl!),
                        fit: fit ?? BoxFit.fill,
                      ),
                    ),
                  );
                },
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _ImageHero extends StatelessWidget {
  final String heroTag;
  final Widget child;

  const _ImageHero({this.heroTag = "", required this.child});

  @override
  Widget build(BuildContext context) {
    if (heroTag.isEmpty) return child;
    return Hero(tag: heroTag, child: child);
  }
}
