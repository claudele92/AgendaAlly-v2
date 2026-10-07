import 'dart:convert';

List<List<StoryModel?>?>? storyModelFromJson(dynamic str) => str == null
    ? []
    : List<List<StoryModel?>?>.from(
        str.map(
          (x) => x == null
              ? []
              : List<StoryModel?>.from(x!.map((x) => StoryModel.fromJson(x))),
        ),
      );

String storyModelToJson(List<List<StoryModel?>?>? data) => json.encode(
  data == null
      ? []
      : List<dynamic>.from(
          data.map(
            (x) =>
                x == null ? [] : List<dynamic>.from(x.map((x) => x!.toJson())),
          ),
        ),
);

class StoryModel {
  StoryModel({
    this.shopId,
    this.logoImg,
    this.title,
    this.shopUuid,
    this.url,
    this.modelType,
    this.modelTitle,
    this.modelSlug,
    this.modelUuid,
    this.createdAt,
    this.updatedAt,
  });

  int? shopId;
  String? logoImg;
  String? title;
  String? shopUuid;
  String? url;
  String? modelType;
  String? modelTitle;
  String? modelSlug;
  dynamic modelUuid;
  DateTime? createdAt;
  DateTime? updatedAt;

  factory StoryModel.fromJson(Map<String, dynamic> json) {
    return StoryModel(
      shopId: json["shop_id"],
      logoImg: json["logo_img"],
      title: json["title"],
      shopUuid: json["shop_uuid"],
      url: json["url"],
      modelType: json["model_type"],
      modelTitle: json["model_title"],
      modelSlug: json["model_slug"],
      modelUuid: json["model_uuid"],
      createdAt: DateTime.tryParse(json["created_at"])?.toLocal(),
      updatedAt: DateTime.tryParse(json["updated_at"])?.toLocal(),
    );
  }

  Map<String, dynamic> toJson() => {
    "shop_id": shopId,
    "logo_img": logoImg,
    "title": title,
    "shop_uuid": shopUuid,
    "url": url,
    "model_type": modelType,
    "model_title": modelTitle,
    "model_slug": modelSlug,
    "model_uuid": modelUuid,
    "created_at": createdAt?.toIso8601String(),
    "updated_at": updatedAt?.toIso8601String(),
  };
}
