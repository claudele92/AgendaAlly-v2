import { Button, Form, Input, Space } from 'antd';
import { InfiniteSelect } from 'components/infinite-select';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { shallowEqual, useSelector } from 'react-redux';
import addressService from 'services/rest/address';
import AddressForm from 'components/forms/address-form';
import Map from 'components/map';
import getDefaultLocation from 'helpers/getDefaultLocation';

// initialValues (edit mode only) is { alias, country, city, address,
// latitude, longitude } — country/city already in the {value, label} shape
// fetchCountries/fetchCities produce, so InfiniteSelect (labelInValue) can
// display them without needing to re-fetch the option first.
const LocationSelect = ({ onClose, onSubmit, isButtonLoading, initialValues }) => {
  const { t } = useTranslation();
  const [form] = Form.useForm();
  const { settings } = useSelector(
    (state) => state.globalSettings,
    shallowEqual,
  );
  const [links, setLinks] = useState(null);
  const [selectedCountry, setSelectedCountry] = useState(
    initialValues?.country || null,
  );
  const [addressValue, setAddressValue] = useState(
    initialValues?.address || '',
  );
  const [location, setLocation] = useState(
    initialValues?.latitude && initialValues?.longitude
      ? { lat: initialValues.latitude, lng: initialValues.longitude }
      : getDefaultLocation(settings),
  );

  const fetchCountries = ({ search, page }) => {
    const params = { search, page };

    if (!search?.trim()?.length) delete params?.search;

    return addressService.getCountries(params).then((res) => {
      setLinks(res.links);
      return res.data.map((country) => ({
        label: country?.translation?.title,
        value: `${country?.id},${country?.region_id}`,
        key: country?.id,
      }));
    });
  };

  const fetchCities = ({ search, page }) => {
    const params = {
      search,
      page,
      country_id: selectedCountry?.value?.split(',')[0],
    };

    if (!search?.trim()?.length) delete params?.search;

    return addressService.getCities(params).then((res) => {
      setLinks(res.links);
      const cities = [];
      if (res.meta.current_page === 1) {
        cities.push({
          label: t('whole.country'),
          value: 'all',
        });
      }
      res.data.forEach((country) => {
        cities.push({
          label: country?.translation?.title,
          value: country.id,
        });
      });
      return cities;
    });
  };

  const handleFinish = (values) => {
    onSubmit({
      ...values,
      latitude: location?.lat,
      longitude: location?.lng,
    });
  };

  return (
    <Form
      layout='vertical'
      form={form}
      onFinish={handleFinish}
      initialValues={initialValues}
    >
      <Form.Item name='alias' label={t('location.name')}>
        <Input placeholder={t('location.name.placeholder')} />
      </Form.Item>
      <Form.Item
        name='country'
        label={t('country')}
        rules={[{ required: true, message: t('required') }]}
      >
        <InfiniteSelect
          hasMore={links?.next}
          fetchOptions={fetchCountries}
          onChange={(value) => {
            setSelectedCountry(value);
            form.setFieldsValue({ city: null });
          }}
        />
      </Form.Item>
      {selectedCountry && (
        <>
          <Form.Item
            rules={[{ required: true, message: t('required') }]}
            name='city'
            label={t('city')}
          >
            <InfiniteSelect hasMore={links?.next} fetchOptions={fetchCities} />
          </Form.Item>
          <AddressForm
            withLanguages={false}
            addressRequired={false}
            value={addressValue}
            setValue={setAddressValue}
            setLocation={setLocation}
          />
          <Map
            location={location}
            setLocation={setLocation}
            setAddress={(value) => {
              setAddressValue(value);
              form.setFieldsValue({ address: value });
            }}
          />
        </>
      )}
      <Space className='justify-content-end w-100'>
        <Button onClick={onClose} htmlType='button'>
          {t('cancel')}
        </Button>
        <Button loading={isButtonLoading} htmlType='submit' type='primary'>
          {initialValues ? t('save') : t('add')}
        </Button>
      </Space>
    </Form>
  );
};

export default LocationSelect;
