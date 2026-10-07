import 'package:flutter/material.dart';
import 'package:demand/domain/model/model/shop_model.dart';
import 'package:demand/domain/model/response/delivery_point_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:intl/intl.dart';

abstract class TimeService {
  TimeService._();

  static final String _timeFormat = AppHelpers.getHourFormat24()
      ? 'HH:mm'
      : 'h:mm a';

  static DateTime dateFormatYMD(DateTime? time) {
    return DateTime.tryParse(
          DateFormat("yyyy-MM-dd").format(time ?? DateTime.now()),
        ) ??
        DateTime.now();
  }

  static String dateFormatMDYHm(DateTime? time) {
    return DateFormat(
      "MMM d, yyyy - $_timeFormat",
    ).format(time ?? DateTime.now());
  }

  static String dateFormatYMDHm(DateTime? time) {
    return DateFormat("yyyy-MM-dd HH:mm").format(time ?? DateTime.now());
  }

  static String dateFormatDMY(DateTime? time) {
    return DateFormat("d MMM, yyyy").format(time ?? DateTime.now());
  }

  static String dateFormatDM(DateTime? time) {
    if (DateTime.now().year == time?.year) {
      return DateFormat("d MMMM").format(time ?? DateTime.now());
    }
    return DateFormat("d MMMM, yyyy").format(time ?? DateTime.now());
  }

  static String timeFormat(DateTime? time) {
    return DateFormat(_timeFormat).format(time ?? DateTime.now());
  }

  static String dateFormatWDM(DateTime? time) {
    return DateFormat("EEE, d MMM").format(time ?? DateTime.now());
  }

  static String dateFormatForChat(DateTime? time) {
    if ((DateTime.now().difference(time ?? DateTime.now()).inHours) > 24 &&
        (DateTime.now().difference(time ?? DateTime.now()).inDays) < 7) {
      return DateFormat("EEEE").format(time ?? DateTime.now());
    }
    if ((DateTime.now().difference(time ?? DateTime.now()).inDays) > 7) {
      return DateFormat("MMM/d/yyyy").format(time ?? DateTime.now());
    }
    return DateFormat(_timeFormat).format(time ?? DateTime.now());
  }

  static String dateFormatForNotification(DateTime? time) {
    return DateFormat("d MMM, $_timeFormat").format(time ?? DateTime.now());
  }

  static String formatHHMMSS(int seconds) {
    seconds = (seconds % 3600).truncate();
    int minutes = (seconds / 60).truncate();
    String minutesStr = (minutes).toString().padLeft(2, '0');
    String secondsStr = (seconds % 60).toString().padLeft(2, '0');
    return "$minutesStr:$secondsStr";
  }

  static String shopTime(List<WorkingDay>? days) {
    String time = "";
    days?.forEach((element) {
      if ((element.day?.toLowerCase() ?? "") ==
          DateFormat("EEEE").format(DateTime.now()).toLowerCase()) {
        if (!(element.disabled ?? false)) {
          time =
              "${timeFormatTime(element.from)} - ${timeFormatTime(element.to)}";
        } else {
          time = AppHelpers.getTranslation(TrKeys.shopIsClosed);
        }
        return;
      }
    });

    return time;
  }

  static String shopCheckOpen({
    required bool? isOpen,
    required List<WorkingDay>? days,
    required List<ShopClosedDate>? closedDays,
  }) {
    String open = TrKeys.open;
    if (!(isOpen ?? false)) {
      open = TrKeys.closed;
    }
    closedDays?.forEach((element) {
      if (element.day?.year == DateTime.now().year) {
        if (element.day?.month == DateTime.now().month) {
          if (element.day?.day == DateTime.now().day) {
            open = TrKeys.closed;
            return;
          }
        }
      }
    });

    if (open == TrKeys.open) {
      days?.forEach((element) {
        if ((element.day?.toLowerCase() ?? "") ==
            DateFormat("EEEE").format(DateTime.now()).toLowerCase()) {
          TimeOfDay begin = TimeOfDay(
            hour:
                int.tryParse(
                  element.from?.substring(0, element.from?.indexOf(":")) ?? "",
                ) ??
                1,
            minute:
                int.tryParse(
                  element.from?.substring(
                        element.from?.indexOf(":") ?? 0 + 1,
                      ) ??
                      "",
                ) ??
                0,
          );
          TimeOfDay end = TimeOfDay(
            hour:
                int.tryParse(
                  element.to?.substring(0, element.to?.indexOf(":")) ?? "",
                ) ??
                1,
            minute:
                int.tryParse(
                  element.to?.substring(element.to?.indexOf(":") ?? 0 + 1) ??
                      "",
                ) ??
                0,
          );
          if (begin.hour >= TimeOfDay.now().hour &&
              end.hour <= TimeOfDay.now().hour) {
            open = TrKeys.closed;
          }
          return;
        }
      });
    }

    return AppHelpers.getTranslation(open);
  }

  static String timeFormatTime(String? time) {
    if (time == null) {
      return '';
    }
    return DateFormat(_timeFormat).format(
      DateTime.now()
          .add(Duration(days: 1))
          .copyWith(
            hour: int.tryParse(time.substring(0, time.indexOf(':'))) ?? 0,
            minute:
                int.tryParse(
                  time.substring(time.indexOf(':') + 1, time.length),
                ) ??
                0,
          ),
    );
  }
}
