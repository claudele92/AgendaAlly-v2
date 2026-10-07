import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import {
  Button,
  Card,
  Col,
  Alert,
  Form,
  Input,
  Row,
  Select,
  Spin,
  Switch,
} from 'antd';
import { batch, shallowEqual, useDispatch, useSelector } from 'react-redux';
import { disableRefetch, removeFromMenu, setMenuData } from 'redux/slices/menu';
import { useTranslation } from 'react-i18next';
import Paystack from 'assets/images/paystack.svg';
import { FaPaypal } from 'react-icons/fa';
import { SiStripe, SiRazorpay, SiFlutter } from 'react-icons/si';
import { paymentPayloadService } from 'services/paymentPayload';
import { AsyncSelect } from 'components/async-select';
import currencyService from 'services/currency';
import { fetchPaymentPayloads } from 'redux/slices/paymentPayload';
import MediaUpload from 'components/upload';
import ProviderStateSummary from 'components/payment/provider-state-summary';

const PLATFORM_PAYLOAD_FIELDS = {
  stripe: ['currency', 'stripe_pk', 'stripe_sk', 'stripe_webhook_secret'],
  'flutter-wave': [
    'currency',
    'title',
    'description',
    'logo',
    'flw_sk',
    'flw_webhook_secret_hash',
    'flw_account_id',
  ],
  paystack: ['currency', 'paystack_pk', 'paystack_sk'],
  paypal: [
    'paypal_currency',
    'paypal_mode',
    'paypal_validate_ssl',
    'paypal_sandbox_client_id',
    'paypal_sandbox_client_secret',
    'paypal_live_client_id',
    'paypal_live_client_secret',
    'paypal_merchant_id',
    'paypal_webhook_id',
  ],
};

const PLATFORM_SECRET_FIELDS = [
  'stripe_sk',
  'stripe_webhook_secret',
  'flw_sk',
  'flw_webhook_secret_hash',
  'flw_account_id',
  'paystack_sk',
  'paypal_sandbox_client_id',
  'paypal_sandbox_client_secret',
  'paypal_live_client_id',
  'paypal_live_client_secret',
  'paypal_webhook_id',
];

const isPlatformPayload = (tag) => Object.prototype.hasOwnProperty.call(PLATFORM_PAYLOAD_FIELDS, tag);

function getPlatformPayload(tag, values, image) {
  const fields = PLATFORM_PAYLOAD_FIELDS[tag];
  if (!fields) return null;

  return fields.reduce((payload, field) => {
    if (field === 'logo') {
      if (image[0]?.name) payload.logo = image[0].name;
      return payload;
    }
    const value = values[field];
    // Password inputs intentionally start empty. An empty secret means keep
    // the server-side value; never serialize a placeholder or blank over it.
    if (value === undefined || value === null || value === '') return payload;
    const normalizedValue = ['currency', 'paypal_currency'].includes(field)
      ? value?.label || value
      : value;
    payload[field] = field === 'paypal_validate_ssl'
      ? Number(Boolean(value))
      : normalizedValue;
    return payload;
  }, {});
}

const PaymentPayloadEdit = () => {
  const { t } = useTranslation();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const dispatch = useDispatch();
  const [form] = Form.useForm();
  const [credentialPresence, setCredentialPresence] = useState({});
  const [providerState, setProviderState] = useState(null);
  const [configuredEnvironment, setConfiguredEnvironment] = useState(null);
  const navigate = useNavigate();
  const { id } = useParams();
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [loading, setLoading] = useState(false);
  const [activePayment, setActivePayment] = useState(null);
  const [image, setImage] = useState(
    activeMenu.data?.image ? [activeMenu.data?.image] : [],
  );
  const { defaultCurrency } = useSelector(
    (state) => state.currency,
    shallowEqual,
  );

  useEffect(() => {
    return () => {
      const data = form.getFieldsValue(true);
      // Do not persist entered merchant secrets in menu/Redux draft storage.
      PLATFORM_SECRET_FIELDS.forEach((key) => delete data[key]);
      dispatch(setMenuData({ activeMenu, data }));
    };
    // eslint-disable-next-line
  }, []);

  const createImage = (name) => {
    return {
      name,
      url: name,
    };
  };

  const getPayload = (id) => {
    setLoading(true);
    paymentPayloadService
      .getById(id)
      .then(({ data }) => {
        setCredentialPresence(data?.credential_presence || {});
        setProviderState(data?.provider_state || null);
        setConfiguredEnvironment(data?.payload?.configured_environment || null);
        const tag = data?.payment?.tag?.toLowerCase();
        const safePayload = isPlatformPayload(tag)
          ? Object.fromEntries(
              Object.entries(data?.payload || {}).filter(([key]) =>
                PLATFORM_PAYLOAD_FIELDS[tag].includes(key) &&
                !PLATFORM_SECRET_FIELDS.includes(key),
              ),
            )
          : data.payload;
        setActivePayment({
          label: data?.payment?.tag,
          value: data?.payment?.id,
          key: data?.payment?.id,
        });
        form.setFieldsValue({
          ...safePayload,
          ...Object.fromEntries(PLATFORM_SECRET_FIELDS.map((key) => [key, undefined])),
          demo: Boolean(data?.payload?.demo),
          payment_id: data?.payment.tag,
          paypal_validate_ssl: Boolean(data?.payload?.paypal_validate_ssl),
          // sandbox: Boolean(data?.payload?.sandbox),
        });

        setImage([createImage(data?.payload.logo)]);
      })
      .finally(() => {
        setLoading(false);
        dispatch(disableRefetch(activeMenu));
      });
  };

  const onFinish = (values) => {
    delete values.payment_id;
    const tag = activePayment?.label?.toLowerCase();
    if (activePayment?.label === 'flutter-wave' && !image[0]) {
      toast.error(t('choose.payload.image'));
      return;
    }
    setLoadingBtn(true);
    const payload = isPlatformPayload(tag)
      ? getPlatformPayload(tag, values, image)
      : {
          ...values,
          logo: image[0] ? image[0].name : undefined,
          currency: values.currency?.label || values.currency,
          paypal_validate_ssl: values?.paypal_validate_ssl
            ? Number(values.paypal_validate_ssl)
            : undefined,
          sandbox: Number(Boolean(values?.sandbox)),
          demo: values?.demo ? Number(Boolean(values?.demo)) : undefined,
          sub_merchant_key: values?.sub_merchant_key?.length
            ? values?.sub_merchant_key
            : undefined,
        };
    const body = {
      payment_id: activePayment.value,
      payload,
    };

    paymentPayloadService
      .update(id, body)
      .then(() => {
        const nextUrl = 'payment-payloads';
        toast.success(t('successfully.updated'));
        batch(() => {
          dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
          dispatch(fetchPaymentPayloads({}));
        });
        navigate(`/${nextUrl}`);
      })
      .finally(() => setLoadingBtn(false));
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      getPayload(id);
    }
    // eslint-disable-next-line
  }, [activeMenu.refetch]);

  const handleAddIcon = (data) => {
    switch (data) {
      case 'Paypal':
        return <FaPaypal size={80} />;
      case 'Stripe':
        return <SiStripe size={80} />;
      case 'Razorpay':
        return <SiRazorpay size={80} />;
      case 'Paystack':
        return <img src={Paystack} alt='img' width='80' height='80' />;
      case 'flutter-wave':
        return <SiFlutter size={80} />;
      default:
        return null;
    }
  };

  return (
    <Card title={t('edit.payment.payloads')} className='h-100'>
      <ProviderStateSummary state={providerState} />
      {isPlatformPayload(activePayment?.label?.toLowerCase()) && (
        <Alert
          type='info'
          showIcon
          message='Platform payment credentials'
          description={`These credentials configure the platform gateway only; they do not configure Vendor-direct payments. Configuration readiness is separate from provider activation. Save configuration before activation; checkout remains disabled until the server reports it ready and active.${configuredEnvironment ? ` Configured environment: ${configuredEnvironment}.` : ''}`}
          className='mb-3'
        />
      )}
      {!loading ? (
        <Form
          name='edit.payment.payloads'
          layout='vertical'
          onFinish={onFinish}
          form={form}
          initialValues={{ ...activeMenu.data }}
          className='d-flex flex-column h-100'
        >
          <Row gutter={12}>
            <Col
              span={
                activePayment?.label === 'cash' ||
                activePayment?.label === 'wallet'
                  ? 12
                  : 24
              }
            >
              <Form.Item
                label={t('payment')}
                name='payment_id'
                rules={[
                  {
                    required: true,
                    message: t('required'),
                  },
                ]}
              >
                <Select
                  notFoundContent={
                    loading ? <Spin size='small' /> : 'no results'
                  }
                  allowClear
                  disabled
                />
              </Form.Item>
            </Col>

            {activePayment?.label === 'cash' ||
            activePayment?.label === 'wallet' ? (
              ''
            ) : (
              <>
                <Col
                  span={24}
                  className='d-flex justify-content-center mt-4 mb-5'
                >
                  {handleAddIcon(activePayment?.label)}
                </Col>

                {activePayment?.label === 'paystack' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        label={t('paystack.pk')}
                        name='paystack_pk'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('paystack.sk')}
                        name='paystack_sk'
                        extra={credentialPresence.paystack_sk ? 'Configured — leave blank to keep' : 'Not configured'}
                      >
                        <Input.Password autoComplete='new-password' />
                      </Form.Item>
                    </Col>{' '}
                    <Col span={12}>
                      <Form.Item
                        label={t('currency')}
                        name='currency'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <AsyncSelect
                          placeholder={t('select.currency')}
                          valuePropName='label'
                          defaultValue={{
                            value: defaultCurrency.id,
                            label: defaultCurrency.title,
                          }}
                          fetchOptions={() =>
                            currencyService.getAll().then(({ data }) => {
                              return data
                                .filter((item) => item.active)
                                .map((item) => ({
                                  value: item.id,
                                  label: `${item.title}`,
                                  key: item.id,
                                }));
                            })
                          }
                        />
                      </Form.Item>
                    </Col>
                  </>
                ) : activePayment?.label === 'paypal' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        label={t('paypal.mode')}
                        name='paypal_mode'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Select
                          options={[
                            { value: 'live', label: t('live') },
                            { value: 'sandbox', label: t('sandbox') },
                          ]}
                        />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('paypal.currency')}
                        name='paypal_currency'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <AsyncSelect
                          placeholder={t('select.currency')}
                          valuePropName='label'
                          fetchOptions={() =>
                            currencyService.getAll().then(({ data }) =>
                              data
                                .filter((item) => item.active)
                                .map((item) => ({
                                  value: item.id,
                                  label: item.title,
                                  key: item.id,
                                })),
                            )
                          }
                        />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label='Validate PayPal SSL'
                        name='paypal_validate_ssl'
                        valuePropName='checked'
                      >
                        <Switch />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('paypal.sandbox.client.id')}
                        name='paypal_sandbox_client_id'
                        dependencies={['paypal_mode']}
                        rules={[
                          ({ getFieldValue }) => ({
                            required:
                              getFieldValue('paypal_mode') === 'sandbox' &&
                              !credentialPresence.paypal_sandbox_client_id,
                            message: t('required'),
                          }),
                        ]}
                        extra={credentialPresence.paypal_sandbox_client_id ? 'Configured — leave blank to keep' : 'Not configured'}
                      >
                        <Input.Password autoComplete='new-password' />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('paypal.sandbox.client.secret')}
                        name='paypal_sandbox_client_secret'
                        dependencies={['paypal_mode']}
                        rules={[
                          ({ getFieldValue }) => ({
                            required:
                              getFieldValue('paypal_mode') === 'sandbox' &&
                              !credentialPresence.paypal_sandbox_client_secret,
                            message: t('required'),
                          }),
                        ]}
                        extra={credentialPresence.paypal_sandbox_client_secret ? 'Configured — leave blank to keep' : 'Not configured'}
                      >
                        <Input.Password autoComplete='new-password' />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('paypal.live.client.id')}
                        name='paypal_live_client_id'
                        dependencies={['paypal_mode']}
                        rules={[
                          ({ getFieldValue }) => ({
                            required:
                              getFieldValue('paypal_mode') === 'live' &&
                              !credentialPresence.paypal_live_client_id,
                            message: t('required'),
                          }),
                        ]}
                        extra={credentialPresence.paypal_live_client_id ? 'Configured — leave blank to keep' : 'Not configured'}
                      >
                        <Input.Password autoComplete='new-password' />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('paypal.live.client.secret')}
                        name='paypal_live_client_secret'
                        dependencies={['paypal_mode']}
                        rules={[
                          ({ getFieldValue }) => ({
                            required:
                              getFieldValue('paypal_mode') === 'live' &&
                              !credentialPresence.paypal_live_client_secret,
                            message: t('required'),
                          }),
                        ]}
                        extra={credentialPresence.paypal_live_client_secret ? 'Configured — leave blank to keep' : 'Not configured'}
                      >
                        <Input.Password autoComplete='new-password' />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label='PayPal merchant ID'
                        name='paypal_merchant_id'
                        extra='Public merchant identifier used by PayPal verification; not a secret.'
                      >
                        <Input autoComplete='off' />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label='PayPal webhook ID'
                        name='paypal_webhook_id'
                        extra={credentialPresence.paypal_webhook_id ? 'Configured — leave blank to keep' : 'Not configured'}
                      >
                        <Input.Password autoComplete='new-password' />
                      </Form.Item>
                    </Col>
                  </>
                ) : activePayment?.label === 'stripe' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        label={t('stripe.pk')}
                        name='stripe_pk'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('stripe.sk')}
                        name='stripe_sk'
                        extra={credentialPresence.stripe_sk ? 'Configured — leave blank to keep' : 'Not configured'}
                      >
                        <Input.Password autoComplete='new-password' />
                      </Form.Item>
                    </Col>{' '}
                    <Col span={12}>
                      <Form.Item
                        label='Stripe webhook secret'
                        name='stripe_webhook_secret'
                        extra={credentialPresence.stripe_webhook_secret ? 'Configured — leave blank to keep' : 'Not configured'}
                      >
                        <Input.Password autoComplete='new-password' />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('currency')}
                        name='currency'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <AsyncSelect
                          placeholder={t('select.currency')}
                          valuePropName='label'
                          defaultValue={{
                            value: defaultCurrency.id,
                            label: defaultCurrency.title,
                          }}
                          fetchOptions={() =>
                            currencyService.getAll().then(({ data }) => {
                              return data
                                .filter((item) => item.active)
                                .map((item) => ({
                                  value: item.id,
                                  label: `${item.title}`,
                                  key: item.id,
                                }));
                            })
                          }
                        />
                      </Form.Item>
                    </Col>
                  </>
                ) : activePayment?.label === 'razorpay' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        label={t('razorpay.key')}
                        name='razorpay_key'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('razorpay.secret')}
                        name='razorpay_secret'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>{' '}
                    <Col span={12}>
                      <Form.Item
                        label={t('currency')}
                        name='currency'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <AsyncSelect
                          placeholder={t('select.currency')}
                          valuePropName='label'
                          defaultValue={{
                            value: defaultCurrency.id,
                            label: defaultCurrency.title,
                          }}
                          fetchOptions={() =>
                            currencyService.getAll().then(({ data }) => {
                              return data
                                .filter((item) => item.active)
                                .map((item) => ({
                                  value: item.id,
                                  label: `${item.title}`,
                                  key: item.id,
                                }));
                            })
                          }
                        />
                      </Form.Item>
                    </Col>
                  </>
                ) : activePayment?.label === 'flutter-wave' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        label={t('payload.title')}
                        name='title'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('payload.description')}
                        name='description'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('flw_sk')}
                        name='flw_sk'
                        extra={credentialPresence.flw_sk ? 'Configured — leave blank to keep' : 'Not configured'}
                      >
                        <Input.Password autoComplete='new-password' />
                      </Form.Item>
                    </Col>
                    {[
                      ['flw_webhook_secret_hash', 'Webhook verification secret'],
                      ['flw_account_id', 'Merchant account ID'],
                    ].map(([key, label]) => (
                      <Col span={12} key={key}>
                        <Form.Item
                          name={key}
                          label={label}
                          extra={credentialPresence[key] ? 'Configured — leave blank to keep' : 'Not configured'}
                        >
                          <Input.Password autoComplete='new-password' />
                        </Form.Item>
                      </Col>
                    ))}
                    <Col span={12}>
                      <Form.Item
                        label={t('currency')}
                        name='currency'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <AsyncSelect
                          placeholder={t('select.currency')}
                          valuePropName='label'
                          defaultValue={{
                            value: defaultCurrency.id,
                            label: defaultCurrency.title,
                          }}
                          fetchOptions={() =>
                            currencyService.getAll().then(({ data }) => {
                              return data
                                .filter((item) => item.active)
                                .map((item) => ({
                                  value: item.id,
                                  label: `${item.title}`,
                                  key: item.id,
                                }));
                            })
                          }
                        />
                      </Form.Item>
                    </Col>
                    <Col span={6}>
                      <Form.Item rules={[{ required: true }]} label={t('logo')}>
                        <MediaUpload
                          type='brands'
                          imageList={image}
                          setImageList={setImage}
                          form={form}
                          multiple={false}
                        />
                      </Form.Item>
                    </Col>
                  </>
                ) : activePayment?.label.toLowerCase() === 'mollie' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        label={t('partner.id')}
                        name='partner_id'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('profile.id')}
                        name='profile_id'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('secret.key')}
                        name='secret_key'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('currency')}
                        name='currency'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <AsyncSelect
                          placeholder={t('select.currency')}
                          valuePropName='label'
                          defaultValue={{
                            value: defaultCurrency.id,
                            label: defaultCurrency.title,
                          }}
                          fetchOptions={() =>
                            currencyService.getAll().then(({ data }) => {
                              return data
                                .filter((item) => item.active)
                                .map((item) => ({
                                  value: item.id,
                                  label: `${item.title}`,
                                  key: item.id,
                                }));
                            })
                          }
                        />
                      </Form.Item>
                    </Col>
                    <Col span={6}>
                      <Form.Item rules={[{ required: true }]} label={t('logo')}>
                        <MediaUpload
                          type='brands'
                          imageList={image}
                          setImageList={setImage}
                          form={form}
                          multiple={false}
                        />
                      </Form.Item>
                    </Col>
                  </>
                ) : activePayment?.label?.toLowerCase() === 'moya-sar' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        label={t('public.key')}
                        name='public_key'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('secret.key')}
                        name='secret_key'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('secret.token')}
                        name='secret_token'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('currency')}
                        name='currency'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <AsyncSelect
                          placeholder={t('select.currency')}
                          valuePropName='label'
                          defaultValue={{
                            value: defaultCurrency.id,
                            label: defaultCurrency.title,
                          }}
                          fetchOptions={() =>
                            currencyService.getAll().then(({ data }) => {
                              return data
                                .filter((item) => item.active)
                                .map((item) => ({
                                  value: item.id,
                                  label: `${item.title}`,
                                  key: item.id,
                                }));
                            })
                          }
                        />
                      </Form.Item>
                    </Col>
                    <Col span={6}>
                      <Form.Item rules={[{ required: true }]} label={t('logo')}>
                        <MediaUpload
                          type='brands'
                          imageList={image}
                          setImageList={setImage}
                          form={form}
                          multiple={false}
                        />
                      </Form.Item>
                    </Col>
                  </>
                ) : activePayment?.label?.toLowerCase() === 'paytabs' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        label={t('server.key')}
                        name='server_key'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('profile.id')}
                        name='profile_id'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('client.key')}
                        name='client_key'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('currency')}
                        name='currency'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <AsyncSelect
                          placeholder={t('select.currency')}
                          valuePropName='label'
                          defaultValue={{
                            value: defaultCurrency.id,
                            label: defaultCurrency.title,
                          }}
                          fetchOptions={() =>
                            currencyService.getAll().then(({ data }) => {
                              return data
                                .filter((item) => item.active)
                                .map((item) => ({
                                  value: item.id,
                                  label: `${item.title}`,
                                  key: item.id,
                                }));
                            })
                          }
                        />
                      </Form.Item>
                    </Col>
                  </>
                ) : activePayment?.label?.toLowerCase() === 'zain-cash' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        label={t('url')}
                        name='url'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('msisdn')}
                        name='msisdn'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('merchantId')}
                        name='merchantId'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('key')}
                        name='key'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('currency')}
                        name='currency'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <AsyncSelect
                          placeholder={t('select.currency')}
                          valuePropName='label'
                          defaultValue={{
                            value: defaultCurrency.id,
                            label: defaultCurrency.title,
                          }}
                          fetchOptions={() =>
                            currencyService.getAll().then(({ data }) => {
                              return data
                                .filter((item) => item.active)
                                .map((item) => ({
                                  value: item.id,
                                  label: `${item.title}`,
                                  key: item.id,
                                }));
                            })
                          }
                        />
                      </Form.Item>
                    </Col>
                  </>
                ) : activePayment?.label?.toLowerCase() === 'mercado-pago' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        label={t('token')}
                        name='token'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('currency')}
                        name='currency'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <AsyncSelect
                          placeholder={t('select.currency')}
                          valuePropName='label'
                          defaultValue={{
                            value: defaultCurrency.id,
                            label: defaultCurrency.title,
                          }}
                          fetchOptions={() =>
                            currencyService.getAll().then(({ data }) => {
                              return data
                                .filter((item) => item.active)
                                .map((item) => ({
                                  value: item.id,
                                  label: `${item.title}`,
                                  key: item.id,
                                }));
                            })
                          }
                        />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('sandbox')}
                        name='sandbox'
                        valuePropName='checked'
                      >
                        <Switch />
                      </Form.Item>
                    </Col>
                  </>
                ) : activePayment?.label?.toLowerCase() === 'maksekeskus' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        label={t('shop.id')}
                        name='shop_id'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('key.publishable')}
                        name='key_publishable'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('country')}
                        name='country'
                        rules={[
                          {
                            required: true,
                            message: t('required'),
                          },
                        ]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('locale')}
                        name='locale'
                        rules={[
                          {
                            required: true,
                            message: t('required'),
                          },
                        ]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('currency')}
                        name='currency'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <AsyncSelect
                          placeholder={t('select.currency')}
                          valuePropName='label'
                          defaultValue={{
                            value: defaultCurrency.id,
                            label: defaultCurrency.title,
                          }}
                          fetchOptions={() =>
                            currencyService.getAll().then(({ data }) => {
                              return data
                                .filter((item) => item.active)
                                .map((item) => ({
                                  value: item.id,
                                  label: `${item.title}`,
                                  key: item.id,
                                }));
                            })
                          }
                        />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('demo')}
                        name='demo'
                        valuePropName='checked'
                      >
                        <Switch />
                      </Form.Item>
                    </Col>
                  </>
                ) : activePayment?.label?.toLowerCase() === 'iyzico' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        name='api_key'
                        label={t('api.key')}
                        rules={[
                          {
                            required: true,
                            message: t('required'),
                          },
                        ]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        name='secret_key'
                        label={t('secret.key')}
                        rules={[
                          {
                            required: true,
                            message: t('required'),
                          },
                        ]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        name='sub_merchant_key'
                        label={t('sub.merchant.key')}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('currency')}
                        name='currency'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <AsyncSelect
                          placeholder={t('select.currency')}
                          valuePropName='label'
                          defaultValue={{
                            value: defaultCurrency.id,
                            label: defaultCurrency.title,
                          }}
                          fetchOptions={() =>
                            currencyService.getAll().then(({ data }) => {
                              return data
                                .filter((item) => item.active)
                                .map((item) => ({
                                  value: item.id,
                                  label: `${item.title}`,
                                  key: item.id,
                                }));
                            })
                          }
                        />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('sandbox')}
                        name='sandbox'
                        valuePropName='checked'
                      >
                        <Switch />
                      </Form.Item>
                    </Col>
                  </>
                ) : activePayment?.label === 'pay-fast' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        label={t('merchant.id')}
                        name='merchant_id'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('merchant.key')}
                        name='merchant_key'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('pass.phrase')}
                        name='pass_phrase'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('sandbox')}
                        name='sandbox'
                        valuePropName='checked'
                      >
                        <Switch />
                      </Form.Item>
                    </Col>
                  </>
                ) : activePayment?.label === 'payu' ? (
                  <>
                    <Col span={12}>
                      <Form.Item
                        label={t('client.id')}
                        name='client_id'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('client.secret')}
                        name='client_secret'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('merchant.id')}
                        name='merchant_id'
                        rules={[{ required: true, message: t('required') }]}
                      >
                        <Input />
                      </Form.Item>
                    </Col>
                    <Col span={12}>
                      <Form.Item
                        label={t('sandbox')}
                        name='sandbox'
                        valuePropName='checked'
                      >
                        <Switch />
                      </Form.Item>
                    </Col>
                  </>
                ) : null}
              </>
            )}
          </Row>
          <div className='flex-grow-1 d-flex flex-column justify-content-end'>
            <div className='pb-5'>
              <Button
                type='primary'
                htmlType='submit'
                loading={loadingBtn}
                disabled={loadingBtn}
              >
                {t('submit')}
              </Button>
            </div>
          </div>
        </Form>
      ) : (
        <div className='d-flex justify-content-center align-items-center'>
          <Spin size='large' className='py-5' />
        </div>
      )}
    </Card>
  );
};

export default PaymentPayloadEdit;
