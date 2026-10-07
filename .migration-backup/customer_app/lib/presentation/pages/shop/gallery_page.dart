import 'package:demand/domain/model/models.dart';
import 'package:flutter/material.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';

import '../../components/custom_image.dart';
import '../../style/style.dart';

class GalleryPage extends StatefulWidget {
  final List<Galleries> list;
  final int index;

  const GalleryPage._(this.list, this.index);

  static Route route(
    BuildContext context, {
    required List<Galleries> list,
    int index = 0,
  }) {
    return MaterialPageRoute(builder: (_) => GalleryPage._(list, index));
  }

  @override
  State<GalleryPage> createState() => _GalleryPageState();
}

class _GalleryPageState extends State<GalleryPage> {
  late PageController controller;
  ValueNotifier<int> currentPage = ValueNotifier(0);

  @override
  void initState() {
    super.initState();
    controller = PageController(initialPage: widget.index);
    currentPage = ValueNotifier(widget.index);
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        leading: GestureDetector(
          onTap: () => Navigator.pop(context),
          child: const Padding(
            padding: EdgeInsets.all(10),
            child: Icon(Icons.close, size: 30),
          ),
        ),
        actions: [
          ValueListenableBuilder(
            valueListenable: currentPage,
            builder: (context, value, child) {
              return Container(
                width: 60.r,
                height: 20.r,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: CustomStyle.black60,
                  borderRadius: BorderRadius.circular(6.r),
                ),
                child: Text(
                  '${value + 1} / ${widget.list.length}',
                  style: CustomStyle.interNoSemi(color: Colors.white, size: 12),
                ),
              );
            },
          ),
        ],
      ),
      body: SizedBox(
        width: double.infinity,
        height: double.infinity,
        child: PageView.builder(
          controller: controller,
          itemCount: widget.list.length,
          onPageChanged: (index) {
            currentPage.value = index;
          },
          itemBuilder: (_, i) {
            return Hero(
              tag: "image:$i",
              child: Builder(
                builder: (context) {
                  return CustomImage(
                    imageUrl: widget.list[i].path,
                    width: double.infinity,
                    fit: BoxFit.fitWidth,
                    enabledZoom: true,
                  );
                },
              ),
            );
          },
        ),
      ),
    );
  }
}
