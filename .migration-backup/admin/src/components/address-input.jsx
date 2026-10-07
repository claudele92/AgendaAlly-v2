import { Form } from 'antd';
import React from 'react';
import { usePlacesWidget } from 'react-google-autocomplete';
import { useTranslation } from 'react-i18next';
import useGoogleMapsReady from '../helpers/googleMapsLoader';
import MapUnavailable from './map-unavailable';

// Only ever mounted once the shared Google Maps script has loaded (see
// AddressInput below) — usePlacesWidget's own script-loading effect runs
// once, on mount, so it must not mount before then.
function AddressAutocompleteInput({ setLocation, form, defaultLang, ...rest }) {
  const { ref } = usePlacesWidget({
    onPlaceSelected: (place) => {
      const location = {
        lat: place?.geometry.location.lat(),
        lng: place?.geometry.location.lng(),
      };
      setLocation(location);
      form.setFieldsValue({
        [`address[${defaultLang}]`]: place?.formatted_address,
      });
    },
  });

  return (
    <input className='address-input' ref={ref} placeholder={''} {...rest} />
  );
}

export default function AddressInput({
  setLocation,
  form,
  item,
  idx,
  defaultLang,
}) {
  const { t } = useTranslation();
  const ready = useGoogleMapsReady();

  return (
    <Form.Item
      key={'address' + idx}
      label={t('address')}
      name={`address[${item?.locale}]`}
      rules={[
        {
          required: item?.locale === defaultLang,
          message: t('required'),
        },
      ]}
      hidden={item?.locale !== defaultLang}
    >
      {ready ? (
        <AddressAutocompleteInput
          setLocation={setLocation}
          form={form}
          defaultLang={defaultLang}
        />
      ) : (
        <>
          <input className='address-input' disabled placeholder={''} />
          <MapUnavailable inline />
        </>
      )}
    </Form.Item>
  );
}
