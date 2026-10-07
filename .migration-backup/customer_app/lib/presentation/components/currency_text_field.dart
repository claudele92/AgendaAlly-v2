import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../infrastructure/local_storage/local_storage.dart';
import '../style/style.dart';
import '../style/theme/theme.dart';

class CurrencyTextField extends StatelessWidget {
  final TextEditingController? controller;
  final CustomColorSet colors;
  final Function(String? value)? onChange;

  const CurrencyTextField({
    super.key,
    this.controller,
    required this.colors,
    this.onChange,
  });

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      cursorColor: colors.textBlack,
      style: CustomStyle.interNormal(color: colors.textBlack),
      onChanged: onChange,
      inputFormatters: [CurrencyInputFormatter()],
      decoration: InputDecoration(
        hintText: '0.00',
        hintStyle: CustomStyle.interNormal(
          color: colors.textBlack.withValues(alpha: 0.5),
        ),
        contentPadding: EdgeInsets.symmetric(horizontal: 16),
        border: OutlineInputBorder(
          borderSide: BorderSide(color: colors.textHint),
          borderRadius: BorderRadius.circular(12.r),
        ),
        focusedBorder: OutlineInputBorder(
          borderSide: BorderSide(color: colors.textHint),
          borderRadius: BorderRadius.circular(12.r),
        ),
        enabledBorder: OutlineInputBorder(
          borderSide: BorderSide(color: colors.textHint),
          borderRadius: BorderRadius.circular(12.r),
        ),
        suffixIcon: Padding(
          padding: EdgeInsets.only(top: 10.r, left: 12.r, right: 10.r),
          child: Text(
            LocalStorage.getSelectedCurrency()?.symbol ?? '',
            style: CustomStyle.interNormal(color: colors.textBlack),
          ),
        ),
      ),
    );
  }
}

class CurrencyInputFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(
    TextEditingValue oldValue,
    TextEditingValue newValue,
  ) {
    String newText = newValue.text.replaceAll(' ', '');
    if (!_isValidDecimalInput(newText)) {
      return oldValue;
    }

    String formattedText = _formatWithThousandsSeparator(newText);
    return TextEditingValue(
      text: formattedText,
      selection: TextSelection.collapsed(offset: formattedText.length),
    );
  }

  bool _isValidDecimalInput(String value) {
    final regex = RegExp(r'^\d*\.?\d{0,4}$');
    return regex.hasMatch(value);
  }

  String _formatWithThousandsSeparator(String value) {
    if (value.isEmpty) return value;

    List<String> parts = value.split('.');
    String integerPart = parts[0];
    String decimalPart = parts.length > 1 ? '.${parts[1]}' : '';

    final buffer = StringBuffer();
    for (int i = 0; i < integerPart.length; i++) {
      if (i > 0 && (integerPart.length - i) % 3 == 0) {
        buffer.write(' ');
      }
      buffer.write(integerPart[i]);
    }
    return buffer.toString() + decimalPart;
  }
}
