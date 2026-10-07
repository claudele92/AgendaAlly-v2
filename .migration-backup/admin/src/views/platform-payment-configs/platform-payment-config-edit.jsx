import React, { useEffect, useState } from 'react';
import { Button, Card, Col, Form, Row, Select, Spin, Switch } from 'antd';
import { useTranslation } from 'react-i18next';
import { useParams, useNavigate } from 'react-router-dom';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { toast } from 'react-toastify';
import { disableRefetch, removeFromMenu } from 'redux/slices/menu';
import platformPaymentConfigService from 'services/platformPaymentConfig';
import paymentService from 'services/payment';
import countryService from 'services/deliveryzone/country';
import { InfiniteSelect } from 'components/infinite-select';
import GatewayCredentialFields from 'components/payment/gateway-credential-fields';
import { SuperAdminRoute } from 'context/superadmin-route';

// Platform payment configs have no equivalent of ShopPayment's
// secret_id/payment_key/merchant_email fields, so only Orange and MTN
// — the two gateways this model actually has fields for — are offered.
const SUPPORTED_TAGS = ['orange', 'mtn'];

export default function PlatformPaymentConfigEdit() {
  const { t } = useTranslation();
  const [form] = Form.useForm();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { id } = useParams();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [loading, setLoading] = useState(true);
  const [paymentList, setPaymentList] = useState([]);
  const [activeTag, setActiveTag] = useState(null);
  const [configured, setConfigured] = useState({});

  const fetchCountry = ({ search, page }) =>
    countryService
      .get({ search: !!search?.length ? search : undefined, page })
      .then((res) =>
        res.data.map((country) => ({
          label: country?.translation?.title,
          value: country.id,
        })),
      );

  useEffect(() => {
    setLoading(true);
    Promise.all([
      paymentService.getAll(),
      platformPaymentConfigService.getById(id),
    ])
      .then(([paymentsRes, configRes]) => {
        const list = paymentsRes.data
          .filter((item) => SUPPORTED_TAGS.includes(item.tag))
          .map((item) => ({ label: item.tag, value: item.id, tag: item.tag }));
        setPaymentList(list);

        const config = configRes.data;
        setActiveTag(config.payment?.tag);
        setConfigured({
          merchant_key: config.merchant_key_configured,
          subscription_key: config.subscription_key_configured,
          api_user: config.api_user_configured,
          api_key: config.api_key_configured,
        });
        form.setFieldsValue({
          status: config.status,
          client_id: config.client_id,
          payment_id: config.payment?.id,
          country_id: config.country
            ? { label: config.country.name, value: config.country.id }
            : undefined,
        });
      })
      .finally(() => setLoading(false));
    // eslint-disable-next-line
  }, [id]);

  const onFinish = (values) => {
    setLoadingBtn(true);
    platformPaymentConfigService
      .update(id, {
        ...values,
        country_id: values?.country_id?.value,
      })
      .then(() => {
        toast.success(t('successfully.updated'));
        const nextUrl = 'platform-payment-configs';
        dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
        dispatch(disableRefetch(activeMenu));
        navigate(`/${nextUrl}`);
      })
      .finally(() => setLoadingBtn(false));
  };

  return (
    <SuperAdminRoute>
    <Card title={t('edit.platform.payment.config')} className='h-100'>
      {!loading ? (
        <Form
          layout='vertical'
          form={form}
          onFinish={onFinish}
          initialValues={{ status: true }}
        >
          <Row gutter={12}>
            <Col span={12}>
              <Form.Item
                name='country_id'
                label={t('country_id')}
                rules={[{ required: true, message: t('required') }]}
              >
                <InfiniteSelect fetchOptions={fetchCountry} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item
                name='payment_id'
                label={t('payment')}
                rules={[{ required: true, message: t('required') }]}
              >
                <Select
                  allowClear
                  options={paymentList}
                  onSelect={(value) =>
                    setActiveTag(
                      paymentList.find((item) => item.value === value)?.tag,
                    )
                  }
                />
              </Form.Item>
            </Col>
            {SUPPORTED_TAGS.includes(activeTag) && (
              <GatewayCredentialFields
                tag={activeTag}
                isEdit
                configured={configured}
              />
            )}
            <Col span={12}>
              <Form.Item
                label={t('status')}
                name='status'
                valuePropName='checked'
              >
                <Switch />
              </Form.Item>
            </Col>
          </Row>
          <Button type='primary' htmlType='submit' loading={loadingBtn}>
            {t('submit')}
          </Button>
        </Form>
      ) : (
        <div className='d-flex justify-content-center align-items-center'>
          <Spin size='large' className='py-5' />
        </div>
      )}
    </Card>
    </SuperAdminRoute>
  );
}
