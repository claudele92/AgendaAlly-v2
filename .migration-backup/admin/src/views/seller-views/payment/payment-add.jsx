import React, { useEffect, useRef, useState } from 'react';
import {
  Button,
  Card,
  Col,
  Form,
  Row,
  Select,
  Spin,
  Switch,
  Typography,
} from 'antd';
import { useTranslation } from 'react-i18next';
import { removeFromMenu, setRefetch } from '../../../redux/slices/menu';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { fetchSellerPayments } from '../../../redux/slices/payment';
import paymentService from '../../../services/seller/payment';
import { toast } from 'react-toastify';
import { useNavigate } from 'react-router-dom';
import GatewayCredentialFields from '../../../components/payment/gateway-credential-fields';
import { useSearchParams } from 'react-router-dom';

const hasValidPaymentContext = (context) =>
  Boolean(
    context &&
      context.valid === true &&
      Array.isArray(context.country_ids) &&
      context.country_ids.length > 0 &&
      context.transaction_currency,
  );

export default function SellerPaymentAdd() {
  const { t } = useTranslation();
  const [form] = Form.useForm();
  const [searchParams] = useSearchParams();
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [loading, setLoading] = useState(false);
  const [paymentList, setPaymentList] = useState([]);
  const [activePayment, setActivePayment] = useState(null);
  const [policy, setPolicy] = useState(null);
  const [locationType, setLocationType] = useState(
    Number(searchParams.get('location_type')) === 2 ? 2 : 1,
  );
  const [policyError, setPolicyError] = useState(null);
  const requestSequence = useRef(0);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const dispatch = useDispatch();
  const navigate = useNavigate();

  const onFinish = (values) => {
    if (
      !hasValidPaymentContext(policy?.context) ||
      policyError
    ) {
      return;
    }
    setLoadingBtn(true);
    paymentService
      .create({ ...values, location_type: locationType })
      .then(() => {
        const nextUrl = 'seller/payments';
        toast.success(t('successfully.created'));
        dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
        navigate(`/${nextUrl}`);
        dispatch(fetchSellerPayments());
        dispatch(setRefetch(activeMenu));
      })
      .catch((err) => {
        setPolicyError(err?.response?.data?.message || err?.message || t('unable.to.save.payment'));
      })
      .finally(() => setLoadingBtn(false));
  };

  async function fetchPayment() {
    const requestId = ++requestSequence.current;
    setLoading(true);
    setPolicyError(null);
    setPolicy(null);
    setPaymentList([]);
    setActivePayment(null);
    return paymentService
      .policy({ location_type: locationType })
      .then((response) => {
        if (requestId !== requestSequence.current) return null;
        const resolvedPolicy = response?.data || null;
        setPolicy(resolvedPolicy);
        if (
          !hasValidPaymentContext(resolvedPolicy?.context)
        ) {
          setPaymentList([]);
          return null;
        }
        return paymentService.allPayment({ location_type: locationType }).then((response) => {
          if (requestId !== requestSequence.current) return null;
          const availableIds = new Set(
            (resolvedPolicy?.methods || [])
              .filter(
                (method) =>
                  method.available_for_configuration && method.vendor_configurable,
              )
              .map((method) => method.id),
          );
          const methods = Array.isArray(response?.data) ? response.data : [];
          setPaymentList(
            methods
              .filter((item) => availableIds.has(item.id))
              .map((item) => ({
                label: (item.tag || '').toString().replace(/^./, (letter) => letter.toUpperCase()),
                value: item.id,
                key: item.id,
                tag: item.tag,
              })),
          );
        });
      })
      .catch((err) => {
        if (requestId !== requestSequence.current) return;
        setPolicy(null);
        setPaymentList([]);
        setPolicyError(err?.message || t('unable.to.load.payment.policy'));
      })
      .finally(() => {
        if (requestId === requestSequence.current) setLoading(false);
      });
  }

  useEffect(() => {
    fetchPayment();
  }, [locationType]);

  const directCollectionReady =
    hasValidPaymentContext(policy?.context);

  return (
    <Card title={t('add.payment')} className='h-100'>
      <Form
        layout='vertical'
        name='user-address'
        form={form}
        onFinish={onFinish}
        initialValues={{ status: true }}
      >
        {loading ? (
          <Typography.Paragraph type='secondary' role='status'>{t('loading')}</Typography.Paragraph>
        ) : policyError ? (
          <Typography.Paragraph type='danger' role='alert'>{policyError}</Typography.Paragraph>
        ) : !directCollectionReady ? (
          <div>
            <Typography.Paragraph type='warning' role='status'>
              {t('payment.merchant.configuration.requires.direct.collection', {
                defaultValue: 'Merchant settings require a valid business country and transaction currency. Saving settings does not activate Customer checkout.',
              })}
            </Typography.Paragraph>
            <Button onClick={() => navigate('/seller/payments')}>
              {t('payment.collection.settings', { defaultValue: 'Payment collection settings' })}
            </Button>
          </div>
        ) : (
        <>
        <Typography.Paragraph type='secondary'>
          {t('payment.transaction.type')}: {locationType === 1 ? t('products') : t('services')}
          {policy?.context?.country_ids?.length
            ? ` · ${t('business.country.ids')}: ${policy.context.country_ids.join(', ')}`
            : ''}
          {policy?.context?.transaction_currency
            ? ` · ${t('transaction.currency')}: ${policy.context.transaction_currency}`
            : ''}
        </Typography.Paragraph>
        <Form.Item label={t('payment.transaction.type')}>
          <Select
            value={locationType}
            disabled={loading || loadingBtn}
            onChange={(value) => {
              form.resetFields(['payment_id']);
              setActivePayment(null);
              setPolicy(null);
              setPolicyError(null);
              setPaymentList([]);
              setLoading(true);
              setLocationType(value);
            }}
            options={[
              { value: 1, label: t('products') },
              { value: 2, label: t('services') },
            ]}
          />
        </Form.Item>
        {paymentList.length === 0 ? (
          <Typography.Paragraph role='status'>
            {t('payment.no.vendor.methods.available', {
              defaultValue: 'No payment methods are currently available for Vendor configuration in this transaction context. Country assignment does not activate a provider.',
            })}
          </Typography.Paragraph>
        ) : <>
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
                onSelect={(value) => setActivePayment(paymentList.find((payment) => payment.value === value))}
              />
            </Form.Item>
          </Col>

          {['orange', 'mtn'].includes(activePayment?.tag) ? (
            <GatewayCredentialFields tag={activePayment.label.toLowerCase()} />
          ) : activePayment ? (
            <Col span={12}>
              <Typography.Text type='secondary'>
                {t('payment.no.additional.credentials.required')}
              </Typography.Text>
            </Col>
          ) : null}
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
        <div className='flex-grow-1 d-flex flex-column justify-content-end'>
          <div className='pb-5'>
            <Button
              type='primary'
              htmlType='submit'
              loading={loadingBtn}
              disabled={loadingBtn || loading || !!policyError || paymentList.length === 0}
            >
              {t('submit')}
            </Button>
          </div>
        </div>
        </>}
        </>
        )}
      </Form>
    </Card>
  );
}
