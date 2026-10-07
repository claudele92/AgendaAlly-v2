import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/product_detail/product_detail_bloc.dart';
import 'package:demand/domain/model/model/review_data.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';

class ImagesList extends StatelessWidget {
  final List<Galleries> list;
  final int selectImageId;
  final ScrollController controller;

  const ImagesList({
    super.key,
    required this.list,
    required this.selectImageId,
    required this.controller,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.symmetric(horizontal: 16.r),
      child: BlurWrap(
        radius: BorderRadius.circular(100.r),
        child: Container(
          padding: EdgeInsets.all(4.r),
          decoration: BoxDecoration(
            color: CustomStyle.black.withValues(alpha: 0.2),
            borderRadius: BorderRadius.circular(100.r),
          ),
          child: SingleChildScrollView(
            controller: controller,
            scrollDirection: Axis.horizontal,
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: list
                  .map(
                    (e) => ButtonEffectAnimation(
                      onTap: () {
                        context.read<ProductDetailBloc>().add(
                          ProductDetailEvent.selectImage(
                            image: e,
                            nextImageTo: true,
                          ),
                        );
                      },
                      child: Container(
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: selectImageId == e.id
                                ? CustomStyle.textHint
                                : CustomStyle.transparent,
                          ),
                        ),
                        padding: selectImageId == e.id
                            ? EdgeInsets.all(4.r)
                            : EdgeInsets.zero,
                        child: Container(
                          margin: EdgeInsets.symmetric(horizontal: 2.r),
                          decoration: const BoxDecoration(
                            color: CustomStyle.white,
                            shape: BoxShape.circle,
                          ),
                          child: CustomNetworkImage(
                            enableButton: false,
                            url: e.path ?? "",
                            preview: e.preview,
                            height: 44,
                            width: 44,
                            radius: 100,
                          ),
                        ),
                      ),
                    ),
                  )
                  .toList(),
            ),
          ),
        ),
      ),
    );
  }
}
