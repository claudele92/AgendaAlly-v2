import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:flutter_libphonenumber/flutter_libphonenumber.dart';

import '../../app_constants.dart';
import '../../infrastructure/local_storage/local_storage.dart';
import '../../infrastructure/service/services.dart';
import '../style/style.dart';
import '../style/theme/theme.dart';

class CustomPhoneField extends StatefulWidget {
  final CustomColorSet colors;
  final ValueChanged<String> onChanged;
  final String? initialValue;
  final TextEditingController? controller;
  final String? Function(String?)? validator;
  final bool autoValidate;

  const CustomPhoneField({
    super.key,
    required this.colors,
    required this.onChanged,
    this.initialValue,
    this.controller,
    this.validator,
    this.autoValidate = true,
  });

  @override
  State<CustomPhoneField> createState() => _CustomPhoneFieldState();
}

class _CustomPhoneFieldState extends State<CustomPhoneField> {
  late TextEditingController _controller;
  late CountryWithPhoneCode _currentCountry;
  List<CountryWithPhoneCode> _allCountries = [];
  String _placeholderHint = '';
  bool _isValid = true;
  String? _errorMessage;
  bool _isInitialized = false;

  @override
  void initState() {
    super.initState();
    _controller = widget.controller ?? TextEditingController();

    if (widget.initialValue != null && widget.controller == null) {
      _controller.text = widget.initialValue!;
    }

    _initCountry();
  }

  Future<void> _initCountry() async {
    final countryCode =
        LocalStorage.getAddress()?.countryCode ?? AppConstants.countryCodeISO;

    await init();
    _allCountries = CountryManager().countries;

    try {
      _currentCountry = _allCountries.firstWhere(
        (c) => c.countryCode == countryCode,
      );
    } catch (e) {
      try {
        _currentCountry = _allCountries.firstWhere(
          (c) => c.countryCode == 'UZ',
        );
      } catch (e) {
        _currentCountry = const CountryWithPhoneCode.us();
      }
    }

    if (mounted) {
      setState(() {
        _isInitialized = true;
      });
      _updatePlaceholder();
    }
  }

  void _updatePlaceholder() {
    setState(() {
      _placeholderHint = _currentCountry.exampleNumberMobileInternational
          .substring(_currentCountry.phoneCode.length + 2);
    });
  }

  Future<void> _validateNumber(String value) async {
    if (value.isEmpty) {
      setState(() {
        _isValid = true;
        _errorMessage = null;
      });
      return;
    }

    // Custom validator agar berilgan bo'lsa
    if (widget.validator != null) {
      final fullNumber = '+${_currentCountry.phoneCode}$value';
      final customError = widget.validator!(fullNumber);
      if (customError != null) {
        setState(() {
          _isValid = false;
          _errorMessage = customError;
        });
        return;
      }
    }

    // Auto-validation libphonenumber bilan
    if (widget.autoValidate) {
      try {
        final fullNumber = '+${_currentCountry.phoneCode}$value';
        final result = await parse(
          fullNumber,
          region: _currentCountry.countryCode,
        );

        final isValidNumber = result['type'] != null;
        setState(() {
          _isValid = isValidNumber;
          _errorMessage = isValidNumber
              ? null
              : AppHelpers.getTranslation(TrKeys.phoneNumberIsNotValid);
        });
      } catch (e) {
        setState(() {
          _isValid = false;
          _errorMessage = AppHelpers.getTranslation(
            TrKeys.phoneNumberIsNotValid,
          );
        });
      }
    }
  }

  /// Public method to validate from outside (for forms)
  bool validate() {
    final value = _controller.text;
    if (value.isEmpty) {
      setState(() {
        _isValid = false;
        _errorMessage = AppHelpers.getTranslation(TrKeys.phoneNumberIsNotValid);
      });
      return false;
    }
    return _isValid;
  }

  @override
  void dispose() {
    if (widget.controller == null) {
      _controller.dispose();
    }
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (!_isInitialized) {
      return Container(
        height: 56.r,
        decoration: BoxDecoration(
          color: widget.colors.socialButtonColor,
          borderRadius: BorderRadius.circular(10.r),
          border: Border.all(color: widget.colors.icon),
        ),
        child: Center(
          child: SizedBox(
            width: 20.r,
            height: 20.r,
            child: CircularProgressIndicator(
              strokeWidth: 2,
              valueColor: AlwaysStoppedAnimation(widget.colors.primary),
            ),
          ),
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            GestureDetector(
              onTap: _showCountryPicker,
              child: Container(
                padding: EdgeInsets.symmetric(horizontal: 12.r, vertical: 11.r),
                decoration: BoxDecoration(
                  color: widget.colors.socialButtonColor,
                  borderRadius: BorderRadius.only(
                    topLeft: Radius.circular(10.r),
                    bottomLeft: Radius.circular(10.r),
                  ),
                  border: Border.all(
                    color: _isValid ? widget.colors.icon : CustomStyle.red,
                    width: _isValid ? 1 : 1.5,
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (AppConstants.showFlag) ...[
                      Text(
                        _getFlag(_currentCountry.countryCode),
                        style: CustomStyle.interNormal(
                          size: 20,
                          color: widget.colors.textBlack,
                        ),
                      ),
                      6.horizontalSpace,
                    ],
                    Text(
                      '+${_currentCountry.phoneCode}',
                      style: CustomStyle.interNormal(
                        size: 14,
                        color: widget.colors.textBlack,
                      ),
                    ),
                    if (AppConstants.showArrowIcon) ...[
                      4.horizontalSpace,
                      Icon(
                        Icons.arrow_drop_down,
                        size: 20.r,
                        color: widget.colors.textBlack,
                      ),
                    ],
                  ],
                ),
              ),
            ),

            // Phone input field
            Expanded(
              child: TextField(
                controller: _controller,
                cursorWidth: 1.5,
                cursorColor: widget.colors.primary,
                style: CustomStyle.interNormal(
                  size: 15,
                  color: widget.colors.textBlack,
                ),
                keyboardType: TextInputType.phone,
                textAlignVertical: const TextAlignVertical(y: .2),
                decoration: InputDecoration(
                  floatingLabelBehavior: FloatingLabelBehavior.always,
                  fillColor: widget.colors.socialButtonColor,
                  filled: true,
                  counterText: '',
                  hintText: _placeholderHint,
                  hintStyle: CustomStyle.interNormal(
                    size: 14,
                    color: widget.colors.textHint,
                  ),
                  contentPadding: EdgeInsets.symmetric(
                    horizontal: 16.r,
                    vertical: 16.r,
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.only(
                      topRight: Radius.circular(10.r),
                      bottomRight: Radius.circular(10.r),
                    ),
                    borderSide: BorderSide(
                      color: _isValid ? widget.colors.icon : CustomStyle.red,
                      width: _isValid ? 1 : 1.5,
                    ),
                  ),
                  errorBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.only(
                      topRight: Radius.circular(10.r),
                      bottomRight: Radius.circular(10.r),
                    ),
                    borderSide: const BorderSide(
                      color: CustomStyle.red,
                      width: 1.5,
                    ),
                  ),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.only(
                      topRight: Radius.circular(10.r),
                      bottomRight: Radius.circular(10.r),
                    ),
                    borderSide: BorderSide(color: widget.colors.icon),
                  ),
                  disabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.only(
                      topRight: Radius.circular(10.r),
                      bottomRight: Radius.circular(10.r),
                    ),
                    borderSide: BorderSide(color: widget.colors.icon),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.only(
                      topRight: Radius.circular(10.r),
                      bottomRight: Radius.circular(10.r),
                    ),
                    borderSide: BorderSide(
                      color: _isValid ? widget.colors.icon : CustomStyle.red,
                      width: 1.5,
                    ),
                  ),
                  focusedErrorBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.only(
                      topRight: Radius.circular(10.r),
                      bottomRight: Radius.circular(10.r),
                    ),
                    borderSide: const BorderSide(
                      color: CustomStyle.red,
                      width: 1.5,
                    ),
                  ),
                ),
                inputFormatters: [
                  LibPhonenumberTextFormatter(
                    country: _currentCountry,
                    phoneNumberType: PhoneNumberType.mobile,
                    phoneNumberFormat: PhoneNumberFormat.national,
                    inputContainsCountryCode: false,
                    shouldKeepCursorAtEndOfInput: true,
                  ),
                ],
                onChanged: (value) {
                  final fullNumber = '+${_currentCountry.phoneCode}$value';
                  widget.onChanged(fullNumber);
                  _validateNumber(value);
                },
              ),
            ),
          ],
        ),

        // Error message
        if (!_isValid &&
            _controller.text.isNotEmpty &&
            _errorMessage != null) ...[
          8.verticalSpace,
          Padding(
            padding: EdgeInsets.only(left: 12.r),
            child: Row(
              children: [
                Icon(Icons.error_outline, size: 14.r, color: CustomStyle.red),
                6.horizontalSpace,
                Expanded(
                  child: Text(
                    _errorMessage!,
                    style: CustomStyle.interRegular(
                      size: 12.sp,
                      color: CustomStyle.red,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ],
    );
  }

  Future<void> _showCountryPicker() async {
    final sortedCountries = List<CountryWithPhoneCode>.from(_allCountries)
      ..sort((a, b) => (a.countryName ?? '').compareTo(b.countryName ?? ''));

    final selected = await showModalBottomSheet<CountryWithPhoneCode>(
      context: context,
      isScrollControlled: true,
      backgroundColor: widget.colors.backgroundColor,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20.r)),
      ),
      builder: (context) {
        return DraggableScrollableSheet(
          initialChildSize: 0.75,
          minChildSize: 0.5,
          maxChildSize: 0.9,
          expand: false,
          builder: (context, scrollController) {
            return Column(
              children: [
                // Drag handle
                Container(
                  margin: EdgeInsets.only(top: 12.r, bottom: 8.r),
                  width: 40.r,
                  height: 4.r,
                  decoration: BoxDecoration(
                    color: widget.colors.icon,
                    borderRadius: BorderRadius.circular(2.r),
                  ),
                ),

                // Title
                Padding(
                  padding: EdgeInsets.symmetric(
                    horizontal: 16.r,
                    vertical: 8.r,
                  ),
                  child: Text(
                    AppHelpers.getTranslation(TrKeys.selectCountry),
                    style: CustomStyle.interSemi(
                      size: 18,
                      color: widget.colors.textBlack,
                    ),
                  ),
                ),

                Divider(color: widget.colors.icon, height: 1),

                16.verticalSpace,

                // Countries list
                Expanded(
                  child: ListView.separated(
                    controller: scrollController,
                    padding: EdgeInsets.symmetric(horizontal: 16.r),
                    itemCount: sortedCountries.length,
                    separatorBuilder: (context, index) => Divider(
                      color: widget.colors.icon.withValues(alpha: 0.3),
                      height: 1,
                    ),
                    itemBuilder: (context, index) {
                      final country = sortedCountries[index];
                      final isSelected =
                          country.countryCode == _currentCountry.countryCode;

                      return InkWell(
                        onTap: () => Navigator.of(context).pop(country),
                        child: Container(
                          padding: EdgeInsets.symmetric(vertical: 14.r),
                          child: Row(
                            children: [
                              // Flag
                              Text(
                                _getFlag(country.countryCode),
                                style: CustomStyle.interNormal(
                                  size: 28,
                                  color: widget.colors.textBlack,
                                ),
                              ),

                              16.horizontalSpace,

                              // Country name
                              Expanded(
                                child: Text(
                                  country.countryName ?? country.countryCode,
                                  style: CustomStyle.interNormal(
                                    size: 15,
                                    color: widget.colors.textBlack,
                                  ),
                                ),
                              ),

                              12.horizontalSpace,

                              // Phone code
                              Text(
                                '+${country.phoneCode}',
                                style: CustomStyle.interRegular(
                                  size: 14,
                                  color: widget.colors.textHint,
                                ),
                              ),

                              8.horizontalSpace,

                              // Selected indicator
                              if (isSelected)
                                Icon(
                                  Icons.check_circle,
                                  size: 20.r,
                                  color: widget.colors.primary,
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
          },
        );
      },
    );

    if (selected != null &&
        selected.countryCode != _currentCountry.countryCode) {
      setState(() {
        _currentCountry = selected;
        _updatePlaceholder();
        _controller.clear();
        _isValid = true;
        _errorMessage = null;
      });
      widget.onChanged('+${_currentCountry.phoneCode}');
    }
  }

  /// Get current phone number with country code
  String getFullPhoneNumber() {
    return '+${_currentCountry.phoneCode}${_controller.text}';
  }

  /// Get current country
  CountryWithPhoneCode getCurrentCountry() {
    return _currentCountry;
  }

  String _getFlag(String countryCode) {
    return countryCode.toUpperCase().replaceAllMapped(
      RegExp(r'[A-Z]'),
      (match) => String.fromCharCode(match.group(0)!.codeUnitAt(0) + 127397),
    );
  }
}
