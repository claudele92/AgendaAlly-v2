import 'package:flutter/material.dart';
import '../infrastructure/session/session_coordinator.dart';

/// No guest/customer routes are exposed while local identity cleanup is uncertain.
class SessionRecoveryPage extends StatelessWidget {
  const SessionRecoveryPage({super.key});
  @override
  Widget build(BuildContext context) => Scaffold(
    body: SafeArea(
      child: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Device sign-out could not finish. Customer access is blocked.',
            ),
            if (SessionCoordinator.instance.logoutMessage != null)
              Text(SessionCoordinator.instance.logoutMessage!),
            TextButton(
              onPressed: () async {
                final session = SessionCoordinator.instance;
                await session.invalidate();
                if (!session.cleanupFailed) {
                  session.logoutMessage =
                      'Device cleanup completed. Please sign in again.';
                  session.publishInvalidation();
                }
              },
              child: const Text('Retry device cleanup'),
            ),
          ],
        ),
      ),
    ),
  );
}

class SessionLogoutPendingPage extends StatelessWidget {
  const SessionLogoutPendingPage({super.key});
  @override
  Widget build(BuildContext context) => const Scaffold(
    body: SafeArea(
      child: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            CircularProgressIndicator(),
            Text('Device signed out. Finishing server sign-out.'),
          ],
        ),
      ),
    ),
  );
}

class SessionIdentityPendingPage extends StatelessWidget {
  const SessionIdentityPendingPage({super.key});
  @override
  Widget build(BuildContext context) => const Scaffold(
    body: SafeArea(
      child: Center(
        child: Text('An earlier identity request is finishing. Please wait.'),
      ),
    ),
  );
}
