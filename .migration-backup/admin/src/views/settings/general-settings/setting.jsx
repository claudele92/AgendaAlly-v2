import {
  Button,
  Col,
  Form,
  Input,
  InputNumber,
  Row,
  Select,
  Space,
} from 'antd';
import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch } from 'react-redux';
import { shallowEqual, useSelector } from 'react-redux';
import { toast } from 'react-toastify';
import ImageUploadSingle from 'components/image-upload-single';
import settingService from 'services/settings';
import { Time } from './deliveryman_time';
import { fetchSettings as getSettings } from 'redux/slices/globalSettings';
import useDemo from 'helpers/useDemo';

const Setting = ({
  setFavicon,
  favicon,
  setLogo,
  logo,
  setDarkLogo,
  darkLogo,
  adminFavicon,
  setAdminFavicon,
}) => {
  const { t } = useTranslation();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const dispatch = useDispatch();
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [form] = Form.useForm();
  const { isDemo } = useDemo();
  const serviceFeeType =
    Form.useWatch('service_fee_type', form) ??
    activeMenu.data?.service_fee_type ??
    'fixed';

  function updateSettings(data) {
    setLoadingBtn(true);
    settingService
      .update(data)
      .then(() => {
        toast.success(t('successfully.updated'));
        dispatch(getSettings());
      })
      .finally(() => setLoadingBtn(false));
  }

  const onFinish = (values) => {
    const body = {
      ...values,
      favicon: favicon.name,
      logo: logo.name,
      // Optional, unlike the other three - a shop that never uploads one
      // just keeps using `logo` everywhere, light or dark background alike.
      dark_logo: darkLogo?.name || '',
      admin_favicon: adminFavicon.name,
      hour_format: values.using_12_hour_format === '1' ? 'hh:mm a' : 'HH:mm',
    };
    updateSettings(body);
  };

  return (
    <Form
      layout='vertical'
      form={form}
      name='global-settings'
      onFinish={onFinish}
      initialValues={{
        delivery: '1',
        // payment_type: 'admin',
        deliveryman_order_acceptance_time: 30,
        ...activeMenu.data,
        max_day_booking: activeMenu.data?.max_day_booking || 90,
        service_fee_type: activeMenu.data?.service_fee_type || 'fixed',
      }}
    >
      <Row gutter={12}>
        <Col span={12}>
          <Form.Item
            label={t('title')}
            name='title'
            rules={[
              {
                validator(_, value) {
                  if (!value) {
                    return Promise.reject(new Error(t('required')));
                  } else if (value && value?.trim() === '') {
                    return Promise.reject(new Error(t('no.empty.space')));
                  } else if (value && value?.trim().length < 2) {
                    return Promise.reject(new Error(t('must.be.at.least.2')));
                  }
                  return Promise.resolve();
                },
              },
            ]}
          >
            <Input />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('deliveryman.order.acceptance.time')}
            name='deliveryman_order_acceptance_time'
          >
            <Select options={Time} />
          </Form.Item>
        </Col>
        {/*<Col span={12}>*/}
        {/*  <Form.Item*/}
        {/*    label={t('payment.type')}*/}
        {/*    name='payment_type'*/}
        {/*    rules={[*/}
        {/*      {*/}
        {/*        required: true,*/}
        {/*        message: t('required'),*/}
        {/*      },*/}
        {/*    ]}*/}
        {/*  >*/}
        {/*    <Select>*/}
        {/*      <Select.Option value='admin'>{t('admin')}</Select.Option>*/}
        {/*      <Select.Option value='seller'>{t('seller')}</Select.Option>*/}
        {/*    </Select>*/}
        {/*  </Form.Item>*/}
        {/*</Col>*/}
        <Col span={12}>
          <Form.Item
            label={t('service_fee_type')}
            name='service_fee_type'
          >
            <Select
              options={[
                { label: t('fixed'), value: 'fixed' },
                { label: t('percentage'), value: 'percentage' },
              ]}
            />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('service.fee')}
            name='service_fee'
            rules={[
              {
                validator(_, value) {
                  if (
                    serviceFeeType === 'percentage' &&
                    value &&
                    (value < 0 || value > 100)
                  ) {
                    return Promise.reject(
                      new Error(t('must.be.between.0.and.100')),
                    );
                  }
                  return Promise.resolve();
                },
              },
              {
                required: true,
                message: t('required'),
              },
            ]}
          >
            <InputNumber
              min={0}
              className='w-100'
              addonAfter={serviceFeeType === 'percentage' ? '%' : undefined}
            />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('min.amount')}
            name='min_amount'
            rules={[{ required: true, message: t('required') }]}
          >
            <InputNumber min={0} className='w-100' />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('max.day.booking')}
            name='max_day_booking'
            rules={[{ required: true, message: t('required') }]}
          >
            <InputNumber min={0} className='w-100' />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('booking.refund.canceled.hour')}
            name='booking_refund_canceled_hour'
            rules={[{ required: true, message: t('required') }]}
          >
            <InputNumber min={0} className='w-100' />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('booking.service.fee')}
            name='booking_service_fee'
            rules={[
              {
                validator(_, value) {
                  if (
                    serviceFeeType === 'percentage' &&
                    value &&
                    (value < 0 || value > 100)
                  ) {
                    return Promise.reject(
                      new Error(t('must.be.between.0.and.100')),
                    );
                  }
                  return Promise.resolve();
                },
              },
              { required: true, message: t('required') },
            ]}
          >
            <InputNumber
              min={0}
              className='w-100'
              addonAfter={serviceFeeType === 'percentage' ? '%' : undefined}
            />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('booking_canceled_commission')}
            name='booking_canceled_commission'
            rules={[
              {
                validator(_, value) {
                  if (value && (value < 0 || value > 100)) {
                    return Promise.reject(
                      new Error(t('must.be.between.0.and.100')),
                    );
                  }
                  return Promise.resolve();
                },
              },
              { required: true, message: t('required') },
            ]}
          >
            <InputNumber min={0} className='w-100' addonAfter='%' />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('time.format')}
            name='using_12_hour_format'
            rules={[{ required: true, message: t('required') }]}
          >
            <Select
              options={[
                { label: t('24.hour.format'), value: '0' },
                { label: t('12.hour.format'), value: '1' },
              ]}
            />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('ai.api.key')}
            name='ai_api_key'
            rules={[
              {
                validator(_, value) {
                  if (!value) {
                    return Promise.reject(new Error(t('required')));
                  } else if (value && value?.trim() === '') {
                    return Promise.reject(new Error(t('no.empty.space')));
                  } else if (value && value?.trim().length < 2) {
                    return Promise.reject(new Error(t('must.be.at.least.2')));
                  }
                  return Promise.resolve();
                },
              },
            ]}
          >
            <Input />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Space>
            <Form.Item
              label={t('favicon')}
              name='favicon'
              rules={[
                {
                  required: true,
                  message: t('required'),
                },
              ]}
            >
              <ImageUploadSingle
                type='settings'
                image={favicon}
                setImage={setFavicon}
                form={form}
                name='favicon'
              />
            </Form.Item>
            <Form.Item
              label={t('logo')}
              name='logo'
              rules={[
                {
                  required: true,
                  message: t('required'),
                },
              ]}
            >
              <ImageUploadSingle
                type='settings'
                image={logo}
                setImage={setLogo}
                form={form}
                name='logo'
              />
            </Form.Item>
            <Form.Item label={t('dark.logo')} name='dark_logo'>
              <ImageUploadSingle
                type='settings'
                image={darkLogo}
                setImage={setDarkLogo}
                form={form}
                name='dark_logo'
              />
            </Form.Item>
            <Form.Item
              label={t('admin.favicon')}
              name='admin_favicon'
              rules={[
                {
                  required: true,
                  message: t('required'),
                },
              ]}
            >
              <ImageUploadSingle
                type='settings'
                image={adminFavicon}
                setImage={setAdminFavicon}
                form={form}
                name='admin_favicon'
              />
            </Form.Item>
          </Space>
        </Col>
      </Row>
      <Button
        onClick={() => form.submit()}
        type='primary'
        disabled={isDemo}
        loading={loadingBtn}
      >
        {t('save')}
      </Button>
    </Form>
  );
};

export default Setting;
