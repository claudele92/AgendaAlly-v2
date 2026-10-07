import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/game/game_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class ScoreBoard extends StatelessWidget {
  final CustomColorSet colors;

  const ScoreBoard({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<GameBloc, GameState>(
      builder: (context, state) {
        final score = state.board?.score ?? 0;
        final best = state.board?.best ?? 0;
        return Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Score(
              label: AppHelpers.getTranslation(TrKeys.score),
              score: '$score',
              colors: colors,
            ),
            16.horizontalSpace,
            Score(
              label: AppHelpers.getTranslation(TrKeys.best),
              score: '$best',
              padding: const EdgeInsets.symmetric(
                horizontal: 16.0,
                vertical: 8.0,
              ),
              colors: colors,
            ),
          ],
        );
      },
    );
  }
}

class Score extends StatelessWidget {
  final CustomColorSet colors;

  const Score({
    super.key,
    required this.label,
    required this.score,
    this.padding,
    required this.colors,
  });

  final String label;
  final String score;
  final EdgeInsets? padding;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding:
          padding ??
          const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      decoration: BoxDecoration(
        color: colors.newBoxColor,
        borderRadius: BorderRadius.circular(8.0),
      ),
      child: Column(
        children: [
          Text(
            label.toUpperCase(),
            style: CustomStyle.interNormal(color: colors.textBlack),
          ),
          Text(score, style: CustomStyle.interSemi(color: colors.textBlack)),
        ],
      ),
    );
  }
}
