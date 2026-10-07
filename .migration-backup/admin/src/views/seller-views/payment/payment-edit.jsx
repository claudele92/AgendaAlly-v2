import React, { useEffect, useRef, useState } from 'react';
import { useNavigate, useParams, useSearchParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import {
  Button,
  Card,
  Col,
  Form,
  Row,
  Switch,
  Spin,
  Typography,
} from 'antd';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import {
  disableRefetch,
  removeFromMenu,
} from '../../../redux/slices/menu';
import { useTranslation } from 'react-i18next';
import paymentService from '../../../services/seller/payment';
import { fetchSellerPayments } from '../../../redux/slices/payment';
import GatewayCredentialFields from '../../../components/payment/gateway-credential-fields';

const hasValidPaymentContext = (context) =>
  Boolean(
    context &&
      context.valid === true &&
      Array.isArray(context.country_ids) &&
      context.country_ids.length > 0 &&
      context.transaction_currency,
  );

const SellerPaymentEdit = () => {
  const { t } = useTranslation();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const dispatch = useDispatch();
  const [form] = Form.useForm();
  const navigate = useNavigate();
  const { id } = useParams();
  const [searchParams] = useSearchParams();
  const locationType = Number(searchParams.get('location_type')) === 2 ? 2 : 1;
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [loading, setLoading] = useState(false);
  const [activePayment, setActivePayment] = useState(null);
  const [configured, setConfigured] = useState({});
  const [policy, setPolicy] = useState(null);
  const [policyAllowsEdit, setPolicyAllowsEdit] = useState(false);
  const [policyError, setPolicyError] = useState(null);
  const requestSequence = useRef(0);

  const loadPayment = (paymentId) => {
    const requestId = ++requestSequence.current;
    setLoading(true);
    setPolicyError(null);
    setPolicy(null);
    setActivePayment(null);
    setPolicyAllowsEdit(false);
    paymentService
      .policy({ location_type: locationType })
      .then((policyResponse) => {
        if (requestId !== requestSequence.current) return null;
        const resolvedPolicy = policyResponse?.data || null;
        setPolicy(resolvedPolicy);
        if (
          !hasValidPaymentContext(resolvedPolicy?.context)
        ) {
          return null;
        }
        return paymentService.getById(paymentId).then((paymentResponse) => {
        if (requestId !== requestSequence.current) return null;
        const data = paymentResponse?.data;
        setActivePayment({
          label: data?.payment?.tag,
          value: data?.payment?.id,
          tag: data?.payment?.tag,
        });
        const isConfigured = Boolean(data?.configured);
        setConfigured({
          client_id: data?.client_id_configured,
          merchant_key: data?.merchant_key_configured ?? isConfigured,
          subscription_key: data?.subscription_key_configured ?? isConfigured,
          api_user: data?.api_user_configured ?? isConfigured,
          api_key: data?.api_key_configured ?? isConfigured,
        });
        setPolicyAllowsEdit(
          Boolean(
            resolvedPolicy?.methods?.some(
              (method) =>
                method.id === data?.payment?.id &&
                method.available_for_configuration &&
                method.vendor_configurable,
            ),
          ),
        );
        form.setFieldsValue({
          status: data?.status,
          payment_id: data?.payment?.tag,
          target_environment: data?.target_environment,
          base_url: data?.base_url,
        });
        });
      })
      .catch((err) => {
        if (requestId !== requestSequence.current) return;
        setPolicy(null);
        setPolicyAllowsEdit(false);
        setPolicyError(err?.message || t('unable.to.load.payment.policy'));
      })
      .finally(() => {
        if (requestId === requestSequence.current) {
          setLoading(false);
          dispatch(disableRefetch(activeMenu));
        }
      });
  };

  const onFinish = (values) => {
    if (
      !hasValidPaymentContext(policy?.context) ||
      !policyAllowsEdit ||
      policyError
    ) {
      return;
    }
    setLoadingBtn(true);
    const bodyValues = { ...values };
    [
      'client_id',
      'merchant_key',
      'subscription_key',
      'api_user',
      'api_key',
      'target_environment',
    ].forEach((key) => {
      if (typeof bodyValues[key] === 'undefined' || bodyValues[key] === '') {
        delete bodyValues[key];
      }
    });
    const body = {
      ...bodyValues,
      payment_id: activePayment.value,
      location_type: locationType,
    };
    paymentService
      .update(id, body)
      .then(() => {
        const nextUrl = 'seller/payments';
        toast.success(t('successfully.updated'));
        dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
        navigate(`/${nextUrl}`);
        dispatch(fetchSellerPayments());
      })
      .catch((err) => {
        setPolicyError(err?.response?.data?.message || err?.message || t('unable.to.save.payment'));
      })
      .finally(() => setLoadingBtn(false));
  };

  useEffect(() => {
    loadPayment(id);
  }, [activeMenu.refetch, id, locationType]);

  const directCollectionReady =
    hasValidPaymentContext(policy?.context);

  return (
    <Card title={t('edit.payment')} className='h-100'>
      {!loading ? (
        <Form
          name='edit.payment'
          layout='vertical'
          onFinish={onFinish}
          form={form}
          initialValues={{ status: true }}
          className='d-flex flex-column h-100'
        >
          {policy?.context && (
            <Typography.Paragraph type='secondary'>
              {t('payment.transaction.type')}: {locationType === 1 ? t('products') : t('services')}
              {policy.context.country_ids?.length
                ? ` · ${t('business.country.ids')}: ${policy.context.country_ids.join(', ')}`
                : ''}
              {policy.context.transaction_currency
                ? ` · ${t('transaction.currency')}: ${policy.context.transaction_currency}`
                : ''}
            </Typography.Paragraph>
          )}
          {policyError && <Typography.Paragraph type='danger' role='alert'>{policyError}</Typography.Paragraph>}
          {!directCollectionReady && !policyError && (
            <Typography.Paragraph type='warning' role='status'>
              {t('payment.merchant.configuration.requires.direct.collection', {
                defaultValue: 'Merchant settings require a valid business country and transaction currency. Saving settings does not activate Customer checkout.',
              })}
            </Typography.Paragraph>
          )}
          {directCollectionReady && !policyAllowsEdit && !policyError && (
            <Typography.Paragraph type='warning' role='status'>
              {t('payment.configuration.unavailable.for.context', {
                defaultValue: 'This method is currently gated by payment policy and cannot be changed here.',
              })}
            </Typography.Paragraph>
          )}
          {(!directCollectionReady || policyError || !policyAllowsEdit) && (
            <Button onClick={() => navigate('/seller/payments')}>
              {t('payment.collection.settings', { defaultValue: 'Payment collection settings' })}
            </Button>
          )}
          {directCollectionReady && policyAllowsEdit && (
          <>
          <Row gutter={12}>
            <Col span={24}>
              <Typography.Text strong>
                {t('payment')}: {activePayment?.tag ? t(activePayment.tag) : ''}
              </Typography.Text>
            </Col>

            {['orange', 'mtn'].includes(activePayment?.tag) ? (
              <GatewayCredentialFields
                tag={activePayment.tag}
                isEdit
                configured={configured}
                disabled={!policyAllowsEdit}
                allowBlankTargetEnvironment
              />
            ) : activePayment?.tag ? (
              <Col span={24}>
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
                <Switch disabled={!policyAllowsEdit} />
              </Form.Item>
            </Col>
          </Row>
          <div className='flex-grow-1 d-flex flex-column justify-content-end'>
            <div className='pb-5'>
              <Button
                type='primary'
                htmlType='submit'
                loading={loadingBtn}
                disabled={loadingBtn || !policyAllowsEdit || !activePayment}
              >
                {t('submit')}
              </Button>
            </div>
          </div>
          </>
          )}
        </Form>
      ) : (
        <div className='d-flex justify-content-center align-items-center'>
          <Spin size='large' className='py-5' />
        </div>
      )}
    </Card>
  );
};

export default SellerPaymentEdit;
