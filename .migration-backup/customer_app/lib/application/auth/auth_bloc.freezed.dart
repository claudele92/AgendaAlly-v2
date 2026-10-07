// GENERATED CODE - DO NOT MODIFY BY HAND
// coverage:ignore-file
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'auth_bloc.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

// dart format off
T _$identity<T>(T value) => value;
/// @nodoc
mixin _$AuthEvent {





@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AuthEvent);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'AuthEvent()';
}


}

/// @nodoc
class $AuthEventCopyWith<$Res>  {
$AuthEventCopyWith(AuthEvent _, $Res Function(AuthEvent) __);
}


/// Adds pattern-matching-related methods to [AuthEvent].
extension AuthEventPatterns on AuthEvent {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>({TResult Function( Login value)?  login,TResult Function( SignUp value)?  signUp,TResult Function( SignUpWithData value)?  signUpWithData,TResult Function( SignUpEmail value)?  signUpEmail,TResult Function( SignUpPhone value)?  signUpPhone,TResult Function( ForgotPassword value)?  forgotPassword,TResult Function( SocialSignIn value)?  socialSignIn,TResult Function( SetVerificationId value)?  setVerificationId,TResult Function( ShowPassword value)?  showPassword,TResult Function( ShowConfirmPassword value)?  showConfirmPassword,TResult Function( SwitchScreen value)?  switchScreen,TResult Function( CheckVerify value)?  checkVerify,TResult Function( CheckVerifyEmail value)?  checkVerifyEmail,TResult Function( CheckPhone value)?  checkPhone,TResult Function( ForgotPasswordConfirm value)?  forgotPasswordConfirm,TResult Function( ForgotPasswordAfter value)?  forgotPasswordAfter,TResult Function( LoadingChange value)?  loadingChange,TResult Function( AcceptTerm value)?  acceptTerm,required TResult orElse(),}){
final _that = this;
switch (_that) {
case Login() when login != null:
return login(_that);case SignUp() when signUp != null:
return signUp(_that);case SignUpWithData() when signUpWithData != null:
return signUpWithData(_that);case SignUpEmail() when signUpEmail != null:
return signUpEmail(_that);case SignUpPhone() when signUpPhone != null:
return signUpPhone(_that);case ForgotPassword() when forgotPassword != null:
return forgotPassword(_that);case SocialSignIn() when socialSignIn != null:
return socialSignIn(_that);case SetVerificationId() when setVerificationId != null:
return setVerificationId(_that);case ShowPassword() when showPassword != null:
return showPassword(_that);case ShowConfirmPassword() when showConfirmPassword != null:
return showConfirmPassword(_that);case SwitchScreen() when switchScreen != null:
return switchScreen(_that);case CheckVerify() when checkVerify != null:
return checkVerify(_that);case CheckVerifyEmail() when checkVerifyEmail != null:
return checkVerifyEmail(_that);case CheckPhone() when checkPhone != null:
return checkPhone(_that);case ForgotPasswordConfirm() when forgotPasswordConfirm != null:
return forgotPasswordConfirm(_that);case ForgotPasswordAfter() when forgotPasswordAfter != null:
return forgotPasswordAfter(_that);case LoadingChange() when loadingChange != null:
return loadingChange(_that);case AcceptTerm() when acceptTerm != null:
return acceptTerm(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>({required TResult Function( Login value)  login,required TResult Function( SignUp value)  signUp,required TResult Function( SignUpWithData value)  signUpWithData,required TResult Function( SignUpEmail value)  signUpEmail,required TResult Function( SignUpPhone value)  signUpPhone,required TResult Function( ForgotPassword value)  forgotPassword,required TResult Function( SocialSignIn value)  socialSignIn,required TResult Function( SetVerificationId value)  setVerificationId,required TResult Function( ShowPassword value)  showPassword,required TResult Function( ShowConfirmPassword value)  showConfirmPassword,required TResult Function( SwitchScreen value)  switchScreen,required TResult Function( CheckVerify value)  checkVerify,required TResult Function( CheckVerifyEmail value)  checkVerifyEmail,required TResult Function( CheckPhone value)  checkPhone,required TResult Function( ForgotPasswordConfirm value)  forgotPasswordConfirm,required TResult Function( ForgotPasswordAfter value)  forgotPasswordAfter,required TResult Function( LoadingChange value)  loadingChange,required TResult Function( AcceptTerm value)  acceptTerm,}){
final _that = this;
switch (_that) {
case Login():
return login(_that);case SignUp():
return signUp(_that);case SignUpWithData():
return signUpWithData(_that);case SignUpEmail():
return signUpEmail(_that);case SignUpPhone():
return signUpPhone(_that);case ForgotPassword():
return forgotPassword(_that);case SocialSignIn():
return socialSignIn(_that);case SetVerificationId():
return setVerificationId(_that);case ShowPassword():
return showPassword(_that);case ShowConfirmPassword():
return showConfirmPassword(_that);case SwitchScreen():
return switchScreen(_that);case CheckVerify():
return checkVerify(_that);case CheckVerifyEmail():
return checkVerifyEmail(_that);case CheckPhone():
return checkPhone(_that);case ForgotPasswordConfirm():
return forgotPasswordConfirm(_that);case ForgotPasswordAfter():
return forgotPasswordAfter(_that);case LoadingChange():
return loadingChange(_that);case AcceptTerm():
return acceptTerm(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>({TResult? Function( Login value)?  login,TResult? Function( SignUp value)?  signUp,TResult? Function( SignUpWithData value)?  signUpWithData,TResult? Function( SignUpEmail value)?  signUpEmail,TResult? Function( SignUpPhone value)?  signUpPhone,TResult? Function( ForgotPassword value)?  forgotPassword,TResult? Function( SocialSignIn value)?  socialSignIn,TResult? Function( SetVerificationId value)?  setVerificationId,TResult? Function( ShowPassword value)?  showPassword,TResult? Function( ShowConfirmPassword value)?  showConfirmPassword,TResult? Function( SwitchScreen value)?  switchScreen,TResult? Function( CheckVerify value)?  checkVerify,TResult? Function( CheckVerifyEmail value)?  checkVerifyEmail,TResult? Function( CheckPhone value)?  checkPhone,TResult? Function( ForgotPasswordConfirm value)?  forgotPasswordConfirm,TResult? Function( ForgotPasswordAfter value)?  forgotPasswordAfter,TResult? Function( LoadingChange value)?  loadingChange,TResult? Function( AcceptTerm value)?  acceptTerm,}){
final _that = this;
switch (_that) {
case Login() when login != null:
return login(_that);case SignUp() when signUp != null:
return signUp(_that);case SignUpWithData() when signUpWithData != null:
return signUpWithData(_that);case SignUpEmail() when signUpEmail != null:
return signUpEmail(_that);case SignUpPhone() when signUpPhone != null:
return signUpPhone(_that);case ForgotPassword() when forgotPassword != null:
return forgotPassword(_that);case SocialSignIn() when socialSignIn != null:
return socialSignIn(_that);case SetVerificationId() when setVerificationId != null:
return setVerificationId(_that);case ShowPassword() when showPassword != null:
return showPassword(_that);case ShowConfirmPassword() when showConfirmPassword != null:
return showConfirmPassword(_that);case SwitchScreen() when switchScreen != null:
return switchScreen(_that);case CheckVerify() when checkVerify != null:
return checkVerify(_that);case CheckVerifyEmail() when checkVerifyEmail != null:
return checkVerifyEmail(_that);case CheckPhone() when checkPhone != null:
return checkPhone(_that);case ForgotPasswordConfirm() when forgotPasswordConfirm != null:
return forgotPasswordConfirm(_that);case ForgotPasswordAfter() when forgotPasswordAfter != null:
return forgotPasswordAfter(_that);case LoadingChange() when loadingChange != null:
return loadingChange(_that);case AcceptTerm() when acceptTerm != null:
return acceptTerm(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>({TResult Function( BuildContext context,  String phone,  String email,  String password,  VoidCallback? onSuccess)?  login,TResult Function( BuildContext context,  String firstname,  String lastname,  String email,  String phone,  String password,  String referral,  VoidCallback? onSuccess)?  signUp,TResult Function( BuildContext context,  String firstname,  String lastname,  String phone,  String email,  String password,  String referral,  VoidCallback? onSuccess)?  signUpWithData,TResult Function( BuildContext context,  String email)?  signUpEmail,TResult Function( BuildContext context,  String phone)?  signUpPhone,TResult Function( BuildContext context,  String phone,  VoidCallback? onSuccess)?  forgotPassword,TResult Function( BuildContext context,  IconData type,  VoidCallback? onSuccess)?  socialSignIn,TResult Function( String id)?  setVerificationId,TResult Function()?  showPassword,TResult Function()?  showConfirmPassword,TResult Function( AuthType type)?  switchScreen,TResult Function( BuildContext context,  String verifyId,  String code,  String phone,  bool? editPhone)?  checkVerify,TResult Function( BuildContext context,  String code)?  checkVerifyEmail,TResult Function( BuildContext context,  String phone,  VoidCallback? onSuccess)?  checkPhone,TResult Function( BuildContext context,  String email,  String code,  VoidCallback? onSuccess)?  forgotPasswordConfirm,TResult Function( BuildContext context,  String phone,  String password,  VoidCallback? onSuccess)?  forgotPasswordAfter,TResult Function()?  loadingChange,TResult Function()?  acceptTerm,required TResult orElse(),}) {final _that = this;
switch (_that) {
case Login() when login != null:
return login(_that.context,_that.phone,_that.email,_that.password,_that.onSuccess);case SignUp() when signUp != null:
return signUp(_that.context,_that.firstname,_that.lastname,_that.email,_that.phone,_that.password,_that.referral,_that.onSuccess);case SignUpWithData() when signUpWithData != null:
return signUpWithData(_that.context,_that.firstname,_that.lastname,_that.phone,_that.email,_that.password,_that.referral,_that.onSuccess);case SignUpEmail() when signUpEmail != null:
return signUpEmail(_that.context,_that.email);case SignUpPhone() when signUpPhone != null:
return signUpPhone(_that.context,_that.phone);case ForgotPassword() when forgotPassword != null:
return forgotPassword(_that.context,_that.phone,_that.onSuccess);case SocialSignIn() when socialSignIn != null:
return socialSignIn(_that.context,_that.type,_that.onSuccess);case SetVerificationId() when setVerificationId != null:
return setVerificationId(_that.id);case ShowPassword() when showPassword != null:
return showPassword();case ShowConfirmPassword() when showConfirmPassword != null:
return showConfirmPassword();case SwitchScreen() when switchScreen != null:
return switchScreen(_that.type);case CheckVerify() when checkVerify != null:
return checkVerify(_that.context,_that.verifyId,_that.code,_that.phone,_that.editPhone);case CheckVerifyEmail() when checkVerifyEmail != null:
return checkVerifyEmail(_that.context,_that.code);case CheckPhone() when checkPhone != null:
return checkPhone(_that.context,_that.phone,_that.onSuccess);case ForgotPasswordConfirm() when forgotPasswordConfirm != null:
return forgotPasswordConfirm(_that.context,_that.email,_that.code,_that.onSuccess);case ForgotPasswordAfter() when forgotPasswordAfter != null:
return forgotPasswordAfter(_that.context,_that.phone,_that.password,_that.onSuccess);case LoadingChange() when loadingChange != null:
return loadingChange();case AcceptTerm() when acceptTerm != null:
return acceptTerm();case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>({required TResult Function( BuildContext context,  String phone,  String email,  String password,  VoidCallback? onSuccess)  login,required TResult Function( BuildContext context,  String firstname,  String lastname,  String email,  String phone,  String password,  String referral,  VoidCallback? onSuccess)  signUp,required TResult Function( BuildContext context,  String firstname,  String lastname,  String phone,  String email,  String password,  String referral,  VoidCallback? onSuccess)  signUpWithData,required TResult Function( BuildContext context,  String email)  signUpEmail,required TResult Function( BuildContext context,  String phone)  signUpPhone,required TResult Function( BuildContext context,  String phone,  VoidCallback? onSuccess)  forgotPassword,required TResult Function( BuildContext context,  IconData type,  VoidCallback? onSuccess)  socialSignIn,required TResult Function( String id)  setVerificationId,required TResult Function()  showPassword,required TResult Function()  showConfirmPassword,required TResult Function( AuthType type)  switchScreen,required TResult Function( BuildContext context,  String verifyId,  String code,  String phone,  bool? editPhone)  checkVerify,required TResult Function( BuildContext context,  String code)  checkVerifyEmail,required TResult Function( BuildContext context,  String phone,  VoidCallback? onSuccess)  checkPhone,required TResult Function( BuildContext context,  String email,  String code,  VoidCallback? onSuccess)  forgotPasswordConfirm,required TResult Function( BuildContext context,  String phone,  String password,  VoidCallback? onSuccess)  forgotPasswordAfter,required TResult Function()  loadingChange,required TResult Function()  acceptTerm,}) {final _that = this;
switch (_that) {
case Login():
return login(_that.context,_that.phone,_that.email,_that.password,_that.onSuccess);case SignUp():
return signUp(_that.context,_that.firstname,_that.lastname,_that.email,_that.phone,_that.password,_that.referral,_that.onSuccess);case SignUpWithData():
return signUpWithData(_that.context,_that.firstname,_that.lastname,_that.phone,_that.email,_that.password,_that.referral,_that.onSuccess);case SignUpEmail():
return signUpEmail(_that.context,_that.email);case SignUpPhone():
return signUpPhone(_that.context,_that.phone);case ForgotPassword():
return forgotPassword(_that.context,_that.phone,_that.onSuccess);case SocialSignIn():
return socialSignIn(_that.context,_that.type,_that.onSuccess);case SetVerificationId():
return setVerificationId(_that.id);case ShowPassword():
return showPassword();case ShowConfirmPassword():
return showConfirmPassword();case SwitchScreen():
return switchScreen(_that.type);case CheckVerify():
return checkVerify(_that.context,_that.verifyId,_that.code,_that.phone,_that.editPhone);case CheckVerifyEmail():
return checkVerifyEmail(_that.context,_that.code);case CheckPhone():
return checkPhone(_that.context,_that.phone,_that.onSuccess);case ForgotPasswordConfirm():
return forgotPasswordConfirm(_that.context,_that.email,_that.code,_that.onSuccess);case ForgotPasswordAfter():
return forgotPasswordAfter(_that.context,_that.phone,_that.password,_that.onSuccess);case LoadingChange():
return loadingChange();case AcceptTerm():
return acceptTerm();case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>({TResult? Function( BuildContext context,  String phone,  String email,  String password,  VoidCallback? onSuccess)?  login,TResult? Function( BuildContext context,  String firstname,  String lastname,  String email,  String phone,  String password,  String referral,  VoidCallback? onSuccess)?  signUp,TResult? Function( BuildContext context,  String firstname,  String lastname,  String phone,  String email,  String password,  String referral,  VoidCallback? onSuccess)?  signUpWithData,TResult? Function( BuildContext context,  String email)?  signUpEmail,TResult? Function( BuildContext context,  String phone)?  signUpPhone,TResult? Function( BuildContext context,  String phone,  VoidCallback? onSuccess)?  forgotPassword,TResult? Function( BuildContext context,  IconData type,  VoidCallback? onSuccess)?  socialSignIn,TResult? Function( String id)?  setVerificationId,TResult? Function()?  showPassword,TResult? Function()?  showConfirmPassword,TResult? Function( AuthType type)?  switchScreen,TResult? Function( BuildContext context,  String verifyId,  String code,  String phone,  bool? editPhone)?  checkVerify,TResult? Function( BuildContext context,  String code)?  checkVerifyEmail,TResult? Function( BuildContext context,  String phone,  VoidCallback? onSuccess)?  checkPhone,TResult? Function( BuildContext context,  String email,  String code,  VoidCallback? onSuccess)?  forgotPasswordConfirm,TResult? Function( BuildContext context,  String phone,  String password,  VoidCallback? onSuccess)?  forgotPasswordAfter,TResult? Function()?  loadingChange,TResult? Function()?  acceptTerm,}) {final _that = this;
switch (_that) {
case Login() when login != null:
return login(_that.context,_that.phone,_that.email,_that.password,_that.onSuccess);case SignUp() when signUp != null:
return signUp(_that.context,_that.firstname,_that.lastname,_that.email,_that.phone,_that.password,_that.referral,_that.onSuccess);case SignUpWithData() when signUpWithData != null:
return signUpWithData(_that.context,_that.firstname,_that.lastname,_that.phone,_that.email,_that.password,_that.referral,_that.onSuccess);case SignUpEmail() when signUpEmail != null:
return signUpEmail(_that.context,_that.email);case SignUpPhone() when signUpPhone != null:
return signUpPhone(_that.context,_that.phone);case ForgotPassword() when forgotPassword != null:
return forgotPassword(_that.context,_that.phone,_that.onSuccess);case SocialSignIn() when socialSignIn != null:
return socialSignIn(_that.context,_that.type,_that.onSuccess);case SetVerificationId() when setVerificationId != null:
return setVerificationId(_that.id);case ShowPassword() when showPassword != null:
return showPassword();case ShowConfirmPassword() when showConfirmPassword != null:
return showConfirmPassword();case SwitchScreen() when switchScreen != null:
return switchScreen(_that.type);case CheckVerify() when checkVerify != null:
return checkVerify(_that.context,_that.verifyId,_that.code,_that.phone,_that.editPhone);case CheckVerifyEmail() when checkVerifyEmail != null:
return checkVerifyEmail(_that.context,_that.code);case CheckPhone() when checkPhone != null:
return checkPhone(_that.context,_that.phone,_that.onSuccess);case ForgotPasswordConfirm() when forgotPasswordConfirm != null:
return forgotPasswordConfirm(_that.context,_that.email,_that.code,_that.onSuccess);case ForgotPasswordAfter() when forgotPasswordAfter != null:
return forgotPasswordAfter(_that.context,_that.phone,_that.password,_that.onSuccess);case LoadingChange() when loadingChange != null:
return loadingChange();case AcceptTerm() when acceptTerm != null:
return acceptTerm();case _:
  return null;

}
}

}

/// @nodoc


class Login implements AuthEvent {
  const Login(this.context, {required this.phone, required this.email, required this.password, this.onSuccess});
  

 final  BuildContext context;
 final  String phone;
 final  String email;
 final  String password;
 final  VoidCallback? onSuccess;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$LoginCopyWith<Login> get copyWith => _$LoginCopyWithImpl<Login>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is Login&&(identical(other.context, context) || other.context == context)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.email, email) || other.email == email)&&(identical(other.password, password) || other.password == password)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,phone,email,password,onSuccess);

@override
String toString() {
  return 'AuthEvent.login(context: $context, phone: $phone, email: $email, password: $password, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $LoginCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $LoginCopyWith(Login value, $Res Function(Login) _then) = _$LoginCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String phone, String email, String password, VoidCallback? onSuccess
});




}
/// @nodoc
class _$LoginCopyWithImpl<$Res>
    implements $LoginCopyWith<$Res> {
  _$LoginCopyWithImpl(this._self, this._then);

  final Login _self;
  final $Res Function(Login) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? phone = null,Object? email = null,Object? password = null,Object? onSuccess = freezed,}) {
  return _then(Login(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,password: null == password ? _self.password : password // ignore: cast_nullable_to_non_nullable
as String,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc


class SignUp implements AuthEvent {
  const SignUp(this.context, {required this.firstname, required this.lastname, required this.email, required this.phone, required this.password, required this.referral, this.onSuccess});
  

 final  BuildContext context;
 final  String firstname;
 final  String lastname;
 final  String email;
 final  String phone;
 final  String password;
 final  String referral;
 final  VoidCallback? onSuccess;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SignUpCopyWith<SignUp> get copyWith => _$SignUpCopyWithImpl<SignUp>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SignUp&&(identical(other.context, context) || other.context == context)&&(identical(other.firstname, firstname) || other.firstname == firstname)&&(identical(other.lastname, lastname) || other.lastname == lastname)&&(identical(other.email, email) || other.email == email)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.password, password) || other.password == password)&&(identical(other.referral, referral) || other.referral == referral)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,firstname,lastname,email,phone,password,referral,onSuccess);

@override
String toString() {
  return 'AuthEvent.signUp(context: $context, firstname: $firstname, lastname: $lastname, email: $email, phone: $phone, password: $password, referral: $referral, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $SignUpCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $SignUpCopyWith(SignUp value, $Res Function(SignUp) _then) = _$SignUpCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String firstname, String lastname, String email, String phone, String password, String referral, VoidCallback? onSuccess
});




}
/// @nodoc
class _$SignUpCopyWithImpl<$Res>
    implements $SignUpCopyWith<$Res> {
  _$SignUpCopyWithImpl(this._self, this._then);

  final SignUp _self;
  final $Res Function(SignUp) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? firstname = null,Object? lastname = null,Object? email = null,Object? phone = null,Object? password = null,Object? referral = null,Object? onSuccess = freezed,}) {
  return _then(SignUp(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,firstname: null == firstname ? _self.firstname : firstname // ignore: cast_nullable_to_non_nullable
as String,lastname: null == lastname ? _self.lastname : lastname // ignore: cast_nullable_to_non_nullable
as String,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,password: null == password ? _self.password : password // ignore: cast_nullable_to_non_nullable
as String,referral: null == referral ? _self.referral : referral // ignore: cast_nullable_to_non_nullable
as String,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc


class SignUpWithData implements AuthEvent {
  const SignUpWithData(this.context, {required this.firstname, required this.lastname, required this.phone, required this.email, required this.password, required this.referral, this.onSuccess});
  

 final  BuildContext context;
 final  String firstname;
 final  String lastname;
 final  String phone;
 final  String email;
 final  String password;
 final  String referral;
 final  VoidCallback? onSuccess;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SignUpWithDataCopyWith<SignUpWithData> get copyWith => _$SignUpWithDataCopyWithImpl<SignUpWithData>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SignUpWithData&&(identical(other.context, context) || other.context == context)&&(identical(other.firstname, firstname) || other.firstname == firstname)&&(identical(other.lastname, lastname) || other.lastname == lastname)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.email, email) || other.email == email)&&(identical(other.password, password) || other.password == password)&&(identical(other.referral, referral) || other.referral == referral)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,firstname,lastname,phone,email,password,referral,onSuccess);

@override
String toString() {
  return 'AuthEvent.signUpWithData(context: $context, firstname: $firstname, lastname: $lastname, phone: $phone, email: $email, password: $password, referral: $referral, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $SignUpWithDataCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $SignUpWithDataCopyWith(SignUpWithData value, $Res Function(SignUpWithData) _then) = _$SignUpWithDataCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String firstname, String lastname, String phone, String email, String password, String referral, VoidCallback? onSuccess
});




}
/// @nodoc
class _$SignUpWithDataCopyWithImpl<$Res>
    implements $SignUpWithDataCopyWith<$Res> {
  _$SignUpWithDataCopyWithImpl(this._self, this._then);

  final SignUpWithData _self;
  final $Res Function(SignUpWithData) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? firstname = null,Object? lastname = null,Object? phone = null,Object? email = null,Object? password = null,Object? referral = null,Object? onSuccess = freezed,}) {
  return _then(SignUpWithData(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,firstname: null == firstname ? _self.firstname : firstname // ignore: cast_nullable_to_non_nullable
as String,lastname: null == lastname ? _self.lastname : lastname // ignore: cast_nullable_to_non_nullable
as String,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,password: null == password ? _self.password : password // ignore: cast_nullable_to_non_nullable
as String,referral: null == referral ? _self.referral : referral // ignore: cast_nullable_to_non_nullable
as String,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc


class SignUpEmail implements AuthEvent {
  const SignUpEmail(this.context, {required this.email});
  

 final  BuildContext context;
 final  String email;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SignUpEmailCopyWith<SignUpEmail> get copyWith => _$SignUpEmailCopyWithImpl<SignUpEmail>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SignUpEmail&&(identical(other.context, context) || other.context == context)&&(identical(other.email, email) || other.email == email));
}


@override
int get hashCode => Object.hash(runtimeType,context,email);

@override
String toString() {
  return 'AuthEvent.signUpEmail(context: $context, email: $email)';
}


}

/// @nodoc
abstract mixin class $SignUpEmailCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $SignUpEmailCopyWith(SignUpEmail value, $Res Function(SignUpEmail) _then) = _$SignUpEmailCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String email
});




}
/// @nodoc
class _$SignUpEmailCopyWithImpl<$Res>
    implements $SignUpEmailCopyWith<$Res> {
  _$SignUpEmailCopyWithImpl(this._self, this._then);

  final SignUpEmail _self;
  final $Res Function(SignUpEmail) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? email = null,}) {
  return _then(SignUpEmail(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc


class SignUpPhone implements AuthEvent {
  const SignUpPhone(this.context, {required this.phone});
  

 final  BuildContext context;
 final  String phone;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SignUpPhoneCopyWith<SignUpPhone> get copyWith => _$SignUpPhoneCopyWithImpl<SignUpPhone>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SignUpPhone&&(identical(other.context, context) || other.context == context)&&(identical(other.phone, phone) || other.phone == phone));
}


@override
int get hashCode => Object.hash(runtimeType,context,phone);

@override
String toString() {
  return 'AuthEvent.signUpPhone(context: $context, phone: $phone)';
}


}

/// @nodoc
abstract mixin class $SignUpPhoneCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $SignUpPhoneCopyWith(SignUpPhone value, $Res Function(SignUpPhone) _then) = _$SignUpPhoneCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String phone
});




}
/// @nodoc
class _$SignUpPhoneCopyWithImpl<$Res>
    implements $SignUpPhoneCopyWith<$Res> {
  _$SignUpPhoneCopyWithImpl(this._self, this._then);

  final SignUpPhone _self;
  final $Res Function(SignUpPhone) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? phone = null,}) {
  return _then(SignUpPhone(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc


class ForgotPassword implements AuthEvent {
  const ForgotPassword(this.context, {required this.phone, this.onSuccess});
  

 final  BuildContext context;
 final  String phone;
 final  VoidCallback? onSuccess;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ForgotPasswordCopyWith<ForgotPassword> get copyWith => _$ForgotPasswordCopyWithImpl<ForgotPassword>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ForgotPassword&&(identical(other.context, context) || other.context == context)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,phone,onSuccess);

@override
String toString() {
  return 'AuthEvent.forgotPassword(context: $context, phone: $phone, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $ForgotPasswordCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $ForgotPasswordCopyWith(ForgotPassword value, $Res Function(ForgotPassword) _then) = _$ForgotPasswordCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String phone, VoidCallback? onSuccess
});




}
/// @nodoc
class _$ForgotPasswordCopyWithImpl<$Res>
    implements $ForgotPasswordCopyWith<$Res> {
  _$ForgotPasswordCopyWithImpl(this._self, this._then);

  final ForgotPassword _self;
  final $Res Function(ForgotPassword) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? phone = null,Object? onSuccess = freezed,}) {
  return _then(ForgotPassword(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc


class SocialSignIn implements AuthEvent {
  const SocialSignIn(this.context, {required this.type, this.onSuccess});
  

 final  BuildContext context;
 final  IconData type;
 final  VoidCallback? onSuccess;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SocialSignInCopyWith<SocialSignIn> get copyWith => _$SocialSignInCopyWithImpl<SocialSignIn>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SocialSignIn&&(identical(other.context, context) || other.context == context)&&(identical(other.type, type) || other.type == type)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,type,onSuccess);

@override
String toString() {
  return 'AuthEvent.socialSignIn(context: $context, type: $type, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $SocialSignInCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $SocialSignInCopyWith(SocialSignIn value, $Res Function(SocialSignIn) _then) = _$SocialSignInCopyWithImpl;
@useResult
$Res call({
 BuildContext context, IconData type, VoidCallback? onSuccess
});




}
/// @nodoc
class _$SocialSignInCopyWithImpl<$Res>
    implements $SocialSignInCopyWith<$Res> {
  _$SocialSignInCopyWithImpl(this._self, this._then);

  final SocialSignIn _self;
  final $Res Function(SocialSignIn) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? type = null,Object? onSuccess = freezed,}) {
  return _then(SocialSignIn(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,type: null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as IconData,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc


class SetVerificationId implements AuthEvent {
  const SetVerificationId({required this.id});
  

 final  String id;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SetVerificationIdCopyWith<SetVerificationId> get copyWith => _$SetVerificationIdCopyWithImpl<SetVerificationId>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SetVerificationId&&(identical(other.id, id) || other.id == id));
}


@override
int get hashCode => Object.hash(runtimeType,id);

@override
String toString() {
  return 'AuthEvent.setVerificationId(id: $id)';
}


}

/// @nodoc
abstract mixin class $SetVerificationIdCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $SetVerificationIdCopyWith(SetVerificationId value, $Res Function(SetVerificationId) _then) = _$SetVerificationIdCopyWithImpl;
@useResult
$Res call({
 String id
});




}
/// @nodoc
class _$SetVerificationIdCopyWithImpl<$Res>
    implements $SetVerificationIdCopyWith<$Res> {
  _$SetVerificationIdCopyWithImpl(this._self, this._then);

  final SetVerificationId _self;
  final $Res Function(SetVerificationId) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? id = null,}) {
  return _then(SetVerificationId(
id: null == id ? _self.id : id // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc


class ShowPassword implements AuthEvent {
  const ShowPassword();
  






@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ShowPassword);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'AuthEvent.showPassword()';
}


}




/// @nodoc


class ShowConfirmPassword implements AuthEvent {
  const ShowConfirmPassword();
  






@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ShowConfirmPassword);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'AuthEvent.showConfirmPassword()';
}


}




/// @nodoc


class SwitchScreen implements AuthEvent {
  const SwitchScreen(this.type);
  

 final  AuthType type;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$SwitchScreenCopyWith<SwitchScreen> get copyWith => _$SwitchScreenCopyWithImpl<SwitchScreen>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is SwitchScreen&&(identical(other.type, type) || other.type == type));
}


@override
int get hashCode => Object.hash(runtimeType,type);

@override
String toString() {
  return 'AuthEvent.switchScreen(type: $type)';
}


}

/// @nodoc
abstract mixin class $SwitchScreenCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $SwitchScreenCopyWith(SwitchScreen value, $Res Function(SwitchScreen) _then) = _$SwitchScreenCopyWithImpl;
@useResult
$Res call({
 AuthType type
});




}
/// @nodoc
class _$SwitchScreenCopyWithImpl<$Res>
    implements $SwitchScreenCopyWith<$Res> {
  _$SwitchScreenCopyWithImpl(this._self, this._then);

  final SwitchScreen _self;
  final $Res Function(SwitchScreen) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? type = null,}) {
  return _then(SwitchScreen(
null == type ? _self.type : type // ignore: cast_nullable_to_non_nullable
as AuthType,
  ));
}


}

/// @nodoc


class CheckVerify implements AuthEvent {
  const CheckVerify(this.context, {required this.verifyId, required this.code, required this.phone, this.editPhone});
  

 final  BuildContext context;
 final  String verifyId;
 final  String code;
 final  String phone;
 final  bool? editPhone;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CheckVerifyCopyWith<CheckVerify> get copyWith => _$CheckVerifyCopyWithImpl<CheckVerify>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CheckVerify&&(identical(other.context, context) || other.context == context)&&(identical(other.verifyId, verifyId) || other.verifyId == verifyId)&&(identical(other.code, code) || other.code == code)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.editPhone, editPhone) || other.editPhone == editPhone));
}


@override
int get hashCode => Object.hash(runtimeType,context,verifyId,code,phone,editPhone);

@override
String toString() {
  return 'AuthEvent.checkVerify(context: $context, verifyId: $verifyId, code: $code, phone: $phone, editPhone: $editPhone)';
}


}

/// @nodoc
abstract mixin class $CheckVerifyCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $CheckVerifyCopyWith(CheckVerify value, $Res Function(CheckVerify) _then) = _$CheckVerifyCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String verifyId, String code, String phone, bool? editPhone
});




}
/// @nodoc
class _$CheckVerifyCopyWithImpl<$Res>
    implements $CheckVerifyCopyWith<$Res> {
  _$CheckVerifyCopyWithImpl(this._self, this._then);

  final CheckVerify _self;
  final $Res Function(CheckVerify) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? verifyId = null,Object? code = null,Object? phone = null,Object? editPhone = freezed,}) {
  return _then(CheckVerify(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,verifyId: null == verifyId ? _self.verifyId : verifyId // ignore: cast_nullable_to_non_nullable
as String,code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,editPhone: freezed == editPhone ? _self.editPhone : editPhone // ignore: cast_nullable_to_non_nullable
as bool?,
  ));
}


}

/// @nodoc


class CheckVerifyEmail implements AuthEvent {
  const CheckVerifyEmail(this.context, {required this.code});
  

 final  BuildContext context;
 final  String code;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CheckVerifyEmailCopyWith<CheckVerifyEmail> get copyWith => _$CheckVerifyEmailCopyWithImpl<CheckVerifyEmail>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CheckVerifyEmail&&(identical(other.context, context) || other.context == context)&&(identical(other.code, code) || other.code == code));
}


@override
int get hashCode => Object.hash(runtimeType,context,code);

@override
String toString() {
  return 'AuthEvent.checkVerifyEmail(context: $context, code: $code)';
}


}

/// @nodoc
abstract mixin class $CheckVerifyEmailCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $CheckVerifyEmailCopyWith(CheckVerifyEmail value, $Res Function(CheckVerifyEmail) _then) = _$CheckVerifyEmailCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String code
});




}
/// @nodoc
class _$CheckVerifyEmailCopyWithImpl<$Res>
    implements $CheckVerifyEmailCopyWith<$Res> {
  _$CheckVerifyEmailCopyWithImpl(this._self, this._then);

  final CheckVerifyEmail _self;
  final $Res Function(CheckVerifyEmail) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? code = null,}) {
  return _then(CheckVerifyEmail(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

/// @nodoc


class CheckPhone implements AuthEvent {
  const CheckPhone(this.context, {required this.phone, this.onSuccess});
  

 final  BuildContext context;
 final  String phone;
 final  VoidCallback? onSuccess;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$CheckPhoneCopyWith<CheckPhone> get copyWith => _$CheckPhoneCopyWithImpl<CheckPhone>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is CheckPhone&&(identical(other.context, context) || other.context == context)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,phone,onSuccess);

@override
String toString() {
  return 'AuthEvent.checkPhone(context: $context, phone: $phone, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $CheckPhoneCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $CheckPhoneCopyWith(CheckPhone value, $Res Function(CheckPhone) _then) = _$CheckPhoneCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String phone, VoidCallback? onSuccess
});




}
/// @nodoc
class _$CheckPhoneCopyWithImpl<$Res>
    implements $CheckPhoneCopyWith<$Res> {
  _$CheckPhoneCopyWithImpl(this._self, this._then);

  final CheckPhone _self;
  final $Res Function(CheckPhone) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? phone = null,Object? onSuccess = freezed,}) {
  return _then(CheckPhone(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc


class ForgotPasswordConfirm implements AuthEvent {
  const ForgotPasswordConfirm(this.context, {required this.email, required this.code, this.onSuccess});
  

 final  BuildContext context;
 final  String email;
 final  String code;
 final  VoidCallback? onSuccess;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ForgotPasswordConfirmCopyWith<ForgotPasswordConfirm> get copyWith => _$ForgotPasswordConfirmCopyWithImpl<ForgotPasswordConfirm>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ForgotPasswordConfirm&&(identical(other.context, context) || other.context == context)&&(identical(other.email, email) || other.email == email)&&(identical(other.code, code) || other.code == code)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,email,code,onSuccess);

@override
String toString() {
  return 'AuthEvent.forgotPasswordConfirm(context: $context, email: $email, code: $code, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $ForgotPasswordConfirmCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $ForgotPasswordConfirmCopyWith(ForgotPasswordConfirm value, $Res Function(ForgotPasswordConfirm) _then) = _$ForgotPasswordConfirmCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String email, String code, VoidCallback? onSuccess
});




}
/// @nodoc
class _$ForgotPasswordConfirmCopyWithImpl<$Res>
    implements $ForgotPasswordConfirmCopyWith<$Res> {
  _$ForgotPasswordConfirmCopyWithImpl(this._self, this._then);

  final ForgotPasswordConfirm _self;
  final $Res Function(ForgotPasswordConfirm) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? email = null,Object? code = null,Object? onSuccess = freezed,}) {
  return _then(ForgotPasswordConfirm(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,email: null == email ? _self.email : email // ignore: cast_nullable_to_non_nullable
as String,code: null == code ? _self.code : code // ignore: cast_nullable_to_non_nullable
as String,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc


class ForgotPasswordAfter implements AuthEvent {
  const ForgotPasswordAfter(this.context, {required this.phone, required this.password, this.onSuccess});
  

 final  BuildContext context;
 final  String phone;
 final  String password;
 final  VoidCallback? onSuccess;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$ForgotPasswordAfterCopyWith<ForgotPasswordAfter> get copyWith => _$ForgotPasswordAfterCopyWithImpl<ForgotPasswordAfter>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is ForgotPasswordAfter&&(identical(other.context, context) || other.context == context)&&(identical(other.phone, phone) || other.phone == phone)&&(identical(other.password, password) || other.password == password)&&(identical(other.onSuccess, onSuccess) || other.onSuccess == onSuccess));
}


@override
int get hashCode => Object.hash(runtimeType,context,phone,password,onSuccess);

@override
String toString() {
  return 'AuthEvent.forgotPasswordAfter(context: $context, phone: $phone, password: $password, onSuccess: $onSuccess)';
}


}

/// @nodoc
abstract mixin class $ForgotPasswordAfterCopyWith<$Res> implements $AuthEventCopyWith<$Res> {
  factory $ForgotPasswordAfterCopyWith(ForgotPasswordAfter value, $Res Function(ForgotPasswordAfter) _then) = _$ForgotPasswordAfterCopyWithImpl;
@useResult
$Res call({
 BuildContext context, String phone, String password, VoidCallback? onSuccess
});




}
/// @nodoc
class _$ForgotPasswordAfterCopyWithImpl<$Res>
    implements $ForgotPasswordAfterCopyWith<$Res> {
  _$ForgotPasswordAfterCopyWithImpl(this._self, this._then);

  final ForgotPasswordAfter _self;
  final $Res Function(ForgotPasswordAfter) _then;

/// Create a copy of AuthEvent
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') $Res call({Object? context = null,Object? phone = null,Object? password = null,Object? onSuccess = freezed,}) {
  return _then(ForgotPasswordAfter(
null == context ? _self.context : context // ignore: cast_nullable_to_non_nullable
as BuildContext,phone: null == phone ? _self.phone : phone // ignore: cast_nullable_to_non_nullable
as String,password: null == password ? _self.password : password // ignore: cast_nullable_to_non_nullable
as String,onSuccess: freezed == onSuccess ? _self.onSuccess : onSuccess // ignore: cast_nullable_to_non_nullable
as VoidCallback?,
  ));
}


}

/// @nodoc


class LoadingChange implements AuthEvent {
  const LoadingChange();
  






@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is LoadingChange);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'AuthEvent.loadingChange()';
}


}




/// @nodoc


class AcceptTerm implements AuthEvent {
  const AcceptTerm();
  






@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AcceptTerm);
}


@override
int get hashCode => runtimeType.hashCode;

@override
String toString() {
  return 'AuthEvent.acceptTerm()';
}


}




/// @nodoc
mixin _$AuthState {

 bool get isLoading; bool get isShowPassword; bool get isShowConfirmPassword; AuthType get screenType; bool get isError; bool get isReset; bool get isAcceptTerm; String get verificationId; String get timerText;
/// Create a copy of AuthState
/// with the given fields replaced by the non-null parameter values.
@JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
$AuthStateCopyWith<AuthState> get copyWith => _$AuthStateCopyWithImpl<AuthState>(this as AuthState, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is AuthState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isShowPassword, isShowPassword) || other.isShowPassword == isShowPassword)&&(identical(other.isShowConfirmPassword, isShowConfirmPassword) || other.isShowConfirmPassword == isShowConfirmPassword)&&(identical(other.screenType, screenType) || other.screenType == screenType)&&(identical(other.isError, isError) || other.isError == isError)&&(identical(other.isReset, isReset) || other.isReset == isReset)&&(identical(other.isAcceptTerm, isAcceptTerm) || other.isAcceptTerm == isAcceptTerm)&&(identical(other.verificationId, verificationId) || other.verificationId == verificationId)&&(identical(other.timerText, timerText) || other.timerText == timerText));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,isShowPassword,isShowConfirmPassword,screenType,isError,isReset,isAcceptTerm,verificationId,timerText);

@override
String toString() {
  return 'AuthState(isLoading: $isLoading, isShowPassword: $isShowPassword, isShowConfirmPassword: $isShowConfirmPassword, screenType: $screenType, isError: $isError, isReset: $isReset, isAcceptTerm: $isAcceptTerm, verificationId: $verificationId, timerText: $timerText)';
}


}

/// @nodoc
abstract mixin class $AuthStateCopyWith<$Res>  {
  factory $AuthStateCopyWith(AuthState value, $Res Function(AuthState) _then) = _$AuthStateCopyWithImpl;
@useResult
$Res call({
 bool isLoading, bool isShowPassword, bool isShowConfirmPassword, AuthType screenType, bool isError, bool isReset, bool isAcceptTerm, String verificationId, String timerText
});




}
/// @nodoc
class _$AuthStateCopyWithImpl<$Res>
    implements $AuthStateCopyWith<$Res> {
  _$AuthStateCopyWithImpl(this._self, this._then);

  final AuthState _self;
  final $Res Function(AuthState) _then;

/// Create a copy of AuthState
/// with the given fields replaced by the non-null parameter values.
@pragma('vm:prefer-inline') @override $Res call({Object? isLoading = null,Object? isShowPassword = null,Object? isShowConfirmPassword = null,Object? screenType = null,Object? isError = null,Object? isReset = null,Object? isAcceptTerm = null,Object? verificationId = null,Object? timerText = null,}) {
  return _then(_self.copyWith(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isShowPassword: null == isShowPassword ? _self.isShowPassword : isShowPassword // ignore: cast_nullable_to_non_nullable
as bool,isShowConfirmPassword: null == isShowConfirmPassword ? _self.isShowConfirmPassword : isShowConfirmPassword // ignore: cast_nullable_to_non_nullable
as bool,screenType: null == screenType ? _self.screenType : screenType // ignore: cast_nullable_to_non_nullable
as AuthType,isError: null == isError ? _self.isError : isError // ignore: cast_nullable_to_non_nullable
as bool,isReset: null == isReset ? _self.isReset : isReset // ignore: cast_nullable_to_non_nullable
as bool,isAcceptTerm: null == isAcceptTerm ? _self.isAcceptTerm : isAcceptTerm // ignore: cast_nullable_to_non_nullable
as bool,verificationId: null == verificationId ? _self.verificationId : verificationId // ignore: cast_nullable_to_non_nullable
as String,timerText: null == timerText ? _self.timerText : timerText // ignore: cast_nullable_to_non_nullable
as String,
  ));
}

}


/// Adds pattern-matching-related methods to [AuthState].
extension AuthStatePatterns on AuthState {
/// A variant of `map` that fallback to returning `orElse`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeMap<TResult extends Object?>(TResult Function( _AuthState value)?  $default,{required TResult orElse(),}){
final _that = this;
switch (_that) {
case _AuthState() when $default != null:
return $default(_that);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// Callbacks receives the raw object, upcasted.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case final Subclass2 value:
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult map<TResult extends Object?>(TResult Function( _AuthState value)  $default,){
final _that = this;
switch (_that) {
case _AuthState():
return $default(_that);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `map` that fallback to returning `null`.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case final Subclass value:
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? mapOrNull<TResult extends Object?>(TResult? Function( _AuthState value)?  $default,){
final _that = this;
switch (_that) {
case _AuthState() when $default != null:
return $default(_that);case _:
  return null;

}
}
/// A variant of `when` that fallback to an `orElse` callback.
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return orElse();
/// }
/// ```

@optionalTypeArgs TResult maybeWhen<TResult extends Object?>(TResult Function( bool isLoading,  bool isShowPassword,  bool isShowConfirmPassword,  AuthType screenType,  bool isError,  bool isReset,  bool isAcceptTerm,  String verificationId,  String timerText)?  $default,{required TResult orElse(),}) {final _that = this;
switch (_that) {
case _AuthState() when $default != null:
return $default(_that.isLoading,_that.isShowPassword,_that.isShowConfirmPassword,_that.screenType,_that.isError,_that.isReset,_that.isAcceptTerm,_that.verificationId,_that.timerText);case _:
  return orElse();

}
}
/// A `switch`-like method, using callbacks.
///
/// As opposed to `map`, this offers destructuring.
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case Subclass2(:final field2):
///     return ...;
/// }
/// ```

@optionalTypeArgs TResult when<TResult extends Object?>(TResult Function( bool isLoading,  bool isShowPassword,  bool isShowConfirmPassword,  AuthType screenType,  bool isError,  bool isReset,  bool isAcceptTerm,  String verificationId,  String timerText)  $default,) {final _that = this;
switch (_that) {
case _AuthState():
return $default(_that.isLoading,_that.isShowPassword,_that.isShowConfirmPassword,_that.screenType,_that.isError,_that.isReset,_that.isAcceptTerm,_that.verificationId,_that.timerText);case _:
  throw StateError('Unexpected subclass');

}
}
/// A variant of `when` that fallback to returning `null`
///
/// It is equivalent to doing:
/// ```dart
/// switch (sealedClass) {
///   case Subclass(:final field):
///     return ...;
///   case _:
///     return null;
/// }
/// ```

@optionalTypeArgs TResult? whenOrNull<TResult extends Object?>(TResult? Function( bool isLoading,  bool isShowPassword,  bool isShowConfirmPassword,  AuthType screenType,  bool isError,  bool isReset,  bool isAcceptTerm,  String verificationId,  String timerText)?  $default,) {final _that = this;
switch (_that) {
case _AuthState() when $default != null:
return $default(_that.isLoading,_that.isShowPassword,_that.isShowConfirmPassword,_that.screenType,_that.isError,_that.isReset,_that.isAcceptTerm,_that.verificationId,_that.timerText);case _:
  return null;

}
}

}

/// @nodoc


class _AuthState implements AuthState {
  const _AuthState({this.isLoading = false, this.isShowPassword = false, this.isShowConfirmPassword = false, this.screenType = AuthType.login, this.isError = false, this.isReset = false, this.isAcceptTerm = false, this.verificationId = "", this.timerText = '05:00'});
  

@override@JsonKey() final  bool isLoading;
@override@JsonKey() final  bool isShowPassword;
@override@JsonKey() final  bool isShowConfirmPassword;
@override@JsonKey() final  AuthType screenType;
@override@JsonKey() final  bool isError;
@override@JsonKey() final  bool isReset;
@override@JsonKey() final  bool isAcceptTerm;
@override@JsonKey() final  String verificationId;
@override@JsonKey() final  String timerText;

/// Create a copy of AuthState
/// with the given fields replaced by the non-null parameter values.
@override @JsonKey(includeFromJson: false, includeToJson: false)
@pragma('vm:prefer-inline')
_$AuthStateCopyWith<_AuthState> get copyWith => __$AuthStateCopyWithImpl<_AuthState>(this, _$identity);



@override
bool operator ==(Object other) {
  return identical(this, other) || (other.runtimeType == runtimeType&&other is _AuthState&&(identical(other.isLoading, isLoading) || other.isLoading == isLoading)&&(identical(other.isShowPassword, isShowPassword) || other.isShowPassword == isShowPassword)&&(identical(other.isShowConfirmPassword, isShowConfirmPassword) || other.isShowConfirmPassword == isShowConfirmPassword)&&(identical(other.screenType, screenType) || other.screenType == screenType)&&(identical(other.isError, isError) || other.isError == isError)&&(identical(other.isReset, isReset) || other.isReset == isReset)&&(identical(other.isAcceptTerm, isAcceptTerm) || other.isAcceptTerm == isAcceptTerm)&&(identical(other.verificationId, verificationId) || other.verificationId == verificationId)&&(identical(other.timerText, timerText) || other.timerText == timerText));
}


@override
int get hashCode => Object.hash(runtimeType,isLoading,isShowPassword,isShowConfirmPassword,screenType,isError,isReset,isAcceptTerm,verificationId,timerText);

@override
String toString() {
  return 'AuthState(isLoading: $isLoading, isShowPassword: $isShowPassword, isShowConfirmPassword: $isShowConfirmPassword, screenType: $screenType, isError: $isError, isReset: $isReset, isAcceptTerm: $isAcceptTerm, verificationId: $verificationId, timerText: $timerText)';
}


}

/// @nodoc
abstract mixin class _$AuthStateCopyWith<$Res> implements $AuthStateCopyWith<$Res> {
  factory _$AuthStateCopyWith(_AuthState value, $Res Function(_AuthState) _then) = __$AuthStateCopyWithImpl;
@override @useResult
$Res call({
 bool isLoading, bool isShowPassword, bool isShowConfirmPassword, AuthType screenType, bool isError, bool isReset, bool isAcceptTerm, String verificationId, String timerText
});




}
/// @nodoc
class __$AuthStateCopyWithImpl<$Res>
    implements _$AuthStateCopyWith<$Res> {
  __$AuthStateCopyWithImpl(this._self, this._then);

  final _AuthState _self;
  final $Res Function(_AuthState) _then;

/// Create a copy of AuthState
/// with the given fields replaced by the non-null parameter values.
@override @pragma('vm:prefer-inline') $Res call({Object? isLoading = null,Object? isShowPassword = null,Object? isShowConfirmPassword = null,Object? screenType = null,Object? isError = null,Object? isReset = null,Object? isAcceptTerm = null,Object? verificationId = null,Object? timerText = null,}) {
  return _then(_AuthState(
isLoading: null == isLoading ? _self.isLoading : isLoading // ignore: cast_nullable_to_non_nullable
as bool,isShowPassword: null == isShowPassword ? _self.isShowPassword : isShowPassword // ignore: cast_nullable_to_non_nullable
as bool,isShowConfirmPassword: null == isShowConfirmPassword ? _self.isShowConfirmPassword : isShowConfirmPassword // ignore: cast_nullable_to_non_nullable
as bool,screenType: null == screenType ? _self.screenType : screenType // ignore: cast_nullable_to_non_nullable
as AuthType,isError: null == isError ? _self.isError : isError // ignore: cast_nullable_to_non_nullable
as bool,isReset: null == isReset ? _self.isReset : isReset // ignore: cast_nullable_to_non_nullable
as bool,isAcceptTerm: null == isAcceptTerm ? _self.isAcceptTerm : isAcceptTerm // ignore: cast_nullable_to_non_nullable
as bool,verificationId: null == verificationId ? _self.verificationId : verificationId // ignore: cast_nullable_to_non_nullable
as String,timerText: null == timerText ? _self.timerText : timerText // ignore: cast_nullable_to_non_nullable
as String,
  ));
}


}

// dart format on
