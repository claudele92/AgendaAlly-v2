import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

/// Allowlisted metadata only: never URL, headers, data, exceptions or bodies.
class SafeHttpDiagnostics extends Interceptor {
  SafeHttpDiagnostics({bool? enabled, void Function(String)? sink})
    : enabled =
          kDebugMode &&
          (enabled ?? const bool.fromEnvironment('HTTP_DIAGNOSTICS')),
      sink = sink ?? debugPrint;

  final bool enabled;
  final void Function(String) sink;

  void _record(String phase, String method, int? status) {
    if (!enabled) return;
    final verb =
        const {
          'GET',
          'POST',
          'PUT',
          'PATCH',
          'DELETE',
          'HEAD',
          'OPTIONS',
        }.contains(method)
        ? method
        : 'OTHER';
    final code = status != null && status >= 100 && status <= 599
        ? ' $status'
        : '';
    sink('HTTP $phase $verb$code');
  }

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    _record('request', options.method, null);
    handler.next(options);
  }

  @override
  void onResponse(Response response, ResponseInterceptorHandler handler) {
    _record('response', response.requestOptions.method, response.statusCode);
    handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    _record('error', err.requestOptions.method, err.response?.statusCode);
    handler.next(err);
  }
}

/// Legacy repository "$e" diagnostics cannot stringify a credential-bearing
/// Dio URL/message. Preserve response/status for existing error handling.
class ConfidentialHttpException extends DioException {
  ConfidentialHttpException(DioException error)
    : super(
        requestOptions: error.requestOptions,
        response: error.response,
        type: error.type,
        message: 'HTTP request failed',
      );

  @override
  String toString() =>
      'HTTP request failed (${type.name}, ${response?.statusCode ?? 0})';
}
