import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:intl/intl.dart';
import 'package:demand/application/booking/booking_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class CustomDatePicker extends StatefulWidget {
  final BookingState state;
  final CustomColorSet colors;

  const CustomDatePicker({
    super.key,
    required this.state,
    required this.colors,
  });

  @override
  State<CustomDatePicker> createState() => _CustomDatePickerState();
}

class _CustomDatePickerState extends State<CustomDatePicker> {
  late ScrollController _scrollController;
  DateTime? _previousSelectedDate;

  @override
  void initState() {
    super.initState();
    _scrollController = ScrollController();
    _previousSelectedDate = widget.state.selectDateTime;
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _scrollToSelectedDate();
    });
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  void didUpdateWidget(CustomDatePicker oldWidget) {
    super.didUpdateWidget(oldWidget);
    final currentDate = widget.state.selectDateTime;
    final previousDate = _previousSelectedDate;

    if (currentDate != null && previousDate != null) {
      final currentMonth = DateTime(currentDate.year, currentDate.month);
      final previousMonth = DateTime(previousDate.year, previousDate.month);

      if (currentMonth != previousMonth) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          _scrollToSelectedDate();
        });
      }
    }

    _previousSelectedDate = currentDate;
  }

  void _scrollToSelectedDate() {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final selectedDate = widget.state.selectDateTime ?? today;

    List<DateTime> availableDates = _buildAvailableDates(now, today);

    final selectedDateNormalized = DateTime(
      selectedDate.year,
      selectedDate.month,
      selectedDate.day,
    );

    int index = -1;
    for (int i = 0; i < availableDates.length; i++) {
      final dateNormalized = DateTime(
        availableDates[i].year,
        availableDates[i].month,
        availableDates[i].day,
      );
      if (dateNormalized.isAtSameMomentAs(selectedDateNormalized)) {
        index = i;
        break;
      }
    }

    if (index >= 0) {
      final itemWidth = 70.r + 8.r;
      final targetOffset = index * itemWidth;

      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          targetOffset,
          duration: const Duration(milliseconds: 300),
          curve: Curves.easeInOut,
        );
      }
    }
  }

  List<DateTime> _buildAvailableDates(DateTime now, DateTime today) {
    final maxDate = today.add(const Duration(days: 90));
    List<DateTime> availableDates = [];

    final currentMonthEnd = DateTime(now.year, now.month + 1, 0);
    for (int day = now.day; day <= currentMonthEnd.day; day++) {
      availableDates.add(DateTime(now.year, now.month, day));
    }

    DateTime currentDate = DateTime(now.year, now.month + 1, 1);
    while (currentDate.isBefore(maxDate) ||
        currentDate.isAtSameMomentAs(maxDate)) {
      final monthEnd = DateTime(currentDate.year, currentDate.month + 1, 0);
      final lastDay = monthEnd.day;

      for (int day = 1; day <= lastDay; day++) {
        final date = DateTime(currentDate.year, currentDate.month, day);
        if (date.isAfter(maxDate)) break;
        availableDates.add(date);
      }

      currentDate = DateTime(currentDate.year, currentDate.month + 1, 1);
    }

    return availableDates;
  }

  @override
  Widget build(BuildContext context) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final maxDate = today.add(const Duration(days: 90));
    final selectedDate = widget.state.selectDateTime ?? today;

    final availableDates = _buildAvailableDates(now, today);

    return Column(
      children: [
        Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.r),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              IconButton(
                icon: Icon(
                  Icons.arrow_back_ios,
                  color: () {
                    final currentMonth = DateTime(
                      selectedDate.year,
                      selectedDate.month,
                    );
                    final prevMonth = DateTime(
                      currentMonth.year,
                      currentMonth.month - 1,
                    );
                    final prevMonthYear = prevMonth.year;
                    final prevMonthMonth = prevMonth.month;
                    final todayYear = today.year;
                    final todayMonth = today.month;

                    if (prevMonthYear < todayYear ||
                        (prevMonthYear == todayYear &&
                            prevMonthMonth < todayMonth)) {
                      return Colors.grey;
                    }
                    return widget.colors.textBlack;
                  }(),
                  size: 20.r,
                ),
                onPressed: () {
                  final currentMonth = DateTime(
                    selectedDate.year,
                    selectedDate.month,
                  );
                  final prevMonth = DateTime(
                    currentMonth.year,
                    currentMonth.month - 1,
                  );

                  final prevMonthYear = prevMonth.year;
                  final prevMonthMonth = prevMonth.month;
                  final todayYear = today.year;
                  final todayMonth = today.month;

                  if (prevMonthYear < todayYear ||
                      (prevMonthYear == todayYear &&
                          prevMonthMonth < todayMonth)) {
                    return;
                  }

                  DateTime newDate;
                  if (prevMonthYear == todayYear &&
                      prevMonthMonth == todayMonth) {
                    newDate = today;
                  } else {
                    newDate = DateTime(prevMonthYear, prevMonthMonth, 1);
                  }

                  context.read<BookingBloc>().add(
                    BookingEvent.selectDateTime(selectDateTime: newDate),
                  );
                },
              ),
              GestureDetector(
                onTap: () =>
                    _showMonthYearPicker(context, selectedDate, today, maxDate),
                child: Text(
                  DateFormat(
                    'MMMM yyyy',
                    LocalStorage.getLanguage()?.locale ?? 'en',
                  ).format(selectedDate),
                  style: CustomStyle.interNoSemi(
                    color: widget.colors.textBlack,
                    size: 16,
                  ),
                ),
              ),
              IconButton(
                icon: Icon(
                  Icons.arrow_forward_ios,
                  color: () {
                    final currentMonth = DateTime(
                      selectedDate.year,
                      selectedDate.month,
                    );
                    final nextMonth = DateTime(
                      currentMonth.year,
                      currentMonth.month + 1,
                    );
                    final nextMonthFirstDay = DateTime(
                      nextMonth.year,
                      nextMonth.month,
                      1,
                    );

                    if (nextMonthFirstDay.isAfter(maxDate)) {
                      return Colors.grey;
                    }
                    return widget.colors.textBlack;
                  }(),
                  size: 20.r,
                ),
                onPressed: () {
                  final currentMonth = DateTime(
                    selectedDate.year,
                    selectedDate.month,
                  );
                  final nextMonth = DateTime(
                    currentMonth.year,
                    currentMonth.month + 1,
                  );
                  if (nextMonth.isAfter(maxDate)) {
                    return;
                  }
                  final newDate = DateTime(nextMonth.year, nextMonth.month, 1);
                  if (newDate.isAfter(maxDate)) {
                    return;
                  }
                  context.read<BookingBloc>().add(
                    BookingEvent.selectDateTime(selectDateTime: newDate),
                  );
                },
              ),
            ],
          ),
        ),
        16.verticalSpace,
        SizedBox(
          height: 100.r,
          child: ListView.builder(
            controller: _scrollController,
            scrollDirection: Axis.horizontal,
            padding: EdgeInsets.symmetric(horizontal: 16.r),
            itemCount: availableDates.length,
            itemBuilder: (context, index) {
              final date = availableDates[index];
              final isSelected =
                  date.year == selectedDate.year &&
                  date.month == selectedDate.month &&
                  date.day == selectedDate.day;
              final isToday =
                  date.year == today.year &&
                  date.month == today.month &&
                  date.day == today.day;

              return GestureDetector(
                onTap: () {
                  context.read<BookingBloc>().add(
                    BookingEvent.selectDateTime(selectDateTime: date),
                  );
                },
                child: Container(
                  width: 70.r,
                  margin: EdgeInsets.only(right: 8.r),
                  decoration: BoxDecoration(
                    color: isSelected
                        ? widget.colors.primary
                        : (isToday
                              ? widget.colors.white
                              : widget.colors.transparent),
                    borderRadius: BorderRadius.circular(8.r),
                    border: isToday && !isSelected
                        ? Border.all(color: widget.colors.primary, width: 1.5)
                        : Border.all(color: widget.colors.border, width: 1),
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        DateFormat(
                          'EEE',
                          LocalStorage.getLanguage()?.locale ?? 'en',
                        ).format(date),
                        style: CustomStyle.interNoSemi(
                          color: isSelected
                              ? widget.colors.white
                              : (LocalStorage.getMode()
                                    ? widget.colors.textBlack
                                    : widget.colors.white),
                          size: 12,
                        ),
                      ),
                      4.verticalSpace,
                      Text(
                        '${date.day}',
                        style: CustomStyle.interNoSemi(
                          color: isSelected
                              ? widget.colors.white
                              : (LocalStorage.getMode()
                                    ? widget.colors.textBlack
                                    : widget.colors.white),
                          size: isSelected ? 20 : 16,
                        ),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  void _showMonthYearPicker(
    BuildContext context,
    DateTime selectedDate,
    DateTime minDate,
    DateTime maxDate,
  ) {
    showModalBottomSheet(
      context: context,
      backgroundColor: widget.colors.backgroundColor,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24.r)),
      ),
      builder: (context) {
        return StatefulBuilder(
          builder: (context, setState) {
            DateTime tempDate = selectedDate;
            final maxYear = maxDate.year;
            final minYear = minDate.year;
            final availableYears = List.generate(
              maxYear - minYear + 1,
              (index) => minYear + index,
            );

            List<int> getAvailableMonths() {
              if (tempDate.year == minDate.year &&
                  tempDate.year == maxDate.year) {
                return List.generate(
                  maxDate.month - minDate.month + 1,
                  (index) => minDate.month + index,
                );
              } else if (tempDate.year == minDate.year) {
                return List.generate(
                  12 - minDate.month + 1,
                  (index) => minDate.month + index,
                );
              } else if (tempDate.year == maxDate.year) {
                return List.generate(maxDate.month, (index) => index + 1);
              } else {
                return List.generate(12, (index) => index + 1);
              }
            }

            final availableMonths = getAvailableMonths();

            return Container(
              padding: EdgeInsets.all(24.r),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    AppHelpers.getTranslation(TrKeys.selectDateTime),
                    style: CustomStyle.interNoSemi(
                      color: widget.colors.textBlack,
                      size: 18,
                    ),
                  ),
                  24.verticalSpace,
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: [
                      Column(
                        children: [
                          Text(
                            'Year',
                            style: CustomStyle.interNormal(
                              color: widget.colors.textBlack,
                              size: 14,
                            ),
                          ),
                          8.verticalSpace,
                          Container(
                            height: 200.r,
                            width: 100.r,
                            decoration: BoxDecoration(
                              color: widget.colors.icon,
                              borderRadius: BorderRadius.circular(8.r),
                            ),
                            child: ListWheelScrollView.useDelegate(
                              itemExtent: 40.r,
                              physics: const FixedExtentScrollPhysics(),
                              controller: FixedExtentScrollController(
                                initialItem: availableYears
                                    .indexOf(tempDate.year)
                                    .clamp(0, availableYears.length - 1),
                              ),
                              onSelectedItemChanged: (index) {
                                final year = availableYears[index];
                                tempDate = DateTime(
                                  year,
                                  tempDate.month,
                                  tempDate.day > 28 ? 28 : tempDate.day,
                                );
                                final months = getAvailableMonths();
                                if (!months.contains(tempDate.month)) {
                                  tempDate = DateTime(
                                    year,
                                    months.first,
                                    tempDate.day > 28 ? 28 : tempDate.day,
                                  );
                                }
                                if (tempDate.isBefore(minDate)) {
                                  tempDate = minDate;
                                } else if (tempDate.isAfter(maxDate)) {
                                  tempDate = maxDate;
                                }
                                setState(() {});
                              },
                              childDelegate: ListWheelChildBuilderDelegate(
                                builder: (context, index) {
                                  final year = availableYears[index];
                                  final isSelected = year == tempDate.year;
                                  return Center(
                                    child: Text(
                                      '$year',
                                      style: CustomStyle.interNoSemi(
                                        color: isSelected
                                            ? widget.colors.primary
                                            : widget.colors.textBlack,
                                        size: isSelected ? 18 : 16,
                                      ),
                                    ),
                                  );
                                },
                                childCount: availableYears.length,
                              ),
                            ),
                          ),
                        ],
                      ),
                      Column(
                        children: [
                          Text(
                            'Month',
                            style: CustomStyle.interNormal(
                              color: widget.colors.textBlack,
                              size: 14,
                            ),
                          ),
                          8.verticalSpace,
                          Container(
                            height: 200.r,
                            width: 150.r,
                            decoration: BoxDecoration(
                              color: widget.colors.icon,
                              borderRadius: BorderRadius.circular(8.r),
                            ),
                            child: ListWheelScrollView.useDelegate(
                              itemExtent: 40.r,
                              physics: const FixedExtentScrollPhysics(),
                              controller: FixedExtentScrollController(
                                initialItem: availableMonths
                                    .indexOf(tempDate.month)
                                    .clamp(0, availableMonths.length - 1),
                              ),
                              onSelectedItemChanged: (index) {
                                final month = availableMonths[index];
                                tempDate = DateTime(
                                  tempDate.year,
                                  month,
                                  tempDate.day > 28 ? 28 : tempDate.day,
                                );
                                if (tempDate.isBefore(minDate)) {
                                  tempDate = minDate;
                                } else if (tempDate.isAfter(maxDate)) {
                                  tempDate = maxDate;
                                }
                                setState(() {});
                              },
                              childDelegate: ListWheelChildBuilderDelegate(
                                builder: (context, index) {
                                  final month = availableMonths[index];
                                  final monthName = DateFormat(
                                    'MMMM',
                                    LocalStorage.getLanguage()?.locale ??
                                        'en_US',
                                  ).format(DateTime(2024, month));
                                  final isSelected = month == tempDate.month;
                                  return Center(
                                    child: Text(
                                      monthName,
                                      style: CustomStyle.interNoSemi(
                                        color: isSelected
                                            ? widget.colors.primary
                                            : widget.colors.textBlack,
                                        size: isSelected ? 18 : 16,
                                      ),
                                    ),
                                  );
                                },
                                childCount: availableMonths.length,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                  24.verticalSpace,
                  CustomButton(
                    title: AppHelpers.getTranslation(TrKeys.save),
                    bgColor: widget.colors.primary,
                    titleColor: widget.colors.white,
                    onTap: () {
                      final now = DateTime.now();
                      final today = DateTime(now.year, now.month, now.day);
                      DateTime finalDate;

                      if (tempDate.year == today.year &&
                          tempDate.month == today.month) {
                        finalDate = today;
                      } else {
                        finalDate = DateTime(tempDate.year, tempDate.month, 1);
                      }

                      if (finalDate.isBefore(minDate)) {
                        finalDate = minDate;
                      } else if (finalDate.isAfter(maxDate)) {
                        finalDate = maxDate;
                      }

                      context.read<BookingBloc>().add(
                        BookingEvent.selectDateTime(selectDateTime: finalDate),
                      );
                      Navigator.pop(context);
                    },
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }
}
