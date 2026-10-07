import React, { useEffect, useState } from 'react';
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
import { useTranslation } from 'react-i18next';
import { removeFromMenu, setRefetch } from 'redux/slices/menu';
import { batch, shallowEqual, useDispatch, useSelector } from 'react-redux';
import { toast } from 'react-toastify';
import { useNavigate } from 'react-router-dom';
import Paystack from 'assets/images/paystack.svg';
import { FaPaypal } from 'react-icons/fa';
import { SiStripe, SiRazorpay, SiFlutter } from 'react-icons/si';
import { fetchPaymentPayloads } from 'redux/slices/paymentPayload';
import { paymentPayloadService } from 'services/paymentPayload';
import paymentService from 'services/payment';
import { AsyncSelect } from 'components/async-select';
import currencyService from 'services/currency';
import MediaUpload from 'components/upload';

// Gateways this form actually has a field set for below — matches the
// PaymentPayload-backed services (RazorPayService, FlutterWaveService,
// IyzicoService, MaksekeskusService, MercadoPagoService, MollieService,
// MoyasarService, PayFastService, PayPalService, PayStackService,
// PayTabsService, PayuService, StripeService, ZainCashService). Orange/MTN
// don't use PaymentPayload at all (their config is
// ShopPayment/PlatformPaymentConfig — see the seller/platform payment
// screens), so they're excluded here.
const SUPPORTED_TAGS = [
  'paystack',
  'paypal',
  'stripe',
  'razorpay',
  'flutter-wave',
  'mollie',
  'moya-sar',
  'paytabs',
  'zain-cash',
  'mercado-pago',
  'maksekeskus',
  'iyzico',
  'pay-fast',
  'payu',
];

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

export default function PaymentPayloadAdd() {
  const { t } = useTranslation();
  const [form] = Form.useForm();
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [loading, setLoading] = useState(false);
  const [paymentList, setPaymentList] = useState([]);
  const [activePayment, setActivePayment] = useState(null);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [image, setImage] = useState(
    activeMenu.data?.image ? [activeMenu.data?.image] : [],
  );
  const { defaultCurrency } = useSelector(
    (state) => state.currency,
    shallowEqual,
  );

  const dispatch = useDispatch();
  const navigate = useNavigate();

  const onFinish = (values) => {
    delete values.payment_id;
    const tag = activePayment?.label?.toLowerCase();
    if (activePayment?.label === 'Flutter-wave' && !image[0]) {
      toast.error(t('choose.payload.image'));
      return;
    }
    const payload = isPlatformPayload(tag)
      ? getPlatformPayload(tag, values, image)
      : {
          ...values,
          logo: image[0] ? image[0].name : undefined,
          currency: values.currency?.label || values.currency,
          paypal_validate_ssl: values?.paypal_validate_ssl
            ? Number(values?.paypal_validate_ssl)
            : undefined,
          sandbox: Number(Boolean(values?.sandbox)),
          demo: values?.demo ? Number(Boolean(values?.demo)) : undefined,
          sub_merchant_key: values?.sub_merchant_key?.length
            ? values?.sub_merchant_key
            : undefined,
        };
    setLoadingBtn(true);
    paymentPayloadService
      .create({
        payment_id: activePayment.value,
        payload,
      })
      .then(() => {
        const nextUrl = 'payment-payloads';
        toast.success(t('successfully.created'));
        batch(() => {
          dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
          dispatch(fetchPaymentPayloads({}));
          dispatch(setRefetch(activeMenu));
        });
        navigate(`/${nextUrl}`);
      })
      .catch((err) => {
        toast.dismiss();
        toast.error(err?.response?.data?.params?.payment_id[0]);
      })
      .finally(() => setLoadingBtn(false));
  };

  async function fetchPayment() {
    setLoading(true);
    return paymentService
      .getAll()
      .then(({ data }) => {
        const body = data
          .filter((item) => SUPPORTED_TAGS.includes(item.tag))
          .map((item) => ({
            label: item.tag[0].toUpperCase() + item.tag.substring(1),
            value: item.id,
            key: item.id,
          }));
        setPaymentList(body);
      })
      .finally(() => setLoading(false));
  }

  useEffect(() => {
    fetchPayment();
  }, []);

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
      case 'Flutter-wave':
        return <SiFlutter size={80} />;
      default:
        return null;
    }
  };

  const handleChangePayment = (e) => {
    const selectedPayment = paymentList.find((payment) => payment.value === e);
    switch (selectedPayment.label) {
      case 'Paypal': {
        form.setFieldsValue({
          paypal_currency: {
            label: defaultCurrency?.title,
            value: defaultCurrency?.id,
          },
          paypal_validate_ssl: true,
        });
        break;
      }
      case 'Stripe': {
        form.setFieldsValue({
          currency: defaultCurrency?.title,
        });
        break;
      }
      case 'Razorpay': {
        form.setFieldsValue({
          currency: defaultCurrency?.title,
        });
        break;
      }
      case 'Paystack': {
        form.setFieldsValue({
          currency: defaultCurrency?.title,
        });
        break;
      }
      case 'Flutter-wave': {
        form.setFieldsValue({
          currency: defaultCurrency?.title,
        });
        break;
      }
      case 'Mollie': {
        form.setFieldsValue({
          currency: defaultCurrency?.title,
        });
        break;
      }
      case 'Moya-sar': {
        form.setFieldsValue({
          currency: defaultCurrency?.title,
        });
        break;
      }
      case 'Paytabs': {
        form.setFieldsValue({
          currency: defaultCurrency?.title,
        });
        break;
      }
      case 'Zain-cash': {
        form.setFieldsValue({
          currency: defaultCurrency?.title,
        });
        break;
      }
      case 'Mercado-pago': {
        form.setFieldsValue({
          currency: defaultCurrency?.title,
          sandbox: true,
        });
        break;
      }
      case 'Maksekeskus': {
        form.setFieldsValue({
          demo: true,
          currency: defaultCurrency?.title,
        });
        break;
      }
      case 'Iyzico': {
        form.setFieldsValue({
          sandbox: true,
          currency: defaultCurrency?.title,
        });
        break;
      }
      case 'Pay-fast': {
        form.setFieldsValue({
          sandbox: true,
        });
        break;
      }
      case 'Payu': {
        form.setFieldsValue({
          sandbox: true,
        });
        break;
      }
      default:
        form.resetFields();
    }
    setActivePayment(selectedPayment);
  };

  return (
    <Card title={t('add.payment.payloads')} className='h-100'>
      <Form
        layout='vertical'
        name='user-address'
        form={form}
        onFinish={onFinish}
      >
        <Row gutter={12}>
          <Col
            span={
              activePayment?.label === 'Cash' ||
              activePayment?.label === 'Wallet'
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
                notFoundContent={loading ? <Spin size='small' /> : 'no results'}
                allowClear
                options={paymentList}
                onSelect={handleChangePayment}
              />
            </Form.Item>
          </Col>

          {isPlatformPayload(activePayment?.label?.toLowerCase()) && (
            <Col span={24}>
              <Alert
                type='info'
                showIcon
                message='Platform payment credentials'
                description='These credentials configure the platform gateway only; they do not configure Vendor-direct payments. Configuration readiness is separate from provider activation. Save this configuration before activating the provider; checkout remains disabled until the server reports it ready and active.'
                className='mb-3'
              />
            </Col>
          )}

          {activePayment?.label === 'Cash' ||
          activePayment?.label === 'Wallet' ? (
            ''
          ) : (
            <>
              <Col
                span={24}
                className='d-flex justify-content-center mt-4 mb-5'
              >
                {handleAddIcon(activePayment?.label)}
              </Col>
              {activePayment?.label === 'Paystack' ? (
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
                      rules={[{ required: true, message: t('required') }]}
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
              ) : activePayment?.label === 'Paypal' ? (
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
                        defaultValue={{
                          value: defaultCurrency.id,
                          label: defaultCurrency.title,
                        }}
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
                      <Switch defaultChecked />
                    </Form.Item>
                  </Col>
                  <Col span={12}>
                    <Form.Item
                      label={t('paypal.sandbox.client.id')}
                      name='paypal_sandbox_client_id'
                      dependencies={['paypal_mode']}
                      rules={[
                        ({ getFieldValue }) => ({
                          required: getFieldValue('paypal_mode') === 'sandbox',
                          message: t('required'),
                        }),
                      ]}
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
                          required: getFieldValue('paypal_mode') === 'sandbox',
                          message: t('required'),
                        }),
                      ]}
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
                          required: getFieldValue('paypal_mode') === 'live',
                          message: t('required'),
                        }),
                      ]}
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
                          required: getFieldValue('paypal_mode') === 'live',
                          message: t('required'),
                        }),
                      ]}
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
                    <Form.Item label='PayPal webhook ID' name='paypal_webhook_id'>
                      <Input.Password autoComplete='new-password' />
                    </Form.Item>
                  </Col>
                </>
              ) : activePayment?.label === 'Stripe' ? (
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
                      rules={[{ required: true, message: t('required') }]}
                    >
                      <Input.Password autoComplete='new-password' />
                    </Form.Item>
                  </Col>{' '}
                  <Col span={12}>
                    <Form.Item
                      label='Stripe webhook secret'
                      name='stripe_webhook_secret'
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
              ) : activePayment?.label === 'Razorpay' ? (
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
              ) : activePayment?.label === 'Flutter-wave' ? (
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
                      rules={[{ required: true, message: t('required') }]}
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
                        rules={[{ required: true, message: t('required') }]}
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
                    <Form.Item label={t('sandbox')} name='sandbox'>
                      <Switch defaultChecked={true} />
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
              ) : activePayment?.label === 'Pay-fast' ? (
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
              ) : activePayment?.label === 'Payu' ? (
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
    </Card>
  );
}
