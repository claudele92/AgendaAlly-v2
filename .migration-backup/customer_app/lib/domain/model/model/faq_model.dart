class FaqModel {
  int? id;
  String? slug;
  int? serviceId;
  String? type;
  bool? active;
  DateTime? createdAt;
  DateTime? updatedAt;
  FaqTranslation? translation;

  FaqModel({
    this.id,
    this.slug,
    this.serviceId,
    this.type,
    this.active,
    this.createdAt,
    this.updatedAt,
    this.translation,
  });

  FaqModel copyWith({
    int? id,
    String? slug,
    int? serviceId,
    String? type,
    bool? active,
    DateTime? createdAt,
    DateTime? updatedAt,
    FaqTranslation? translation,
  }) => FaqModel(
    id: id ?? this.id,
    slug: slug ?? this.slug,
    serviceId: serviceId ?? this.serviceId,
    type: type ?? this.type,
    active: active ?? this.active,
    createdAt: createdAt ?? this.createdAt,
    updatedAt: updatedAt ?? this.updatedAt,
    translation: translation ?? this.translation,
  );

  factory FaqModel.fromJson(Map<String, dynamic> json) => FaqModel(
    id: json["id"],
    slug: json["slug"],
    serviceId: json["service_id"],
    type: json["type"],
    active: json["active"],
    createdAt: json["created_at"] == null
        ? null
        : DateTime.parse(json["created_at"]),
    updatedAt: json["updated_at"] == null
        ? null
        : DateTime.parse(json["updated_at"]),
    translation: json["translation"] == null
        ? null
        : FaqTranslation.fromJson(json["translation"]),
  );

  Map<String, dynamic> toJson() => {
    "id": id,
    "slug": slug,
    "service_id": serviceId,
    "type": type,
    "active": active,
    "created_at": createdAt?.toIso8601String(),
    "updated_at": updatedAt?.toIso8601String(),
    "translation": translation?.toJson(),
  };
}

class FaqTranslation {
  int? id;
  String? locale;
  String? question;
  String? answer;

  FaqTranslation({this.id, this.locale, this.question, this.answer});

  FaqTranslation copyWith({
    int? id,
    String? locale,
    String? question,
    String? answer,
  }) => FaqTranslation(
    id: id ?? this.id,
    locale: locale ?? this.locale,
    question: question ?? this.question,
    answer: answer ?? this.answer,
  );

  factory FaqTranslation.fromJson(Map<String, dynamic> json) => FaqTranslation(
    id: json["id"],
    locale: json["locale"],
    question: json["question"],
    answer: json["answer"],
  );

  Map<String, dynamic> toJson() => {
    "id": id,
    "locale": locale,
    "question": question,
    "answer": answer,
  };
}
