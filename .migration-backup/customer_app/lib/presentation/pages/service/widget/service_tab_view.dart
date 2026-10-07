import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/service/service_bloc.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:pull_to_refresh/pull_to_refresh.dart';

class ServiceTabView extends StatefulWidget {
  final CustomColorSet colors;
  final int? categoryId;
  final int? shopId;
  final int? masterId;

  const ServiceTabView({
    super.key,
    required this.colors,
    this.categoryId,
    this.shopId,
    this.masterId,
  });

  @override
  State<ServiceTabView> createState() => _ServiceTabViewState();
}

class _ServiceTabViewState extends State<ServiceTabView> {
  late RefreshController controller;

  @override
  void initState() {
    controller = RefreshController();
    context.read<ServiceBloc>().add(
      ServiceEvent.fetchServices(
        context,
        isRefresh: true,
        categoryId: widget.categoryId,
        shopId: widget.shopId,
        masterId: widget.masterId,
      ),
    );
    super.initState();
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return BlocBuilder<ServiceBloc, ServiceState>(
      builder: (context, state) {
        return state.isLoading
            ? const Loading()
            : SmartRefresher(
                controller: controller,
                enablePullUp: true,
                onRefresh: () {
                  context.read<ServiceBloc>().add(
                    ServiceEvent.fetchServices(
                      context,
                      isRefresh: true,
                      categoryId: widget.categoryId,
                      shopId: widget.shopId,
                      masterId: widget.masterId,
                      controller: controller,
                    ),
                  );
                  controller.resetNoData();
                },
                onLoading: () {
                  context.read<ServiceBloc>().add(
                    ServiceEvent.fetchServices(
                      context,
                      categoryId: widget.categoryId,
                      shopId: widget.shopId,
                      masterId: widget.masterId,
                      controller: controller,
                    ),
                  );
                  controller.resetNoData();
                },
                child: ListView.builder(
                  shrinkWrap: true,
                  padding: EdgeInsets.only(
                    right: 16.r,
                    left: 16.r,
                    bottom: 100.r,
                  ),
                  itemCount: state.services.length,
                  itemBuilder: (context, index) {
                    return ServiceItem(
                      booked: state.selectServices
                          .map((e) => e.id)
                          .contains(state.services[index].id),
                      bookButton: true,
                      extras:
                          state.selectServices
                              .map((e) => e.id)
                              .contains(state.services[index].id)
                          ? state.selectServices
                                .firstWhere(
                                  (e) => e.id == state.services[index].id,
                                )
                                .selectExtras
                          : null,
                      colors: widget.colors,
                      service: state.services[index],
                    );
                  },
                ),
              );
      },
    );
  }
}
