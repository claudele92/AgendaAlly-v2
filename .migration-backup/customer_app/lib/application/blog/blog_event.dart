part of 'blog_bloc.dart';

@freezed
abstract class BlogEvent with _$BlogEvent {
  const factory BlogEvent.fetchBlog(
    BuildContext context, {
    bool? isRefresh,
    RefreshController? controller,
  }) = FetchBlog;

  const factory BlogEvent.fetchBlogById(
    BuildContext context, {
    required int id,
    BlogData? blog,
  }) = FetchBlogById;
}
