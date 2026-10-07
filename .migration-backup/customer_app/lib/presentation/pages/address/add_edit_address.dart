import 'package:demand/domain/model/request/add_user_address_model.dart';
import 'package:demand/presentation/route/app_route.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_screenutil/flutter_screenutil.dart';
import 'package:demand/application/address/address_bloc.dart';
import 'package:demand/domain/model/model/address_info.dart';
import 'package:demand/domain/model/model/location_model.dart';
import 'package:demand/domain/model/response/city_pagination_response.dart';
import 'package:demand/domain/model/response/user_address_response.dart';
import 'package:demand/infrastructure/service/services.dart';
import 'package:demand/infrastructure/local_storage/local_storage.dart';
import 'package:demand/presentation/components/components.dart';
import 'package:demand/presentation/style/style.dart';
import 'package:demand/presentation/style/theme/theme.dart';
import 'package:phone_text_field/phone_text_field.dart';

import 'package:demand/app_constants.dart';

class AddEditAddressPage extends StatefulWidget {
  final UserAddress? address;

  const AddEditAddressPage({super.key, this.address});

  @override
  State<AddEditAddressPage> createState() => _AddEditAddressPageState();
}

class _AddEditAddressPageState extends State<AddEditAddressPage> {
  final GlobalKey<FormState> form = GlobalKey<FormState>();
  late TextEditingController firstName;
  late TextEditingController lastName;
  late TextEditingController phone;
  late TextEditingController zipcode;
  late TextEditingController city;
  late TextEditingController homeNumber;
  late TextEditingController detail;
  late TextEditingController location;
  CityModel? cityModel;
  LocationModel? locationModel;

  @override
  void initState() {
    firstName = TextEditingController(
      text: widget.address?.firstname ?? LocalStorage.getUser().firstname,
    );
    lastName = TextEditingController(
      text: widget.address?.lastname ?? LocalStorage.getUser().lastname,
    );
    phone = TextEditingController(
      text: widget.address?.phone ?? LocalStorage.getUser().phone,
    );
    zipcode = TextEditingController(text: widget.address?.zipcode);
    city = TextEditingController(
      text:
          widget.address?.cityModel?.translation?.title ??
          LocalStorage.getAddress()?.city,
    );
    homeNumber = TextEditingController(text: widget.address?.streetHouseNumber);
    detail = TextEditingController(text: widget.address?.additionalDetails);
    location = TextEditingController(text: widget.address?.location?.address);
    cityModel =
        widget.address?.cityModel ??
        CityModel(id: LocalStorage.getAddress()?.cityId);
    locationModel = widget.address?.location;
    super.initState();
  }

  @override
  void dispose() {
    firstName.dispose();
    lastName.dispose();
    phone.dispose();
    zipcode.dispose();
    city.dispose();
    homeNumber.dispose();
    detail.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return CustomScaffold(
      body: (colors) => SafeArea(
        child: Column(
          children: [
            Text(
              AppHelpers.getTranslation(TrKeys.addAddress),
              style: CustomStyle.interNoSemi(color: colors.textBlack, size: 18),
            ),
            24.verticalSpace,
            _body(context, colors),
          ],
        ),
      ),
      floatingButtonLocation: FloatingActionButtonLocation.centerFloat,
      floatingButton: (colors) => _bottom(colors),
    );
  }

  Widget _body(BuildContext context, CustomColorSet colors) {
    return Expanded(
      child: SingleChildScrollView(
        child: Container(
          margin: MediaQuery.viewInsetsOf(context),
          child: Padding(
            padding: EdgeInsets.symmetric(horizontal: 16.r),
            child: Form(
              key: form,
              child: Column(
                children: [
                  CustomTextFormField(
                    validation: AppValidators.isNotEmptyValidator,
                    controller: firstName,
                    hint: AppHelpers.getTranslation(TrKeys.firstName),
                  ),
                  16.verticalSpace,
                  CustomTextFormField(
                    validation: AppValidators.isNotEmptyValidator,
                    controller: lastName,
                    hint: AppHelpers.getTranslation(TrKeys.lastName),
                  ),
                  16.verticalSpace,
                  _phoneTextFormField(colors),
                  16.verticalSpace,
                  CustomTextFormField(
                    validation: AppValidators.isNotEmptyValidator,
                    controller: location,
                    readOnly: true,
                    onTap: () async {
                      final data = await AppRoute.goMap(context);
                      if (data.runtimeType == AddressInfo) {
                        location.text = (data as AddressInfo).address ?? "";
                        locationModel = data.toLocation();
                        zipcode.text = data.zipcode ?? '';
                      }
                    },
                    hint: AppHelpers.getTranslation(TrKeys.locationInMap),
                  ),
                  16.verticalSpace,
                  Row(
                    children: [
                      Expanded(
                        child: CustomTextFormField(
                          controller: zipcode,
                          inputType: TextInputType.number,
                          hint: AppHelpers.getTranslation(TrKeys.zipCode),
                        ),
                      ),
                      16.horizontalSpace,
                      Expanded(
                        child: CustomTextFormField(
                          validation: AppValidators.isNotEmptyValidator,
                          readOnly: city.text.isNotEmpty,
                          controller: city,
                          hint: AppHelpers.getTranslation(TrKeys.city),
                        ),
                      ),
                    ],
                  ),
                  16.verticalSpace,
                  CustomTextFormField(
                    validation: AppValidators.isNotEmptyValidator,
                    controller: homeNumber,
                    hint: AppHelpers.getTranslation(TrKeys.homeNumber),
                  ),
                  16.verticalSpace,
                  CustomTextFormField(
                    controller: detail,
                    hint: AppHelpers.getTranslation(TrKeys.detail),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _bottom(CustomColorSet colors) {
    return Padding(
      padding: EdgeInsets.symmetric(horizontal: 16.r),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            children: [
              PopButton(colors: colors),
              24.horizontalSpace,
              BlocBuilder<AddressBloc, AddressState>(
                builder: (context, state) {
                  return Expanded(
                    child: CustomButton(
                      isLoading: state.isButtonLoading,
                      title: AppHelpers.getTranslation(
                        widget.address?.id == null ? TrKeys.save : TrKeys.edit,
                      ),
                      bgColor: colors.primary,
                      titleColor: colors.white,
                      onTap: () {
                        if (form.currentState?.validate() ?? false) {
                          if (widget.address?.id != null) {
                            context.read<AddressBloc>().add(
                              AddressEvent.editAddress(
                                context,
                                editUserAddress: AddUserAddressModel(
                                  firstName: firstName.text,
                                  lastName: lastName.text,
                                  phone: phone.text,
                                  zipcode: zipcode.text,
                                  city: cityModel?.id.toString(),
                                  homeNumber: homeNumber.text,
                                  detail: detail.text,
                                  locationModel: locationModel,
                                ),
                                addressId: widget.address?.id,
                                onSuccess: () {
                                  Navigator.pop(context);
                                },
                              ),
                            );
                            return;
                          }
                          context.read<AddressBloc>().add(
                            AddressEvent.addAddress(
                              context,
                              addUserAddress: AddUserAddressModel(
                                firstName: firstName.text,
                                lastName: lastName.text,
                                phone: phone.text,
                                zipcode: zipcode.text,
                                city: cityModel?.id.toString(),
                                homeNumber: homeNumber.text,
                                detail: detail.text,
                                locationModel: locationModel,
                              ),
                              onSuccess: () {
                                Navigator.pop(context);
                              },
                            ),
                          );
                        }
                      },
                    ),
                  );
                },
              ),
            ],
          ),
        ],
      ),
    );
  }

  PhoneTextField _phoneTextFormField(CustomColorSet colors) {
    return PhoneTextField(
      initialValue: phone.text,
      initialCountryCode:
          LocalStorage.getAddress()?.countryCode ??
          LocalStorage.getAddress()?.countryCode ??
          AppConstants.countryCodeISO,
      // LocalStorage.getAddress()?.countryCode?.toUpperCase(),
      textStyle: CustomStyle.interNormal(color: colors.textBlack),
      decoration: InputDecoration(
        hintText: AppHelpers.getTranslation(TrKeys.phoneNumber),
        hintStyle: CustomStyle.interNormal(
          size: 14.sp,
          color: CustomStyle.textHint,
        ),
        floatingLabelBehavior: FloatingLabelBehavior.always,
        fillColor: CustomStyle.black,
        filled: false,
        enabledBorder: OutlineInputBorder(
          borderSide: BorderSide.merge(
            const BorderSide(color: CustomStyle.icon),
            const BorderSide(color: CustomStyle.icon),
          ),
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        errorBorder: OutlineInputBorder(
          borderSide: BorderSide.merge(
            const BorderSide(color: CustomStyle.icon),
            const BorderSide(color: CustomStyle.icon),
          ),
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        border: OutlineInputBorder(
          borderSide: BorderSide.merge(
            const BorderSide(color: CustomStyle.icon),
            const BorderSide(color: CustomStyle.icon),
          ),
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderSide: BorderSide.merge(
            const BorderSide(color: CustomStyle.icon),
            const BorderSide(color: CustomStyle.icon),
          ),
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        disabledBorder: OutlineInputBorder(
          borderSide: BorderSide.merge(
            const BorderSide(color: CustomStyle.icon),
            const BorderSide(color: CustomStyle.icon),
          ),
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
        focusedBorder: OutlineInputBorder(
          borderSide: BorderSide.merge(
            const BorderSide(color: CustomStyle.icon),
            const BorderSide(color: CustomStyle.icon),
          ),
          borderRadius: BorderRadius.circular(AppConstants.radius.r),
        ),
      ),
      onChanged: (s) {
        phone.text = s.completeNumber;
      },
    );
  }
}
