import 'package:auto_size_text/auto_size_text.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/address/address_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

class CityPage extends StatefulWidget {
  final int countryId;
  final bool pushAddress;

  const CityPage({
    super.key,
    required this.countryId,
    this.pushAddress = false,
  });

  @override
  State<CityPage> createState() => _CityPageState();
}

class _CityPageState extends State<CityPage> {
  final RefreshController controller = RefreshController();
  final Delayed delayed = Delayed(milliseconds: 700);
  final GlobalKey key = GlobalKey();

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => KeyboardDismisser(
        isLtr: LocalStorage.getLangLtr(),
        child: SafeArea(
          bottom: false,
          child: Column(
            key: key,
            children: [
              Text(
                AppHelpers.getTranslation(TrKeys.selectCity),
                style: CustomStyle.interNoSemi(
                  color: colors.textBlack,
                  size: 18,
                ),
              ),
              8.verticalSpace,
              Padding(
                padding: EdgeInsets.symmetric(horizontal: 16.r),
                child: CustomTextFormField(
                  hint: AppHelpers.getTranslation(TrKeys.search),
                  onChanged: (s) {
                    if (s.trim().isNotEmpty) {
                      delayed.run(() {
                        context.read<AddressBloc>().add(
                          AddressEvent.searchCity(
                            context,
                            search: s,
                            countyId: widget.countryId,
                          ),
                        );
                      });
                    }
                  },
                ),
              ),
              BlocBuilder<AddressBloc, AddressState>(
                builder: (context, state) {
                  return state.isCityLoading
                      ? Padding(
                          padding: EdgeInsets.only(top: 32.r),
                          child: const Loading(),
                        )
                      : Expanded(
                          child: SmartRefresher(
                            controller: controller,
                            enablePullDown: false,
                            enablePullUp: true,
                            onLoading: () {
                              context.read<AddressBloc>().add(
                                AddressEvent.fetchCity(
                                  context,
                                  controller: controller,
                                  countyId: widget.countryId,
                                ),
                              );
                            },
                            child: ListView.builder(
                              shrinkWrap: true,
                              padding: EdgeInsets.all(16.r),
                              itemCount: state.cities.length,
                              itemBuilder: (context, index) {
                                return _cityItem(colors, state, index);
                              },
                            ),
                          ),
                        );
                },
              ),
            ],
          ),
        ),
      ),
      floatingButtonLocation: FloatingActionButtonLocation.startFloat,
      floatingButton: (colors) => PopButton(colors: colors),
    );
  }

  Widget _cityItem(CustomColorSet colors, AddressState state, int index) {
    return ButtonEffectAnimation(
      onTap: () {
        if (widget.pushAddress) {
          Navigator.pop(context, state.cities[index]);
          return;
        }
        LocalStorage.setAddress(
          LocalStorage.getAddress()?.copyWith(
            cityId: state.cities[index].id,
            city: state.cities[index].translation?.title,
          ),
        );
        AppRoute.goMain(context);
      },
      child: Column(
        children: [
          Row(
            children: [
              AutoSizeText(
                state.cities[index].translation?.title ?? "",
                style: CustomStyle.interNormal(
                  color:
                      LocalStorage.getAddress()?.countryId ==
                          state.cities[index].id
                      ? colors.primary
                      : colors.textBlack,
                  size: 12,
                ),
                minFontSize: 6,
                maxFontSize: 14,
                maxLines: 2,
                textAlign: TextAlign.center,
                overflow: TextOverflow.ellipsis,
              ),
              const Spacer(),
              if (LocalStorage.getAddress()?.cityId == state.cities[index].id)
                Icon(FlutterRemix.check_line, color: colors.primary),
            ],
          ),
          8.verticalSpace,
          const Divider(),
          8.verticalSpace,
        ],
      ),
    );
  }
}
