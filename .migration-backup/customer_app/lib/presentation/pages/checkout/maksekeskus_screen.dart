import 'package:demand/app_constants.dart';
import 'package:demand/application/maksekeskus/maksekeskus_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

class MaksekeskusScreen extends StatelessWidget {
  final CustomColorSet colors;
  final ValueChanged<String> onSuccess;

  const MaksekeskusScreen({
    super.key,
    required this.colors,
    required this.onSuccess,
  });

  @override
  Widget build(BuildContext context) {
    return BlurWrap(
      radius: BorderRadius.only(
        topRight: Radius.circular(AppConstants.radiusMax.r),
        topLeft: Radius.circular(AppConstants.radiusMax.r),
      ),
      child: Container(
        width: double.infinity,
        decoration: BoxDecoration(
          color: colors.newBoxColor,
          borderRadius: BorderRadius.only(
            topRight: Radius.circular(AppConstants.radiusMax.r),
            topLeft: Radius.circular(AppConstants.radiusMax.r),
          ),
        ),
        padding: EdgeInsets.all(16.r),
        child: BlocBuilder<MaksekeskusBloc, MaksekeskusState>(
          builder: (context, state) {
            return Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  AppHelpers.getTranslation(TrKeys.paymentMethod),
                  style: CustomStyle.interSemi(
                    color: colors.textBlack,
                    size: 16,
                  ),
                ),
                16.verticalSpace,
                state.isLoading
                    ? const Loading()
                    : Wrap(
                        spacing: 6,
                        runSpacing: 8,
                        children: [
                          ...?state.maksekeskus?.methods?.map(
                            (e) => InkWell(
                              onTap: () {
                                context.read<MaksekeskusBloc>().add(
                                  MaksekeskusEvent.selectMethod(
                                    link: e.url ?? '',
                                  ),
                                );
                                onSuccess.call(e.url ?? '');
                              },
                              child: Container(
                                padding: REdgeInsets.all(1),
                                decoration: BoxDecoration(
                                  color: state.selectMethodLink == e.url
                                      ? CustomStyle.primary
                                      : CustomStyle.transparent,
                                ),
                                child: CustomNetworkImage(
                                  url: e.logoUrl ?? '',
                                  height: 38,
                                  width: 108,
                                  radius: 0,
                                ),
                              ),
                            ),
                          ),
                        ],
                      ),
                36.verticalSpace,
              ],
            );
          },
        ),
      ),
    );
  }
}
