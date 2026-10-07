import 'package:demand/application/service/direct/direct_service_bloc.dart';
import 'package:demand/presentation/pages/service/direct_service_bottom_sheet.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/application/cart/cart_bloc.dart';
import 'package:demand/application/checkout/checkout_bloc.dart';
import 'package:demand/application/form_option/form_bloc.dart';
import 'package:demand/application/gift_cart/gift_cart_bloc.dart';
import 'package:demand/application/main/main_bloc.dart';
import 'package:demand/application/master/master_bloc.dart';
import 'package:demand/application/membership/membership_bloc.dart';
import 'package:demand/application/review/review_bloc.dart';
import 'package:demand/application/service/service_bloc.dart';
import 'package:demand/application/shop/shop_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/pages/booking/add_note_page.dart';
import 'package:demand/presentation/pages/booking/address_list.dart';
import 'package:demand/presentation/pages/booking/booking_payment_page.dart';
import 'package:demand/presentation/pages/booking/booking_screen.dart';
import 'package:demand/presentation/pages/booking/confirm_page.dart';
import 'package:demand/presentation/pages/booking/failure_page.dart';
import 'package:demand/presentation/pages/booking/form_page.dart';
import 'package:demand/presentation/pages/booking/notes_page.dart';
import 'package:demand/presentation/pages/booking/select_book_time.dart';
import 'package:demand/presentation/pages/booking/select_master.dart';
import 'package:demand/presentation/pages/booking/select_time_modal.dart';
import 'package:demand/presentation/pages/booking/widget/add_review_booking.dart';
import 'package:demand/presentation/pages/booking/widget/select_gift_screen.dart';
import 'package:demand/presentation/pages/booking/widget/select_master_bottom.dart';
import 'package:demand/presentation/pages/master_page/master_page.dart';
import 'package:demand/presentation/pages/booking/widget/select_membership_screen.dart';
import 'package:demand/presentation/pages/service/service_list_page.dart';
import '../../domain/model/models.dart';
import '../pages/service/service_bottom_sheet.dart';
import '../style/theme/theme.dart';

abstract class AppRouteService {
  AppRouteService._();

  static Future goMasterPage(
    BuildContext context, {
    required MasterModel master,
  }) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(value: context.read<MainBloc>()),
            BlocProvider.value(value: context.read<CartBloc>()),
            BlocProvider(
              create: (context) => MasterBloc()
                ..add(MasterEvent.fetchMasterById(context, master: master))
                ..add(
                  MasterEvent.fetchMasterImage(context, id: master.id ?? 0),
                ),
            ),
            BlocProvider(
              create: (context) => ReviewBloc()
                ..add(
                  ReviewEvent.fetchReviewList(context, masterId: master.id),
                ),
            ),
          ],
          child: const MasterPage(),
        ),
      ),
    );
  }

  static void goSelectTomeBottomSheet(
    BuildContext context, {
    int? selectService,
    required String? title,
    required int? shopId,
    required CustomColorSet colors,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      initialChildSize: 0.7,
      maxChildSize: 0.99,
      paddingTop: 48,

      modal: (c) => MultiBlocProvider(
        providers: [
          // BlocProvider(create: (context) => BookingBloc()),
          BlocProvider.value(
            value: context.read<BookingBloc>()
              ..add(BookingEvent.checkTime(context, startTime: DateTime.now())),
          ),
        ],
        child: SelectTimeModal(
          selectService: selectService,
          colors: colors,
          controller: c,
        ),
      ),
    );
  }

  static Future goServiceListPage(
    BuildContext context, {
    MasterModel? master,
    ServiceModel? service,
    int? shopId,
    int? categoryId,
  }) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) => ServiceBloc()
                ..add(
                  ServiceEvent.fetchServiceCategories(
                    context,
                    isRefresh: true,
                    shopId: shopId,
                    categoryId: categoryId,
                    masterId: master?.id,
                  ),
                )
                ..add(ServiceEvent.selectService(service: service)),
            ),
            if (master != null)
              BlocProvider(create: (context) => BookingBloc()),
          ],
          child: ServiceListPage(shopId: shopId, master: master),
        ),
      ),
    );
  }

  static void goServiceBottomSheet(
    BuildContext context, {
    required ServiceModel service,
    required List<ServiceExtra>? extras,
    required bool bookButton,
    required int? shopId,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      initialChildSize: service.extras?.isNotEmpty ?? false ? 0.7 : 0.5,
      minChildSize: 0.5,

      modal: (c) => BlocProvider.value(
        value: context.read<ServiceBloc>(),
        child: ServiceBottomSheet(
          controller: c,
          service: service,
          extras: extras,
          bookButton: bookButton,
          shopId: shopId,
        ),
      ),
    );
  }

  static void goDirectlyServiceBottomSheet(
    BuildContext context, {
    required CustomColorSet colors,
    int? serviceId,
    int? shopId,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      modal: (c) => BlocProvider(
        create: (_) =>
            DirectServiceBloc()
              ..add(DirectServiceEvent.init(context, serviceId: serviceId)),
        child: DirectServiceBottomSheet(
          colors: colors,
          controller: c,
          shopId: shopId,
        ),
      ),
    );
  }

  static void goSelectMasterBottomSheet(
    BuildContext context, {
    int? serviceId,
    List<int>? serviceIds,
    required String title,
    required int? shopId,
    required CustomColorSet colors,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      initialChildSize: 0.7,

      modal: (c) => MultiBlocProvider(
        providers: [
          BlocProvider.value(
            value: context.read<MasterBloc>()
              ..add(
                MasterEvent.fetchMasters(
                  context,
                  shopId: shopId,
                  serviceIds: serviceIds,
                  serviceId: serviceId,
                  isRefresh: true,
                ),
              ),
          ),
          BlocProvider.value(value: context.read<BookingBloc>()),
        ],
        child: SelectMasterBottomSheet(
          colors: colors,
          controller: c,
          title: title,
          shopId: shopId,
          serviceId: serviceId,
        ),
      ),
    );
  }

  static void goSelectOptionsMaster(
    BuildContext context, {
    required List<ServiceModel> services,
    required String title,
    required int? shopId,
    required CustomColorSet colors,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      initialChildSize: 0.55,
      paddingTop: 55,

      modal: (c) => MultiBlocProvider(
        providers: [
          BlocProvider(create: (context) => BookingBloc()),
          BlocProvider(
            create: (context) => MasterBloc()
              ..add(
                MasterEvent.fetchMasters(
                  context,
                  shopId: shopId,
                  serviceIds: services.map((e) => e.id ?? 0).toList(),
                  isRefresh: true,
                ),
              ),
          ),
        ],
        child: SelectMasterBottomSheet(
          colors: colors,
          controller: c,
          title: title,
          services: services,
          shopId: shopId,
          selectAll: true,
        ),
      ),
    );
  }

  static Future goSelectMaster(BuildContext context, {required int? shopId}) {
    return Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider(create: (context) => MasterBloc()),
            BlocProvider.value(value: context.read<BookingBloc>()),
          ],
          child: SelectMasterPage(shopId: shopId),
        ),
      ),
    );
  }

  static Future goBookingPage(
    BuildContext context, {
    required ShopData? shop,
    required int id,
  }) async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) => ShopBloc()
                ..add(ShopEvent.fetchShopById(context, shop: shop))
                ..add(ShopEvent.generateLink(context)),
            ),
            BlocProvider(
              create: (context) =>
                  BookingBloc()
                    ..add(BookingEvent.fetchBookingById(context, id: id)),
            ),
            BlocProvider(create: (context) => FormBloc()),
            BlocProvider(
              create: (context) =>
                  ReviewBloc()
                    ..add(ReviewEvent.checkReview(context, bookingId: id)),
            ),
          ],
          child: BookingPage(shopId: shop?.id ?? 0),
        ),
      ),
    );
  }

  static void goBookingNotesPage(
    BuildContext context, {
    required BookingModel? book,
  }) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [BlocProvider.value(value: context.read<BookingBloc>())],
          child: NotesPage(booking: book),
        ),
      ),
    );
  }

  static Future goAddNotePage(BuildContext context, {required int shopId}) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider(
              create: (context) => MembershipBloc()
                ..add(MembershipEvent.myMemberships(context, shopId: shopId)),
            ),
            BlocProvider(create: (context) => CartBloc()),
            BlocProvider(
              create: (context) =>
                  GiftCartBloc()
                    ..add(GiftCartEvent.myGiftCart(context, shopId: shopId)),
            ),
            BlocProvider.value(value: context.read<BookingBloc>()),
            BlocProvider(
              create: (context) =>
                  CheckoutBloc()..add(CheckoutEvent.fetchUserAddress(context)),
            ),
          ],
          child: const AddNotePage(),
        ),
      ),
    );
  }

  static Future goSelectBookTimePage(
    BuildContext context, {
    required Map<int, MasterModel> selectMasters,
  }) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            // BlocProvider(create: (context) => BookingBloc()),
            BlocProvider.value(
              value: context.read<BookingBloc>()
                ..add(
                  BookingEvent.checkTime(context, startTime: DateTime.now()),
                ),
            ),
          ],
          child: SelectBookTime(selectMasters: selectMasters),
        ),
      ),
    );
  }

  static void goPaymentBottomSheet(
    BuildContext context, {
    required List<MasterModel>? serviceMasters,
    required num? totalPrice,
    required CustomColorSet colors,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      maxChildSize: 1,
      modal: (c) => MultiBlocProvider(
        providers: [BlocProvider.value(value: context.read<BookingBloc>())],
        child: BookingPaymentBottomSheet(
          colors: colors,
          controller: c,
          totalPrice: totalPrice,
        ),
      ),
    );
  }

  static void goPaymentPayLater(
    BuildContext context, {
    required BookingModel? booking,
    required CustomColorSet colors,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      paddingTop: MediaQuery.of(context).padding.top,
      modal: (c) => BlocProvider.value(
        value: context.read<BookingBloc>()
          ..add(BookingEvent.fetchPayments(context, payLater: true)),
        child: BookingPaymentBottomSheet(
          colors: colors,
          booking: booking,
          controller: c,
        ),
      ),
      isDrag: true,
      radius: 12,
    );
  }

  static void goAddressListServiceBottomSheet(
    BuildContext context, {
    required CustomColorSet colors,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      maxChildSize: 1,
      modal: (c) => MultiBlocProvider(
        providers: [
          BlocProvider.value(value: context.read<CheckoutBloc>()),
          BlocProvider.value(value: context.read<BookingBloc>()),
        ],
        child: AddressListService(colors: colors, controller: c),
      ),
    );
  }

  static void goConfirmPage(BuildContext context) {
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (context) => const ConfirmPage()),
      (route) => route.isFirst,
    );
  }

  static void goFailPage(BuildContext context, {String? text}) {
    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (context) => FailurePage(text: text)),
      (route) => route.isFirst,
    );
  }

  static Future goAddReviewBookingPage(
    BuildContext context, {
    required int? shopId,
    required BookingModel? booking,
  }) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(value: context.read<BookingBloc>()),
            BlocProvider(
              create: (context) =>
                  booking?.review == null ? ReviewBloc() : ReviewBloc()
                    ..add(
                      ReviewEvent.selectOfTypeFromReview(
                        review: booking?.review,
                      ),
                    ),
            ),
          ],
          child: AddReviewBooking(
            shopId: shopId,
            bookingModel: booking,
            review: booking?.review,
          ),
        ),
      ),
    );
  }

  static Future goFormOptionPage(
    BuildContext context, {
    required int? formOptionId,
    FormOptionsData? form,
  }) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MultiBlocProvider(
          providers: [
            BlocProvider.value(
              value: context.read<FormBloc>()
                ..add(
                  FormEvent.fetchSingleForms(
                    context,
                    id: formOptionId,
                    form: form,
                  ),
                ),
            ),
            BlocProvider.value(value: context.read<BookingBloc>()),
          ],
          child: const FormOptionsPage(),
        ),
      ),
    );
  }

  static void goSelectMemberships(
    BuildContext context, {
    required CustomColorSet colors,
    required int? serviceId,
    required int? shopId,
    required MasterModel? selectMaster,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      maxChildSize: 1,
      modal: (c) => MultiBlocProvider(
        providers: [
          BlocProvider(
            create: (context) => MembershipBloc()
              ..add(
                MembershipEvent.myMemberships(
                  context,
                  serviceId: serviceId,
                  shopId: shopId,
                ),
              ),
          ),
          BlocProvider.value(value: context.read<BookingBloc>()),
        ],
        child: SelectMembershipScreen(
          colors: colors,
          controller: c,
          serviceId: serviceId,
          selectMaster: selectMaster,
        ),
      ),
    );
  }

  static void goSelectGiftCart(
    BuildContext context, {
    required CustomColorSet colors,
    required int? shopId,
  }) {
    return AppHelpers.showCustomModalBottomSheetDrag(
      context,
      maxChildSize: 1,
      modal: (c) => MultiBlocProvider(
        providers: [
          BlocProvider(
            create: (context) => GiftCartBloc()
              ..add(
                GiftCartEvent.myGiftCart(context, shopId: shopId, valid: true),
              ),
          ),
          BlocProvider.value(value: context.read<BookingBloc>()),
        ],
        child: SelectGiftCartScreen(colors: colors, controller: c),
      ),
    );
  }
}
