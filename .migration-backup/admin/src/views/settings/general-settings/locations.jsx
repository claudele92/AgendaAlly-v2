import { Alert, Button, Card, Checkbox, Col, Form, Input, Row, Switch } from 'antd';
import React, { useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch } from 'react-redux';
import { shallowEqual, useSelector } from 'react-redux';
import { toast } from 'react-toastify';
import Map from 'components/map';
import settingService from 'services/settings';
import { fetchSettings as getSettings } from 'redux/slices/globalSettings';
import useDemo from 'helpers/useDemo';
import AddressForm from 'components/forms/address-form';

const Locations = ({ location, setLocation }) => {
  const { t } = useTranslation();
  const [form] = Form.useForm();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const mapsEnabled = useSelector((state) => state.globalSettings.settings?.maps_enabled);
  const enabled = [true, 1, '1', 'true'].includes(mapsEnabled ?? activeMenu.data?.maps_enabled);
  const mapsEdited = useRef(false);
  useEffect(() => {
    // Menu snapshots predate new settings and can omit this flag. Use the
    // current server settings, without overwriting an unsaved operator edit.
    if (!mapsEdited.current) form.setFieldsValue({ maps_enabled: enabled });
  }, [enabled, form]);
  const dispatch = useDispatch();
  const [loadingBtn, setLoadingBtn] = useState(false);
  const { isDemo } = useDemo();
  const [value, setValue] = useState('');

  function updateSettings(data) {
    setLoadingBtn(true);
    settingService
      .update(data)
      .then(() => {
        mapsEdited.current = false;
        toast.success(t('successfully.updated'));
        dispatch(getSettings());
      })
      .finally(() => setLoadingBtn(false));
  }

  const onFinish = (values) => {
    const body = {
      ...values,
      location: `${location.lat}, ${location.lng}`,
    };
    updateSettings(body);
  };

  return (
    <Form
      layout='vertical'
      form={form}
      name='global-settings'
      onFinish={onFinish}
      onValuesChange={(changed) => {
        if (Object.prototype.hasOwnProperty.call(changed, 'maps_enabled')) mapsEdited.current = true;
      }}
      initialValues={{
        ...activeMenu.data,
        maps_enabled: enabled,
        google_map_server_key: '',
      }}
    >
      <Card>
        <Row>
          <Col span={24}>
            <Alert type='info' showIcon className='mb-3'
              message='Platform Maps configuration'
              description='Browser Maps requires explicit enablement and a browser key restricted to your website referrers. Country/city discovery works without Maps. Android/iOS map SDK keys remain native build configuration. Server geocoding has a separate key and environment opt-in. Reload open Maps pages after changing keys.' />
            <Form.Item label='Enable browser Maps' name='maps_enabled' valuePropName='checked'>
              <Switch />
            </Form.Item>
            <Form.Item
              label='Public browser Maps key'
              name='google_map_key'
              extra='Blank keeps the saved key. This public key is delivered to browsers, not reused for server geocoding.'
            >
              <Input.Password autoComplete='new-password' visibilityToggle={false} />
            </Form.Item>
            <Form.Item name='clear_google_map_key' valuePropName='checked'>
              <Checkbox>Remove saved browser key</Checkbox>
            </Form.Item>
            <Form.Item label='Server geocoding key (optional)' name='google_map_server_key'
              extra={activeMenu.data?.google_map_server_key_configured === '1' ? 'A server key is configured. Leave blank to keep it.' : 'No server key is configured. Restrict this separate key by IP and API.'}>
              <Input.Password autoComplete='new-password' visibilityToggle={false} />
            </Form.Item>
            <Form.Item name='clear_google_map_server_key' valuePropName='checked'>
              <Checkbox>Remove saved server key</Checkbox>
            </Form.Item>
            <AddressForm
              withLanguages={false}
              addressRequired={false}
              setLocation={setLocation}
              value={value}
              setValue={setValue}
            />
          </Col>
          <Col span={24}>
            <Form.Item
              label={t('map')}
              name='location'
              style={{ borderRadius: '50px' }}
            >
              <Map
                location={location}
                setLocation={setLocation}
                setAddress={(value) => form.setFieldsValue({ address: value })}
              />
            </Form.Item>
          </Col>
        </Row>
        <Button
          type='primary'
          onClick={() => form.submit()}
          loading={loadingBtn}
          disabled={isDemo}
        >
          {t('save')}
        </Button>
      </Card>
    </Form>
  );
};

export default Locations;
