import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/blog/blog_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/route/app_route_setting.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class BlogList extends StatelessWidget {
  final CustomColorSet colors;

  const BlogList({super.key, required this.colors});

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<BlogBloc, BlogState>(
      builder: (context, state) {
        return state.blogs.isNotEmpty
            ? Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  32.verticalSpace,
                  TitleWidget(
                    title: AppHelpers.getTranslation(TrKeys.blogLast),
                    titleColor: colors.textBlack,
                    subTitle: AppHelpers.getTranslation(TrKeys.seeAll),
                    onTap: () {
                      AppRouteSetting.goBlog(context);
                    },
                  ),
                  SizedBox(
                    height: 310.r,
                    child: ListView.builder(
                      scrollDirection: Axis.horizontal,
                      padding: EdgeInsets.all(16.r),
                      shrinkWrap: true,
                      itemCount: state.blogs.length,
                      itemBuilder: (context, index) {
                        return Padding(
                          padding: EdgeInsets.only(right: 8.r),
                          child: BlogItem(blog: state.blogs[index]),
                        );
                      },
                    ),
                  ),
                ],
              )
            : const SizedBox.shrink();
      },
    );
  }
}
