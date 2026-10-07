import React from 'react';
import useGoogle from 'react-google-autocomplete/lib/usePlacesAutocompleteService';
import getAddress from 'helpers/getAddress';
import { COUNTRY_CODE } from 'configs/app-global';
import useGoogleMapsReady from 'helpers/googleMapsLoader';
import { shallowEqual } from 'react-redux';
import { Form, Select } from 'antd';
import { t } from 'i18next';
import { useSelector } from 'react-redux';
import { toast } from 'react-toastify';
import getMapApiKey from 'helpers/getMapApiKey';

const options = COUNTRY_CODE
  ? { componentRestrictions: { country: COUNTRY_CODE } }
  : {};

// Renders the address Form.Item(s) — one per language, or a single one —
// with whatever control renderControl() returns. Shared between the live,
// autocomplete-backed control and the disabled placeholder shown before the
// Google Maps script has loaded, so the form's fields (name/rules) register
// identically either way.
const AddressFormFields = ({ withLanguages, addressRequired, renderControl }) => {
  const { defaultLang, languages } = useSelector(
    (state) => state.formLang,
    shallowEqual,
  );

  if (!withLanguages) {
    return (
      <Form.Item
        label={t('address')}
        name='address'
        rules={[{ required: addressRequired, message: t('required') }]}
      >
        {renderControl()}
      </Form.Item>
    );
  }

  return (
    <>
      {languages.map((item, idx) => (
        <Form.Item
          key={'address' + idx}
          label={t('address')}
          name={`address[${item.locale}]`}
          rules={[
            {
              required: item.locale === defaultLang,
              message: t('required'),
            },
          ]}
          hidden={item.locale !== defaultLang}
        >
          {renderControl()}
        </Form.Item>
      ))}
    </>
  );
};

// Only ever mounted once the shared Google Maps script has loaded (see
// AddressForm below) — usePlacesAutocompleteService's own script-loading
// effect runs once, on mount, so it must not mount before then.
const AddressAutocompleteForm = ({
  value,
  setValue,
  setLocation,
  withLanguages,
  addressRequired,
}) => {
  const { placePredictions, getPlacePredictions, isPlacePredictionsLoading } =
    useGoogle({ options });

  const renderSelect = () => (
    <Select
      allowClear
      searchValue={value}
      showSearch
      autoClearSearchValue
      loading={isPlacePredictionsLoading}
      placeholder={t('search')}
      options={placePredictions?.map((prediction) => ({
        label: prediction.description,
        value: prediction.description,
      }))}
      onSearch={(searchValue) => {
        setValue(searchValue);
        if (searchValue.length > 0) {
          getPlacePredictions({ input: searchValue });
        }
      }}
      onSelect={async (value) => {
        const address = await getAddress(value);
        const lat = address?.geometry?.location?.lat;
        const lng = address?.geometry?.location?.lng;

        if (typeof lat !== 'number' || typeof lng !== 'number') {
          toast.warning(t('no.address'));
          return;
        }

        setLocation({ lat, lng });
      }}
      getPopupContainer={(trigger) => trigger.parentNode}
    />
  );

  return (
    <AddressFormFields
      withLanguages={withLanguages}
      addressRequired={addressRequired}
      renderControl={renderSelect}
    />
  );
};

const AddressForm = ({
  value,
  setValue,
  setLocation,
  withLanguages = true,
  addressRequired = true,
}) => {
  const ready = useGoogleMapsReady();
  const apiKey = getMapApiKey();

  if (!ready) {
    return (
      <AddressFormFields
        withLanguages={withLanguages}
        addressRequired={addressRequired}
        renderControl={() => (
          <div>
            <Select disabled loading={Boolean(apiKey)} />
            {!apiKey && (
              <span role='status'>
                Maps are unavailable because no API key is configured for this environment.
              </span>
            )}
          </div>
        )}
      />
    );
  }

  return (
    <AddressAutocompleteForm
      value={value}
      setValue={setValue}
      setLocation={setLocation}
      withLanguages={withLanguages}
      addressRequired={addressRequired}
    />
  );
};

export default AddressForm;
