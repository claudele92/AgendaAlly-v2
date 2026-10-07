import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

import '../../../application/story/story_bloc.dart';
import '../../style/style.dart';
import '../../style/theme/theme.dart';
import 'widgets/story_image.dart';

class StoryPage extends StatefulWidget {
  final RefreshController controller;
  final int index;
  final CustomColorSet colors;

  const StoryPage({
    super.key,
    required this.controller,
    required this.index,
    required this.colors,
  });

  @override
  State<StoryPage> createState() => _StoryPageState();
}

class _StoryPageState extends State<StoryPage> {
  late PageController pageController;

  @override
  void initState() {
    pageController = PageController(initialPage: widget.index);
    super.initState();
  }

  @override
  void dispose() {
    pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: CustomStyle.bgDark,
      body: BlocBuilder<StoryBloc, StoryState>(
        builder: (context, state) {
          return PageView.builder(
            controller: pageController,
            itemCount: state.stories.length,
            physics: const PageScrollPhysics(),
            itemBuilder: (context, index) {
              return StoryImage(
                colors: widget.colors,
                stories: state.stories[index]!,
                nextPage: () {
                  if (index == state.stories.length - 2) {
                    context.read<StoryBloc>().add(
                      StoryEvent.fetchStory(
                        context,
                        controller: widget.controller,
                      ),
                    );
                  }
                  if (index != state.stories.length - 1) {
                    pageController.animateToPage(
                      ++index,
                      duration: const Duration(milliseconds: 500),
                      curve: Curves.easeIn,
                    );
                  } else {
                    Navigator.pop(context);
                  }
                },
                prevPage: () {
                  if (index != 0) {
                    pageController.animateToPage(
                      --index,
                      duration: const Duration(milliseconds: 500),
                      curve: Curves.easeIn,
                    );
                  } else {
                    Navigator.pop(context);
                  }
                },
              );
            },
          );
        },
      ),
    );
  }
}
