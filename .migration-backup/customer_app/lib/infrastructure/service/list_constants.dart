import 'dart:ui';
import 'package:demand/presentation/style/style.dart';
import 'package:flutter_remix/flutter_remix.dart';
import 'package:demand/infrastructure/service/services.dart';

import '../../presentation/app_assets.dart';

abstract class ListConstants {
  ListConstants._();

  /// social sign-in
  static const socialSignIn = [
    FlutterRemix.google_fill,
    FlutterRemix.facebook_fill,
    FlutterRemix.apple_fill,
  ];

  static const socialSignInAndroid = [
    FlutterRemix.google_fill,
    FlutterRemix.facebook_fill,
  ];

  static List<String> genderList = [TrKeys.male, TrKeys.female, TrKeys.all];

  static const filterLayouts = [
    LayoutType.twoH,
    LayoutType.three,
    LayoutType.twoV,
    LayoutType.one,
    LayoutType.newUi,
  ];

  static const payLater = ["progress", "canceled", "rejected"];

  static const String newOrder = 'new_order';
  static const String newParcelOrder = 'new_parcel_order';
  static const String newUserByReferral = 'new_user_by_referral';
  static const String statusChanged = 'status_changed';
  static const String newsPublish = 'news_publish';
  static const String addCashback = 'add_cashback';
  static const String shopApproved = 'shop_approved';
  static const String walletTopUp = 'wallet_top_up';
  static const String bookingStatusChanged = 'booking_status_changed';

  static const List infoImage = [
    Assets.imagesSave,
    Assets.imagesDelivery,
    Assets.imagesFast,
    Assets.imagesSet,
  ];

  static const List imageTypes = [
    '.png',
    '.jpg',
    '.jpeg',
    '.webp',
    '.svg',
    '.jfif',
    '.gif',
  ];

  static const Map socialIcon = {
    "facebook": FlutterRemix.facebook_fill,
    "instagram": FlutterRemix.instagram_fill,
    "telegram": FlutterRemix.telegram_fill,
    "youtube": FlutterRemix.youtube_fill,
    "linkedin": FlutterRemix.linkedin_fill,
    "snapchat": FlutterRemix.snapchat_fill,
    "wechat": FlutterRemix.wechat_fill,
    "whatsapp": FlutterRemix.whatsapp_fill,
    "twitch": FlutterRemix.twitch_fill,
    "discord": FlutterRemix.discord_fill,
    "pinterest": FlutterRemix.pinterest_fill,
    "steam": FlutterRemix.steam_fill,
    "spotify": FlutterRemix.spotify_fill,
    "reddit": FlutterRemix.reddit_fill,
    "skype": FlutterRemix.skype_fill,
    "twitter": FlutterRemix.twitter_fill,
  };

  static const List infoTitle = [
    TrKeys.saveTime,
    TrKeys.deliveryRestriction,
    TrKeys.fast,
    TrKeys.set,
  ];

  static List<Color> adsColor = [
    CustomStyle.seeAllColor,
    CustomStyle.success,
    CustomStyle.starColor,
    CustomStyle.primary,
  ];

  static const List<Color> serviceColors = [
    CustomStyle.service1,
    CustomStyle.service2,
    CustomStyle.service3,
    CustomStyle.service4,
    CustomStyle.service5,
    CustomStyle.service6,
    CustomStyle.service7,
    CustomStyle.service8,
    CustomStyle.service9,
    CustomStyle.service10,
    CustomStyle.service11,
    CustomStyle.service12,
  ];

  static const List<String> listOrderStatus = [
    TrKeys.canceled,
    TrKeys.delivered,
    TrKeys.pause,
    TrKeys.onAWay,
    TrKeys.ready,
    TrKeys.accepted,
    TrKeys.newKey,
  ];

  // static const List sort = [
  //   TrKeys.recommended,
  //   TrKeys.highestRated,
  //   TrKeys.lowestPrice,
  //   TrKeys.highestPrice,
  //   TrKeys.discount,
  // ];

  static const List reviewType = [
    TrKeys.cleanliness,
    TrKeys.masters,
    TrKeys.location,
    TrKeys.price,
    TrKeys.interior,
    TrKeys.serviceQuality,
    TrKeys.communication,
    TrKeys.equipment,
  ];

  static const List formType = [
    TrKeys.multipleChoice,
    TrKeys.shortAnswer,
    TrKeys.longAnswer,
    TrKeys.singleAnswer,
    TrKeys.dropDown,
    TrKeys.yesOrNo,
    TrKeys.descriptionText,
  ];
}
