import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/blog/blog_bloc.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

class BlogListPage extends StatefulWidget {
  const BlogListPage({super.key});

  @override
  State<BlogListPage> createState() => _BlogListPageState();
}

class _BlogListPageState extends State<BlogListPage> {
  late RefreshController blogRefresh;

  @override
  void initState() {
    blogRefresh = RefreshController();
    super.initState();
  }

  @override
  void dispose() {
    blogRefresh.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => SafeArea(
        bottom: false,
        child: Column(
          children: [
            Text(
              AppHelpers.getTranslation(TrKeys.blog),
              style: CustomStyle.interNoSemi(color: colors.textBlack, size: 18),
            ),
            24.verticalSpace,
            BlocBuilder<BlogBloc, BlogState>(
              builder: (context, state) {
                return Expanded(
                  child: state.isLoadingBlog
                      ? const Loading()
                      : SmartRefresher(
                          controller: blogRefresh,
                          enablePullUp: true,
                          onRefresh: () {
                            context.read<BlogBloc>().add(
                              BlogEvent.fetchBlog(
                                context,
                                isRefresh: true,
                                controller: blogRefresh,
                              ),
                            );
                            blogRefresh.resetNoData();
                          },
                          onLoading: () {
                            context.read<BlogBloc>().add(
                              BlogEvent.fetchBlog(
                                context,
                                controller: blogRefresh,
                              ),
                            );
                          },
                          child: ListView.builder(
                            padding: EdgeInsets.symmetric(horizontal: 16.r),
                            shrinkWrap: true,
                            itemCount: state.blogs.length,
                            itemBuilder: (context, index) {
                              return BlogItem(
                                isHomePage: false,
                                blog: state.blogs[index],
                              );
                            },
                          ),
                        ),
                );
              },
            ),
          ],
        ),
      ),
      floatingButtonLocation: FloatingActionButtonLocation.startFloat,
      floatingButton: (colors) => PopButton(colors: colors),
    );
  }
}
