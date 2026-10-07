import 'package:flutter/widgets.dart';
import '../infrastructure/session/session_coordinator.dart';

/// Replaces the Navigator and all account-scoped route providers on invalidation.
/// Theme/language settings above this boundary are deliberately retained.
class SessionBoundary extends StatelessWidget {
  const SessionBoundary({super.key, required this.builder});
  final Widget Function(BuildContext context, int epoch) builder;

  @override
  Widget build(BuildContext context) => StreamBuilder<int>(
    stream: SessionCoordinator.instance.invalidations,
    initialData: SessionCoordinator.instance.generation,
    builder: (context, snapshot) => KeyedSubtree(
      key: ValueKey(snapshot.data),
      child: builder(context, snapshot.data ?? 0),
    ),
  );
}
