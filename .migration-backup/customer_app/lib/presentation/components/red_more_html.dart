import 'package:flutter/material.dart';
import 'package:flutter_html/flutter_html.dart';
import 'package:demand/presentation/style/theme/theme.dart';

class ExpandableText extends StatefulWidget {
  final String? text;
  final int trimLines;
  final CustomColorSet colors;

  const ExpandableText({
    super.key,
    required this.text,
    this.trimLines = 2,
    required this.colors,
  });

  @override
  ExpandableTextState createState() => ExpandableTextState();
}

class ExpandableTextState extends State<ExpandableText> {
  // bool _readMore = true;

  // void _onTapLink() {
  //   setState(() => _readMore = !_readMore);
  // }

  @override
  Widget build(BuildContext context) {
    if ((widget.text?.length ?? 0) > 200) {
      return Container(
        color: Colors.red,
        height: 100,
        child: Html(
          data:
              '${widget.text!.substring(0, widget.text!.substring(0, 200).lastIndexOf('.') + 1)}<button onclick="">Read More</button>',
          style: {
            "body": Style(
              color: widget.colors.textBlack,
              fontSize: FontSize(16),
            ),
          },
        ),
      );
    }
    return Html(
      data: widget.text ?? "",
      style: {
        "body": Style(color: widget.colors.textBlack, fontSize: FontSize(16)),
      },
    );
  }
}
