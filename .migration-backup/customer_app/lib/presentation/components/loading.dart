import 'dart:io' show Platform;
import 'package:flutter/cupertino.dart';
import 'package:flutter/material.dart';
import 'package:demand/presentation/style/theme/theme_wrapper.dart';

class Loading extends StatelessWidget {
  final bool changeColor;

  const Loading({super.key, this.changeColor = false});

  @override
  Widget build(BuildContext context) {
    return ThemeWrapper(
      builder: (colors, c) {
        return Center(
          child: Platform.isAndroid
              ? CircularProgressIndicator(
                  color: changeColor ? colors.white : colors.textBlack,
                )
              : CupertinoActivityIndicator(
                  color: changeColor ? colors.white : colors.textBlack,
                ),
        );
      },
    );
  }
}
