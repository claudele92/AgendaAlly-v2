import React, { useCallback, useContext, useEffect, useRef, useState } from 'react';
import { Alert, Button, Divider, Radio, Space, Table, Typography } from 'antd';
import { useTranslation } from 'react-i18next';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import { addMenu, disableRefetch } from 'redux/slices/menu';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import CustomModal from 'components/modal';
import { toast } from 'react-toastify';
import { Context } from 'context/context';
import { fetchSellerPayments } from 'redux/slices/payment';
import paymentService from 'services/seller/payment';
import shopService from 'services/seller/shop';
import { useNavigate } from 'react-router-dom';
import FilterColumns from 'components/filter-column';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';
import OutlinedButton from 'components/outlined-button';
import ProviderStateSummary from 'components/payment/provider-state-summary';

const hasValidPaymentContext = (context) =>
  Boolean(
    context &&
      context.valid === true &&
      Array.isArray(context.country_ids) &&
      context.country_ids.length > 0 &&
      context.transaction_currency,
  );

const getEligibleVendorMethods = (policy) =>
  (policy?.methods || []).filter((method) => {
    return (
      method.vendor_configurable &&
      method.available_for_configuration
    );
  });

const getResolverEligibleMethods = (policy) =>
  (policy?.methods || []).filter((method) => method.eligible === true);

const paymentMethodLabel = (method, t) => {
  const tag = (method?.tag || '').toString();
  const fallback = method?.title || method?.name || tag.replace(/[_-]+/g, ' ');
  return t(tag, {
    defaultValue: fallback || t('payment.method', { defaultValue: 'Payment method' }),
  });
};

export default function SellerPayment() {
  const { t } = useTranslation();
  const { setIsModalVisible } = useContext(Context);
  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [policy, setPolicy] = useState(null);
  const [policyContexts, setPolicyContexts] = useState({ product: null, service: null });
  const [collectionDraft, setCollectionDraft] = useState(null);
  const [policyLoading, setPolicyLoading] = useState(false);
  const [policyError, setPolicyError] = useState(null);
  const [collectionSaving, setCollectionSaving] = useState(false);
  const [collectionError, setCollectionError] = useState(null);
  const [policyReloadCount, setPolicyReloadCount] = useState(0);
  const dispatch = useDispatch();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const navigate = useNavigate();
  const [text, setText] = useState(null);
  const { payments, loading } = useSelector(
    (state) => state.payment,
    shallowEqual,
  );
  const policyRef = useRef(policy);
  policyRef.current = policy;
  const policyContextsRef = useRef(policyContexts);
  policyContextsRef.current = policyContexts;
  const policyRequestSequence = useRef(0);
  const getPolicyForType = (type) =>
    Number(type) === 2
      ? policyContextsRef.current.service
      : policyContextsRef.current.product;
  const [columns, setColumns] = useState([
    {
      title: t('id'),
      is_show: true,
      dataIndex: 'id',
      key: 'id',
    },
    {
      title: t('title'),
      is_show: true,
      dataIndex: 'title',
      key: 'title',
      render: (title, row) => paymentMethodLabel(row?.payment, t),
    },
    {
      title: t('actions'),
      key: 'actions',
      dataIndex: 'actions',
      is_show: true,
      render: (data, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            disabled={
              !getEligibleVendorMethods(getPolicyForType(row?.location_type)).some(
                (method) =>
                  method.id === (row?.payment?.id || row?.payment_id),
              )
            }
            onClick={(e) => {
              e.stopPropagation();
              goToEdit(row);
            }}
          >
            <EditOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            onClick={() => {
              setId([row.id]);
              setIsModalVisible(true);
              setText(true);
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ]);

  const goToEdit = (row) => {
    if (!policyRef.current?.collection?.can_manage) return;
    const rowLocationType = Number(row?.location_type) === 2 ? 2 : 1;
    const selectedPolicy = getPolicyForType(rowLocationType);
    if (
      !hasValidPaymentContext(selectedPolicy?.context)
    ) return;
    dispatch(
      addMenu({
        url: `seller/payments/${row.id}?location_type=${rowLocationType}`,
        id: 'payments_edit',
        name: t('edit.payments'),
      }),
    );
    navigate(`/seller/payments/${row.id}?location_type=${rowLocationType}`);
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchSellerPayments({}));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  const loadPolicyContexts = useCallback(async () => {
    const requestId = ++policyRequestSequence.current;
    setPolicyLoading(true);
    setPolicyError(null);
    try {
      const [shopResponse, productResponse, serviceResponse] = await Promise.all([
        paymentService.policy(),
        paymentService.policy({ location_type: 1 }),
        paymentService.policy({ location_type: 2 }),
      ]);
      if (requestId !== policyRequestSequence.current) return null;
      const nextContexts = {
        product: productResponse?.data || null,
        service: serviceResponse?.data || null,
      };
      setPolicyContexts(nextContexts);
      if (shopResponse?.data?.collection?.scope !== 'shop') {
        setPolicy(null);
        setPolicyError(
          t('payment.shop.policy.unavailable', {
            defaultValue:
              'A shop-wide payment collection policy could not be loaded. Please retry.',
          }),
        );
        return null;
      }
      const nextPolicy = shopResponse.data;
      setPolicy(nextPolicy);
      if (nextPolicy?.collection?.mode) {
        setCollectionDraft(nextPolicy.collection.mode);
      }
      dispatch(fetchSellerPayments({}));
      return nextPolicy;
    } catch {
      if (requestId === policyRequestSequence.current) {
        setPolicyError(
          t('payment.policy.load.failed', {
            defaultValue: 'Payment settings could not be loaded. Please try again.',
          }),
        );
      }
      return null;
    } finally {
      if (requestId === policyRequestSequence.current) setPolicyLoading(false);
    }
  }, [dispatch, t]);

  useEffect(() => {
    void loadPolicyContexts();
    return () => {
      policyRequestSequence.current += 1;
    };
  }, [activeMenu.refetch, loadPolicyContexts, policyReloadCount]);

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;
    dispatch(fetchSellerPayments({ perPage: pageSize, page: current }));
  };

  const paymentDelete = () => {
    if (!policyRef.current?.collection?.can_manage) return;
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };
    paymentService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        dispatch(fetchSellerPayments({}));
        setIsModalVisible(false);
        setText(null);
      })
      .finally(() => setLoadingBtn(false));
  };

  const onSelectChange = (newSelectedRowKeys) => {
    setId(newSelectedRowKeys);
  };

  const rowSelection = {
    id,
    onChange: onSelectChange,
  };

  const allDelete = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.payment'));
    } else {
      setIsModalVisible(true);
      setText(false);
    }
  };

  const goToAdd = (preferredLocationType) => {
    if (!policyRef.current?.collection?.can_manage) return;
    const chosenType =
      Number(preferredLocationType) === 2
        ? 2
        : getEligibleVendorMethods(policyContextsRef.current.product).length
          ? 1
          : 2;
    const selectedPolicy = getPolicyForType(chosenType);
    if (
      !hasValidPaymentContext(selectedPolicy?.context) ||
      !getEligibleVendorMethods(selectedPolicy).length
    ) return;
    dispatch(
      addMenu({
        id: 'add.payment',
        url: `seller/payments/add?location_type=${chosenType}`,
        name: t('add.payment'),
      }),
    );
    navigate(`/seller/payments/add?location_type=${chosenType}`);
  };

  const collectionMode = policy?.collection?.mode;
  const draftMode = collectionDraft || collectionMode;
  const vendorDirectState = policy?.collection?.vendor_direct_state;
  const productVendorMethods = getEligibleVendorMethods(policyContexts.product);
  const serviceVendorMethods = getEligibleVendorMethods(policyContexts.service);
  const hasApplicablePaymentContext =
    hasValidPaymentContext(policyContexts.product?.context) ||
    hasValidPaymentContext(policyContexts.service?.context);
  const availableForConfiguration =
    !policyLoading
      ? [...productVendorMethods, ...serviceVendorMethods].filter(
          (method, index, methods) =>
            methods.findIndex((candidate) => candidate.id === method.id) === index,
        )
      : [];
  const canSelectPlatform =
    !policyLoading &&
    !collectionSaving &&
    hasApplicablePaymentContext &&
    policy?.collection?.scope === 'shop' &&
    policy?.collection?.applies_to?.includes('product') &&
    policy?.collection?.applies_to?.includes('booking') &&
    policy?.collection?.platform_available === true &&
    policy?.collection?.can_manage === true;
  const canSelectVendorDirect =
    canSelectPlatform &&
    hasApplicablePaymentContext &&
    (vendorDirectState === 'setup_required' || vendorDirectState === 'ready');
  const appliesToLabels = Array.isArray(policy?.collection?.applies_to)
    ? policy.collection.applies_to
        .map((target) =>
          target === 'product'
            ? t('products', { defaultValue: 'Products' })
            : target === 'booking'
              ? t('service.bookings', { defaultValue: 'Service bookings' })
              : null,
        )
        .filter(Boolean)
    : [];
  const shopPolicyIsComplete =
    policy?.collection?.scope === 'shop' &&
    policy.collection.applies_to?.includes('product') &&
    policy.collection.applies_to?.includes('booking');

  const saveCollectionMode = async () => {
    if (
      !policy?.collection ||
      !shopPolicyIsComplete ||
      !draftMode ||
      draftMode === collectionMode ||
      collectionSaving ||
      (draftMode === 'vendor_direct' && !canSelectVendorDirect)
    ) return;
    setCollectionError(null);
    setCollectionSaving(true);
    try {
      await shopService.setCollectViaPlatform({ collection_mode: draftMode });
      const confirmedPolicy = await loadPolicyContexts();
      if (confirmedPolicy?.collection?.mode !== draftMode) {
        setCollectionError(
          t('payment.collection.save.not.confirmed', {
            defaultValue:
              'The requested setting was not confirmed for both Products and Services. Your saved choice is shown above.',
          }),
        );
        return;
      }
      toast.success(
        t('payment.collection.saved', { defaultValue: 'Payment settings saved.' }),
      );
    } catch {
      setCollectionDraft(collectionMode);
      setCollectionError(
          t('payment.collection.save.failed', {
            defaultValue:
              'Could not save payment settings. Your previous saved choice remains active.',
          }),
      );
    } finally {
      setCollectionSaving(false);
    }
  };

  return (
    <Card>
      <Space className='align-items-center justify-content-between w-100'>
        <Typography.Title
          level={1}
          style={{
            color: 'var(--text)',
            fontSize: '20px',
            fontWeight: 500,
            padding: 0,
            margin: 0,
          }}
        >
          {t('payments')}
        </Typography.Title>
        {availableForConfiguration.length > 0 && (
          <Button
            type='primary'
            icon={<PlusOutlined />}
            onClick={() => goToAdd()}
            disabled={policyLoading || !!policyError || !policy?.collection?.can_manage}
            style={{ width: '100%' }}
          >
            {t('add.payment')}
          </Button>
        )}
      </Space>
      <Divider color='var(--divider)' />
      <section aria-labelledby='payment-collection-title' className='mb-4'>
        <Typography.Title level={4} id='payment-collection-title'>
          {t('payment.collection', { defaultValue: 'Payment collection' })}
        </Typography.Title>
        <Typography.Paragraph type='secondary'>
          {t('payment.collection.explanation', {
            defaultValue:
              'Choose who collects payments from customers across this shop. This does not confirm that a payment was received or that payouts are available. Payout methods are separate.',
          })}
        </Typography.Paragraph>
        {collectionError && (
          <Space direction='vertical' className='mb-3' align='start'>
            <Alert type='error' showIcon message={collectionError} />
            <Button
              onClick={() => {
                setCollectionError(null);
                setPolicyReloadCount((count) => count + 1);
              }}
            >
              {t('retry', { defaultValue: 'Retry' })}
            </Button>
          </Space>
        )}
        {policyLoading ? (
          <Typography.Paragraph type='secondary' role='status'>
            {t('loading')}
          </Typography.Paragraph>
        ) : policyError ? (
          <Space direction='vertical' align='start'>
            <Alert type='error' showIcon message={policyError} />
            <Button onClick={() => setPolicyReloadCount((count) => count + 1)}>
              {t('retry', { defaultValue: 'Retry' })}
            </Button>
          </Space>
        ) : !policy?.collection || policy.collection.scope !== 'shop' ? (
          <Alert
            type='warning'
            showIcon
            message={t('payment.shop.policy.unavailable', {
              defaultValue:
                'A shop-wide payment collection policy is not available. Settings cannot be changed here.',
            })}
          />
        ) : (
          <>
            <Alert
              className='mb-3'
              type='info'
              showIcon
              message={
                appliesToLabels.length
                  ? t('payment.collection.scope.shop', {
                      domains: appliesToLabels.join(' and '),
                      defaultValue: `One shop setting applies to ${appliesToLabels.join(' and ')}.`,
                    })
                  : t('payment.shop.policy.unavailable', {
                      defaultValue:
                        'The transaction contexts for this shop setting are not available.',
                    })
              }
            />
            {!shopPolicyIsComplete && (
              <Alert
                className='mb-3'
                type='warning'
                showIcon
                message={t('payment.shop.policy.incomplete', {
                  defaultValue:
                    'Payment settings cannot be changed until the shop policy includes product and booking scope.',
                })}
              />
            )}
            <Radio.Group
              value={draftMode}
              onChange={(event) => setCollectionDraft(event.target.value)}
              className='d-flex flex-column'
              aria-label={t('payment.collection', { defaultValue: 'Payment collection' })}
            >
              <Radio value='platform' disabled={!canSelectPlatform}>
                <Space direction='vertical' size={2}>
                  <Typography.Text strong>
                    {t('agendaally.payments', { defaultValue: 'AgendaAlly Payments' })}
                    {' · '}
                    {t('recommended', { defaultValue: 'Recommended' })}
                  </Typography.Text>
                  <Typography.Text type='secondary'>
                    {t('payment.collection.platform.description', {
                      defaultValue:
                        'Customers pay through methods available from AgendaAlly for their product order or service booking.',
                    })}
                  </Typography.Text>
                </Space>
              </Radio>
              <Radio value='vendor_direct' disabled={!canSelectVendorDirect}>
                <Space direction='vertical' size={2}>
                  <Typography.Text strong>
                    {t('use.my.own.payment.gateway', {
                      defaultValue: 'Use my own payment gateway',
                    })}
                  </Typography.Text>
                  <Typography.Text type='secondary'>
                    {t('payment.collection.vendor.direct.description', {
                      defaultValue:
                        'Customers pay through an eligible merchant gateway connected to this shop.',
                    })}
                  </Typography.Text>
                </Space>
              </Radio>
            </Radio.Group>
            {(vendorDirectState === 'setup_required' || collectionMode === 'platform') && (
              <Alert
                className='mt-3'
                type='warning'
                showIcon
                message={t('payment.collection.setup.required', {
                  defaultValue:
                    'Merchant gateway setup is required. Eligibility is still checked separately for each available product or booking context.',
                })}
                action={
                  <Space wrap>
                    {productVendorMethods.length > 0 && (
                       <Button size='small' disabled={!policy?.collection?.can_manage} onClick={() => goToAdd(1)}>
                        {t('set.up.product.gateway', {
                          defaultValue: 'Set up product gateway',
                        })}
                      </Button>
                    )}
                    {serviceVendorMethods.length > 0 && (
                       <Button size='small' disabled={!policy?.collection?.can_manage} onClick={() => goToAdd(2)}>
                        {t('set.up.service.gateway', {
                          defaultValue: 'Set up booking gateway',
                        })}
                      </Button>
                    )}
                  </Space>
                }
              />
            )}
            {vendorDirectState === 'ready' && (
              <Alert
                className='mt-3'
                type='success'
                showIcon
                message={t('payment.collection.direct.ready', {
                  defaultValue:
                    'Merchant runtime configuration is complete. Customer checkout remains disabled until separately activated.',
                })}
              />
            )}
            {vendorDirectState === 'unavailable' && (
              <Alert
                className='mt-3'
                type='info'
                showIcon
                message={t('payment.collection.vendor.unavailable', {
                  defaultValue:
                    'Merchant-direct collection is unavailable for this shop. AgendaAlly Payments remains available where supported.',
                })}
              />
            )}
            {(policy?.methods || []).filter((method) => ['mtn', 'orange'].includes(method.tag)).map((method) => (
              <div key={`gateway-reason-${method.tag}`} className='mt-2'>
                <Typography.Text strong>{t(method.tag)} — own merchant configuration</Typography.Text>
                <ProviderStateSummary state={method.vendor_direct || method} />
              </div>
            ))}
            {policy.collection.vendor_direct_unavailable_reason === 'shop_wide_merchant_currency_conflict' && (
              <Typography.Paragraph type='secondary'>
                Products and Service bookings use different charge currencies. Native MTN/Orange merchant profiles store one currency per provider, so shop-wide merchant collection cannot be enabled.
              </Typography.Paragraph>
            )}
            {!policy.collection.can_manage && (
              <Typography.Paragraph type='secondary' className='mt-2'>
                {t('payment.collection.cannot.manage', {
                  defaultValue: 'Collection settings are read-only for this account.',
                })}
              </Typography.Paragraph>
            )}
            {policy.collection.can_manage && (
              <Button
                type='primary'
                className='mt-3'
                loading={collectionSaving}
                disabled={
                  !draftMode ||
                  !shopPolicyIsComplete ||
                  draftMode === collectionMode ||
                  (draftMode === 'vendor_direct' && !canSelectVendorDirect)
                }
                onClick={saveCollectionMode}
              >
                {t('save.payment.settings', { defaultValue: 'Save payment settings' })}
              </Button>
            )}
          </>
        )}
      </section>
      {!policyLoading && !policyError && policyContexts.product && policyContexts.service && (
        <div className='mb-3' aria-labelledby='payment-methods-title'>
          <Typography.Title level={5} id='payment-methods-title'>
            {t('available.payment.methods', { defaultValue: 'Available payment methods' })}
          </Typography.Title>
          <Typography.Paragraph type='secondary'>
            {t('payment.methods.contexts.note', {
              defaultValue:
                'Eligibility is checked separately for each transaction context.',
            })}
          </Typography.Paragraph>
          <div className='row g-3'>
            {[
              {
                key: 'product',
                label: t('products', { defaultValue: 'Products' }),
                value: policyContexts.product,
              },
              {
                key: 'service',
                label: t('service.bookings', { defaultValue: 'Service bookings' }),
                value: policyContexts.service,
              },
            ].map((context) => {
              const eligibleMethods = getResolverEligibleMethods(context.value);
              const merchantMethods = getEligibleVendorMethods(context.value);
              const directReady = merchantMethods.filter(
                (method) => method.vendor_direct_ready === true,
              );
              const directSetupRequired = merchantMethods.filter(
                (method) => method.vendor_direct_ready !== true,
              );
              return (
                <div className='col-12 col-lg-6' key={context.key}>
                  <div className='border rounded p-3 h-100'>
                    <Typography.Text strong>{context.label}</Typography.Text>
                    <div className='mt-2'>
                      <Typography.Text type='secondary'>
                        {t('payment.methods.current.route', {
                          defaultValue: 'Eligible for the saved collection choice',
                        })}
                        :{' '}
                        {eligibleMethods.length
                          ? eligibleMethods
                              .map((method) => paymentMethodLabel(method, t))
                              .join(', ')
                          : t('payment.methods.none.available', {
                              defaultValue: 'No methods currently available.',
                            })}
                      </Typography.Text>
                    </div>
                    <div className='mt-2'>
                      <Typography.Text type='secondary'>
                        {t('merchant.gateway.setup', {
                          defaultValue: 'Merchant gateway setup',
                        })}
                        :{' '}
                        {merchantMethods.length
                          ? [
                              ...directReady.map(
                                (method) =>
                                  `${paymentMethodLabel(method, t)} — ${t('ready', {
                                    defaultValue: 'Ready',
                                  })}`,
                              ),
                              ...directSetupRequired.map(
                                (method) =>
                                  `${paymentMethodLabel(method, t)} — ${t(
                                    'payment.gateway.setup.required',
                                    { defaultValue: 'Setup required' },
                                  )}`,
                              ),
                            ].join(', ')
                          : vendorDirectState === 'setup_required'
                            ? t('payment.gateway.setup.required', {
                                defaultValue: 'Setup required',
                              })
                            : t('payment.methods.none.available', {
                                defaultValue: 'No methods currently available.',
                              })}
                      </Typography.Text>
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}
      {hasApplicablePaymentContext &&
      !policyLoading &&
      !policyError && (
      <>
      <Space
        className='w-100 justify-content-end align-items-center'
        style={{ rowGap: '6px', columnGap: '6px', marginBottom: '20px' }}
      >
        <OutlinedButton onClick={allDelete} color='red'>
          {t('delete.selection')}
        </OutlinedButton>
        <FilterColumns columns={columns} setColumns={setColumns} />
      </Space>
      <Table
        key={`${collectionMode}-${policyLoading}`}
        scroll={{ x: true }}
        rowSelection={rowSelection}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={payments}
        rowKey={(record) => record.id}
        onChange={onChangePagination}
        loading={loading}
      />
      </>
      )}
      {hasApplicablePaymentContext &&
      !policyLoading &&
      !policyError && (
        <CustomModal
          click={paymentDelete}
          text={text ? t('delete') : t('all.delete')}
          loading={loadingBtn}
          setText={setId}
        />
      )}
    </Card>
  );
}
