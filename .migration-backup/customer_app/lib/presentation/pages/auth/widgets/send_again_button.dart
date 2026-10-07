import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:demand/application/auth/auth_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class SendAgainButton extends StatefulWidget {
  final bool isLoading;
  final CustomColorSet colors;
  final String phone;
  final bool isReset;

  const SendAgainButton({
    super.key,
    required this.isLoading,
    required this.colors,
    required this.phone,
    this.isReset = false,
  });

  @override
  State<SendAgainButton> createState() => _SendAgainButtonState();
}

class _SendAgainButtonState extends State<SendAgainButton> {
  Timer? timer;
  int initialTime = 120;
  String timerText = "02:00";

  void startTime() {
    timer?.cancel();
    initialTime = 120;
    if (timer != null) {
      initialTime = 120;
      timer?.cancel();
    }
    timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (initialTime < 1) {
        timer.cancel();
        timerText = "0";
        setState(() {});
      } else {
        initialTime--;
        timerText = TimeService.formatHHMMSS(initialTime);
        setState(() {});
      }
    });
  }

  @override
  void initState() {
    startTime();
    super.initState();
  }

  @override
  void dispose() {
    timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomButton(
      title: timerText != "0"
          ? timerText
          : AppHelpers.getTranslation(TrKeys.send),
      bgColor: widget.colors.transparent,
      titleColor: widget.colors.white,
      borderColor: widget.colors.textWhite,
      onTap: () {
        final event = context.read<AuthBloc>();
        if (timerText == "0") {
          if (widget.isReset) {
            if (!AppHelpers.checkPhone(widget.phone)) {
              event.add(
                AuthEvent.forgotPassword(
                  context,
                  phone: widget.phone,
                  onSuccess: () {
                    event.add(AuthEvent.setVerificationId(id: "id"));
                  },
                ),
              );
              return;
            }
          }
          if (!AppHelpers.checkPhone(widget.phone)) {
            event.add(AuthEvent.signUpEmail(context, email: widget.phone));
          }
          context.read<AuthBloc>().add(
            AuthEvent.forgotPassword(
              context,
              phone: widget.phone,
              onSuccess: () => startTime(),
            ),
          );
        }
      },
    );
  }
}
