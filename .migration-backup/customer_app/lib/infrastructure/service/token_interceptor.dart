import 'package:dio/dio.dart';
import '../session/session_coordinator.dart';
import 'safe_http_diagnostics.dart';

class TokenInterceptor extends Interceptor {
  final bool requireAuth;
  final SessionCoordinator? session;
  final int generation;

  TokenInterceptor({required this.requireAuth, required this.session})
    : generation = session?.generation ?? 0;

  @override
  void onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    if ((session != null && !session!.current(generation)) ||
        (requireAuth && (session?.token.isEmpty ?? true))) {
      handler.reject(
        DioException(
          requestOptions: options,
          type: DioExceptionType.cancel,
          message: 'Session is no longer current',
        ),
      );
      return;
    }
    final String token = session?.token ?? '';
    if (token.isNotEmpty && requireAuth) {
      options.headers.addAll({'Authorization': 'Bearer $token'});
    }
    handler.next(options);
  }

  @override
  void onResponse(Response response, ResponseInterceptorHandler handler) {
    if (session != null && !session!.current(generation)) {
      handler.reject(
        DioException(
          requestOptions: response.requestOptions,
          type: DioExceptionType.cancel,
          message: 'Previous session response discarded',
        ),
      );
      return;
    }
    handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    if (requireAuth &&
        err.response?.statusCode == 401 &&
        (session?.current(generation) ?? false)) {
      await session!.invalidate();
    }
    // A 403 is an authorization failure, not a logout instruction.
    handler.next(ConfidentialHttpException(err));
  }
}
