import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:demand/application/checkout/checkout_bloc.dart';
import 'package:demand/application/parcel/parcel_bloc.dart';
import 'package:demand/application/parcels_list/parcels_list_bloc.dart';
import 'package:demand/domain/model/model/parcel_order_model.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/pages/parcel/parcel_list_page.dart';
import 'package:demand/presentation/pages/parcel/parcel_order_page.dart';
import 'package:demand/presentation/pages/parcel/parcel_page.dart';
import 'package:demand/presentation/pages/parcel/widgets/info_screen.dart';
import 'package:demand/presentation/style/theme/theme.dart';

abstract class AppRouteParcel {
  AppRouteParcel._();

  static Future goInfoScreen(
    BuildContext context, {
    required int index,
    bool replace = false,
    required CustomColorSet colors,
  }) {
    if (replace) {
      return Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (_) => InfoPage(index: index, colors: colors),
        ),
      );
    }
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => InfoPage(index: index, colors: colors),
      ),
    );
  }

  static Future goParcel(BuildContext context) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) =>
                  ParcelBloc()..add(ParcelEvent.fetchTypes(context)),
            ),
            BlocProvider(
              create: (_) =>
                  CheckoutBloc()..add(CheckoutEvent.fetchPayments(context)),
            ),
          ],
          child: const ParcelPage(),
        ),
      ),
    );
  }

  static void goParcelOrderPage(
    BuildContext context,
    ParcelOrder? parcel,
    CustomColorSet colors,
  ) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      paddingTop: MediaQuery.sizeOf(context).height / 3,
      modal: (c) => BlocProvider(
        create: (context) => ParcelBloc()
          ..add(
            ParcelEvent.showParcel(
              context,
              orderId: parcel?.id ?? 0,
              parcel: parcel,
            ),
          ),
        child: ParcelOrderScreen(colors: colors, controller: c),
      ),
    );
  }

  static Future goParcelList(BuildContext context) {
    Navigator.popUntil(context, (route) {
      return route.isFirst;
    });
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => BlocProvider(
          create: (context) => ParcelsListBloc()
            ..add(ParcelsListEvent.fetchActiveParcel(context, isRefresh: true))
            ..add(
              ParcelsListEvent.fetchHistoryParcel(context, isRefresh: true),
            ),
          child: const ParcelListPage(),
        ),
      ),
    );
  }
}
