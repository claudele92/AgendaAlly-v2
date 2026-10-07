import 'package:dartz/dartz.dart';
import 'package:demand/domain/model/response/gallary_list_response.dart';
import 'package:demand/domain/model/response/gallery_upload_response.dart';
import 'package:demand/infrastructure/service/services.dart';

abstract class GalleryInterface {
  Future<Either<GalleryUploadResponse, dynamic>> uploadImage(
    String file,
    UploadType uploadType,
  );

  Future<Either<GalleryListUploadResponse, dynamic>> uploadMultipleImage(
    List files,
    UploadType uploadType,
  );
}
