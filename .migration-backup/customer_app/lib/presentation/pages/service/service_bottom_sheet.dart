import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../../application/service/service_bloc.dart';
import '../../../domain/model/models.dart';
import '../../../infrastructure/local_storage/local_storage.dart';
import '../../../infrastructure/service/services.dart';
import '../../components/components.dart';
import '../../route/app_route_service.dart';
import '../../style/style.dart';

class ServiceBottomSheet extends StatefulWidget {
  final ServiceModel service;
  final List<ServiceExtra>? extras;
  final ScrollController controller;
  final bool bookButton;
  final int? shopId;

  const ServiceBottomSheet({
    super.key,
    required this.extras,
    required this.controller,
    required this.service,
    required this.bookButton,
    required this.shopId,
  });

  @override
  State<ServiceBottomSheet> createState() => _ServiceBottomSheetState();
}

class _ServiceBottomSheetState extends State<ServiceBottomSheet> {
  @override
  void initState() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ServiceBloc>().add(
        ServiceEvent.selectAllExtras(extra: widget.extras),
      );
    });
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return KeyboardDismisser(
      isLtr: LocalStorage.getLangLtr(),
      child: ModalWrap(
        body: (colors) => Stack(
          children: [
            ListView(
              controller: widget.controller,
              padding: EdgeInsets.only(
                top: 8.r,
                bottom: 16.r,
                left: 16,
                right: 16,
              ),
              children: [
                ModalDrag(colors: colors),
                8.verticalSpace,
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        widget.service.translation?.title ?? '-',
                        style: CustomStyle.interNoSemi(
                          color: colors.textBlack,
                          size: 22,
                        ),
                      ),
                    ),
                    // const Spacer(),
                    Text(
                      AppHelpers.numberFormat(widget.service.totalPrice),
                      style: CustomStyle.interNoSemi(
                        color: colors.textBlack,
                        size: 18,
                      ),
                    ),
                  ],
                ),
                20.verticalSpace,
                Wrap(
                  children: [
                    Container(
                      margin: EdgeInsets.only(right: 8.r),
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(50.r),
                        border: Border.all(color: colors.textHint),
                      ),
                      padding: EdgeInsets.symmetric(
                        horizontal: 16.r,
                        vertical: 8.r,
                      ),
                      child: Text(
                        "${AppHelpers.getTranslation(TrKeys.from)} ${AppHelpers.numberFormat(widget.service.totalPrice)}",
                        style: CustomStyle.interNormal(
                          color: colors.textHint,
                          size: 12,
                        ),
                      ),
                    ),
                    Container(
                      margin: EdgeInsets.only(right: 8.r),
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(50.r),
                        border: Border.all(color: colors.textHint),
                      ),
                      padding: EdgeInsets.symmetric(
                        horizontal: 16.r,
                        vertical: 8.r,
                      ),
                      child: Text(
                        "${widget.service.interval ?? 0} ${AppHelpers.getTranslation(TrKeys.minute)}",
                        style: CustomStyle.interNormal(
                          color: colors.textHint,
                          size: 12,
                        ),
                      ),
                    ),
                  ],
                ),
                24.verticalSpace,
                CustomReadMoreText(
                  '${widget.service.translation?.description}',
                  trimLines: 4,
                  colors: colors,
                ),
                12.verticalSpace,
                BlocBuilder<ServiceBloc, ServiceState>(
                  buildWhen: (p, n) {
                    return p.selectExtras != n.selectExtras;
                  },
                  builder: (context, state) {
                    return ListView.builder(
                      padding: EdgeInsets.zero,
                      itemCount: widget.service.extras?.length ?? 0,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemBuilder: (context, index) {
                        return Padding(
                          padding: REdgeInsets.only(bottom: 8),
                          child: GestureDetector(
                            onTap: () {
                              if (state.selectServices.any(
                                (e) => e.id == widget.service.id,
                              )) {
                                context.read<ServiceBloc>().add(
                                  ServiceEvent.selectService(
                                    service: widget.service.copyWith(
                                      selectExtras: state.selectExtras,
                                    ),
                                    extra: widget.service.extras?[index],
                                  ),
                                );
                              } else {
                                context.read<ServiceBloc>().add(
                                  ServiceEvent.selectExtraIndex(
                                    extra: widget.service.extras?[index],
                                  ),
                                );
                              }
                            },
                            child: Container(
                              padding: REdgeInsets.symmetric(
                                horizontal: 16,
                                vertical: 12,
                              ),
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(10.r),
                                border: Border.all(
                                  color:
                                      state.selectExtras.any(
                                        (e) =>
                                            widget.service.extras?[index].id ==
                                            e.id,
                                      )
                                      ? colors.textBlack
                                      : colors.textHint,
                                  width:
                                      state.selectExtras.any(
                                        (e) =>
                                            widget.service.extras?[index].id ==
                                            e.id,
                                      )
                                      ? 1.2
                                      : 1,
                                ),
                              ),
                              child: Row(
                                children: [
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          '${widget.service.extras?[index].translation?.title}',
                                          style: CustomStyle.interRegular(
                                            color: colors.textBlack,
                                            size: 16,
                                          ),
                                        ),
                                        Text(
                                          AppHelpers.numberFormat(
                                            widget.service.extras?[index].price,
                                          ),
                                          style: CustomStyle.interRegular(
                                            color: colors.textBlack,
                                            size: 16,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  if (state.selectExtras.any(
                                    (e) =>
                                        widget.service.extras?[index].id ==
                                        e.id,
                                  ))
                                    Container(
                                      height: 18.r,
                                      width: 18.r,
                                      decoration: BoxDecoration(
                                        border: Border.all(
                                          color: colors.textBlack,
                                          width: 6.r,
                                        ),
                                        shape: BoxShape.circle,
                                      ),
                                    ),
                                ],
                              ),
                            ),
                          ),
                        );
                      },
                    );
                  },
                ),
                26.verticalSpace,
              ],
            ),
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              child: Column(
                children: [
                  BlocBuilder<ServiceBloc, ServiceState>(
                    builder: (context, state) {
                      num price = (widget.service.totalPrice ?? 0);
                      for (var e in state.selectExtras) {
                        price += e.price ?? 0;
                      }
                      return Container(
                        decoration: BoxDecoration(
                          color: colors.textWhite,
                          borderRadius: BorderRadius.vertical(
                            top: Radius.circular(16.r),
                          ),
                        ),
                        padding: REdgeInsets.only(
                          left: 16,
                          right: 16,
                          top: 12,
                          bottom: 28,
                        ),
                        child: Row(
                          children: [
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  AppHelpers.getTranslation(TrKeys.price),
                                  style: CustomStyle.interNormal(
                                    color: colors.textHint,
                                    size: 14,
                                  ),
                                ),
                                Text(
                                  AppHelpers.numberFormat(price),
                                  style: CustomStyle.interNoSemi(
                                    color: colors.textBlack,
                                    size: 26,
                                  ),
                                ),
                              ],
                            ),
                            24.horizontalSpace,
                            Expanded(
                              child: CustomButton(
                                title:
                                    state.selectServices.any(
                                      (e) => e.id == widget.service.id,
                                    )
                                    ? AppHelpers.getTranslation(TrKeys.remove)
                                    : AppHelpers.getTranslation(TrKeys.bookNow),
                                bgColor: colors.textBlack,
                                titleColor: colors.textWhite,
                                onTap: () {
                                  if (!widget.bookButton) {
                                    AppRouteService.goServiceListPage(
                                      context,
                                      shopId: widget.shopId,
                                    );
                                    return;
                                  }
                                  context.read<ServiceBloc>().add(
                                    ServiceEvent.selectService(
                                      service: widget.service.copyWith(
                                        selectExtras: state.selectExtras,
                                      ),
                                    ),
                                  );
                                  Navigator.pop(context);
                                },
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
