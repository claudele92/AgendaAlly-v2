import 'package:dio/dio.dart';
import 'package:dio/io.dart';
import 'package:demand/app_constants.dart';

import 'token_interceptor.dart';
import 'safe_http_diagnostics.dart';
import '../session/session_coordinator.dart';

class HttpService {
  HttpService({this.session, this.adapter, this.diagnostics});
  final SessionCoordinator? session;
  final HttpClientAdapter? adapter;
  final SafeHttpDiagnostics? diagnostics;

  Dio client({
    bool requireAuth = false,
    bool routing = false,
    bool forLogout = false,
  }) =>
      Dio(
          BaseOptions(
            baseUrl: routing
                ? AppConstants.routingBaseUrl
                : AppConstants.baseUrl,
            connectTimeout: const Duration(seconds: 30),
            receiveTimeout: const Duration(seconds: 30),
            sendTimeout: const Duration(seconds: 30),
            headers: {
              'Accept':
                  'application/json, application/geo+json, application/gpx+xml, img/png; charset=utf-8',
              'Content-type': 'application/json',
            },
          ),
        )
        ..httpClientAdapter = adapter ?? IOHttpClientAdapter()
        ..interceptors.add(
          TokenInterceptor(
            requireAuth: requireAuth,
            // Only the bounded, captured-bearer logout call can proceed when
            // local cleanup failed. It cannot return private Customer state.
            session: forLogout
                ? null
                : session ?? SessionCoordinator.currentInstance,
          ),
        )
        ..interceptors.add(diagnostics ?? SafeHttpDiagnostics());
}
