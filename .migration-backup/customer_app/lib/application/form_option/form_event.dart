part of 'form_bloc.dart';

@freezed
abstract class FormEvent with _$FormEvent {
  const factory FormEvent.fetchForms(
    BuildContext context, {
    required List<int>? serviceMasterIds,
  }) = FetchForms;

  const factory FormEvent.fetchSingleForms(
    BuildContext context, {
    required int? id,
    FormOptionsData? form,
  }) = FetchSingleForms;

  const factory FormEvent.setFormOption(
    BuildContext context, {
    required FormOptionsData? formOptions,
  }) = SetFormOption;
}
