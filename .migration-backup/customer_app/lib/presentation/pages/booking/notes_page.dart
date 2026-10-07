import 'dart:io';

import 'package:demand/app_constants.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/domain/model/response/booking_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';

class NotesPage extends StatefulWidget {
  final BookingModel? booking;

  const NotesPage({super.key, required this.booking});

  @override
  State<NotesPage> createState() => _NotesPageState();
}

class _NotesPageState extends State<NotesPage> {
  late TextEditingController controller;

  @override
  void initState() {
    controller = TextEditingController();
    super.initState();
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => KeyboardDismisser(
        isLtr: LocalStorage.getLangLtr(),
        child: SafeArea(
          bottom: false,
          child: Padding(
            padding: EdgeInsets.symmetric(horizontal: 16.r),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  AppHelpers.getTranslation(TrKeys.notes),
                  style: CustomStyle.interNoSemi(
                    color: colors.textBlack,
                    size: 18,
                  ),
                ),
                24.verticalSpace,
                BlocBuilder<BookingBloc, BookingState>(
                  builder: (context, state) {
                    return (widget.booking?.notes?.isNotEmpty ?? false)
                        ? ListView.builder(
                            padding: EdgeInsets.zero,
                            shrinkWrap: true,
                            itemCount: widget.booking?.notes?.length ?? 0,
                            itemBuilder: (context, index) {
                              final message = widget.booking?.notes?[index];
                              return Container(
                                margin: REdgeInsets.only(bottom: 6),
                                padding: REdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: colors.primary,
                                  borderRadius: BorderRadius.circular(8.r),
                                ),
                                child: Text(
                                  message ?? '',
                                  style: CustomStyle.interNormal(
                                    size: 15,
                                    color: colors.textBlack,
                                  ),
                                ),
                              );
                            },
                          )
                        : Padding(
                            padding: EdgeInsets.symmetric(vertical: 32.r),
                            child: Center(
                              child: Text(
                                AppHelpers.getTranslation(
                                  TrKeys.thereAreNoNotesHere,
                                ),
                                style: CustomStyle.interNormal(
                                  color: colors.textBlack,
                                ),
                              ),
                            ),
                          );
                  },
                ),
              ],
            ),
          ),
        ),
      ),
      floatingButtonLocation: FloatingActionButtonLocation.centerFloat,
      floatingButton: (colors) => Align(
        alignment: Alignment.bottomLeft,
        child: Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.r),
          child: Row(
            children: [
              PopButton(colors: colors),
              10.horizontalSpace,
              Expanded(
                child: Container(
                  color: colors.backgroundColor,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      6.verticalSpace,
                      Container(
                        height: 60.r,
                        margin: EdgeInsets.only(
                          bottom:
                              MediaQuery.viewInsetsOf(context).bottom +
                              MediaQuery.paddingOf(context).bottom +
                              (Platform.isAndroid ? 8.r : (-12.r)),
                        ),
                        padding: REdgeInsets.only(right: 16, left: 10),
                        decoration: BoxDecoration(
                          border: Border.all(color: colors.icon),
                          borderRadius: BorderRadius.circular(
                            AppConstants.radius.r,
                          ),
                          color: colors.icon,
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: <Widget>[
                            Expanded(
                              child: TextFormField(
                                controller: controller,
                                cursorWidth: 1.r,
                                cursorColor: colors.textBlack,
                                style: CustomStyle.interNormal(
                                  size: 14,
                                  letterSpacing: -0.5,
                                  color: colors.textBlack,
                                ),
                                decoration: InputDecoration(
                                  border: InputBorder.none,
                                  hintStyle: CustomStyle.interNormal(
                                    size: 12,
                                    letterSpacing: -0.5,
                                    color: colors.textHint,
                                  ),
                                  hintText: AppHelpers.getTranslation(
                                    TrKeys.typeSomething,
                                  ),
                                ),
                              ),
                            ),
                            8.horizontalSpace,
                            InkWell(
                              onTap: () {
                                context.read<BookingBloc>().add(
                                  BookingEvent.updateNotes(
                                    context,
                                    id: widget.booking?.id ?? 0,
                                    message: controller.text,
                                    onSuccess: () {
                                      context.read<BookingBloc>().add(
                                        BookingEvent.fetchBookingById(
                                          context,
                                          id:
                                              widget.booking?.parentId ??
                                              widget.booking?.id ??
                                              0,
                                        ),
                                      );
                                      Navigator.pop(context);
                                    },
                                  ),
                                );
                              },
                              child: Container(
                                width: 42.r,
                                height: 42.r,
                                decoration: BoxDecoration(
                                  color: colors.backgroundColor,
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(
                                  FlutterRemix.send_plane_2_line,
                                  size: 18.r,
                                  color: colors.textBlack,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
