import React, { useEffect, useState } from 'react';
import { Button, Card, Col, Form, Row, Select, Spin, Switch } from 'antd';
import { useTranslation } from 'react-i18next';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { removeFromMenu } from 'redux/slices/menu';
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

export default function PlatformPaymentConfigAdd() {
  const { t } = useTranslation();
  const [form] = Form.useForm();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [loading, setLoading] = useState(false);
  const [paymentList, setPaymentList] = useState([]);
  const [activeTag, setActiveTag] = useState(null);

  useEffect(() => {
    setLoading(true);
    paymentService
      .getAll()
      .then(({ data }) =>
        setPaymentList(
          data
            .filter((item) => SUPPORTED_TAGS.includes(item.tag))
            .map((item) => ({
              label: item.tag,
              value: item.id,
              tag: item.tag,
            })),
        ),
      )
      .finally(() => setLoading(false));
  }, []);

  const fetchCountry = ({ search, page }) =>
    countryService
      .get({ search: !!search?.length ? search : undefined, page })
      .then((res) =>
        res.data.map((country) => ({
          label: country?.translation?.title,
          value: country.id,
        })),
      );

  const onFinish = (values) => {
    setLoadingBtn(true);
    platformPaymentConfigService
      .create({
        ...values,
        country_id: values?.country_id?.value,
      })
      .then(() => {
        toast.success(t('successfully.created'));
        const nextUrl = 'platform-payment-configs';
        dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
        navigate(`/${nextUrl}`);
      })
      .finally(() => setLoadingBtn(false));
  };

  return (
    <SuperAdminRoute>
    <Card title={t('add.platform.payment.config')} className='h-100'>
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
                notFoundContent={
                  loading ? <Spin size='small' /> : 'no results'
                }
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
            <GatewayCredentialFields tag={activeTag} />
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
    </Card>
    </SuperAdminRoute>
  );
}
