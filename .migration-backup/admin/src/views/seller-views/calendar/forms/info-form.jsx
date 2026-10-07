import React, { useContext, useEffect, useMemo, useRef, useState } from 'react';
import { t } from 'i18next';
import { PlusOutlined } from '@ant-design/icons';
import {
  Alert,
  Button,
  Col,
  Form,
  Input,
  message,
  Modal,
  Row,
  Select,
  Spin,
  Typography,
} from 'antd';
import debounce from 'lodash/debounce';
import userService from 'services/seller/user';
import paymentService from 'services/rest/payment';
import ServiceCard from '../components/service-card';
import { BookingContext } from '../provider';
import { downloadInvoice } from '../helpers/index';
import BookingBranchSelect from 'components/booking-branch-select';
import {
  bookingClientFromCreateResponse,
  bookingClientsFromResponse,
} from '../helpers/booking-client-response.mjs';
import {
  clearClientSaveIntent,
  clientSavePayload,
  clientSaveIntentStorage,
  createClientSaveIntentId,
  readClientSaveIntent,
  writeClientSaveIntent,
} from '../helpers/client-save-intent.mjs';
const { Title } = Typography;

const InfoFormItems = ({
  isDisabled,
  title = 'new.booking',
  isAdd = false,
  shopLocations,
}) => {
  const {
    calculatedData,
    setViewContent,
    setInfoData,
    infoForm,
    service_id,
    clientSaveIntent,
    setClientSaveIntent,
    clientSaveIntentScope,
    clientSaveIntentRecoveryError,
    setClientSaveIntentRecoveryError,
  } = useContext(BookingContext);

  const [downloadInvoiceModal, setDownloadInvoiceModal] = useState(false);
  const [clientOptions, setClientOptions] = useState([]);
  const [cashPayment, setCashPayment] = useState(null);
  const [cashPaymentError, setCashPaymentError] = useState('');
  const [cashPaymentLoading, setCashPaymentLoading] = useState(false);
  const clientSearchSequence = useRef(0);
  const [clientLoading, setClientLoading] = useState(false);
  const [clientError, setClientError] = useState('');
  const [clientCreateLoading, setClientCreateLoading] = useState(false);
  const [isNewClientOpen, setIsNewClientOpen] = useState(false);
  const [newClientForm] = Form.useForm();
  const clientSaveAttempt = useRef(false);
  const selectedBranch = Form.useWatch('shop_location_id', infoForm);
  const selectedClient = Form.useWatch('client', infoForm);
  const isRegisteredClient = selectedClient?.value?.startsWith('registered:');
  useEffect(() => {
    if (!isAdd) return undefined;
    let current = true;
    setCashPaymentLoading(true);
    setCashPaymentError('');
    paymentService.getAll().then((response) => {
      const cash = response?.data?.filter((item) =>
        item.tag === 'cash' && item.active === true && Number.isInteger(item.id) && item.id > 0);
      if (cash?.length !== 1) throw new Error('Cash is unavailable in the current payment catalog.');
      if (current) setCashPayment(cash[0]);
    }).catch(() => {
      if (current) {
        setCashPayment(null);
        setCashPaymentError('Cash selection is unavailable. Close and reopen this booking to retry.');
      }
    }).finally(() => {
      if (current) setCashPaymentLoading(false);
    });
    return () => { current = false; };
  }, [isAdd]);
  const serviceLocations = (shopLocations || []).filter(
    (location) => location.type === 2,
  );
  const storage = clientSaveIntentStorage();
  const attachClient = (client, reusedExisting = false) => {
    if (!client?.id || !client?.kind || !client?.name) {
      throw new Error('The new client response was incomplete.');
    }
    const value = `${client.kind}:${client.id}`;
    const phoneOrEmail = client.phone || client.email;
    infoForm.setFieldsValue({
      client: {
        value,
        key: value,
        label: `${client.name}${phoneOrEmail ? ` · ${phoneOrEmail}` : ''}`,
      },
      payment_id: client.kind === 'local' ? undefined : infoForm.getFieldValue('payment_id'),
    });
    setClientOptions((previous) => [
      {
        label: `${client.name}${phoneOrEmail ? ` · ${phoneOrEmail}` : ''}`,
        value,
        key: value,
      },
      ...previous.filter((option) => option.value !== value),
    ]);
    if (reusedExisting) message.info('An existing authorized client was selected.');
    newClientForm.resetFields();
    setIsNewClientOpen(false);
  };

  useEffect(() => {
    if (clientSaveIntent?.state !== 'resolved' || !clientSaveIntent.client) return;
    try {
      if (clientSaveIntent.recovered) {
        const client = clientSaveIntent.client;
        const value = `${client.kind}:${client.id}`;
        const phoneOrEmail = client.phone || client.email;
        setClientOptions((previous) => [
          {
            label: `${client.name}${phoneOrEmail ? ` · ${phoneOrEmail}` : ''}`,
            value,
            key: value,
          },
          ...previous.filter((option) => option.value !== value),
        ]);
        message.info('Your earlier directory save is confirmed. Select the client when you are ready to continue.');
      } else {
        attachClient(clientSaveIntent.client, clientSaveIntent.reusedExisting);
      }
      setClientSaveIntent(null);
      setClientSaveIntentRecoveryError('');
    } catch (error) {
      setClientError(error.message);
    }
    // A resolved provider intent is consumed exactly once.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [clientSaveIntent]);

  const loadClients = async (search = '') => {
    const sequence = ++clientSearchSequence.current;
    setClientLoading(true);
    setClientError('');
    try {
      const response = await userService.bookingClients({
        search,
        perPage: 10,
        shop_location_id: selectedBranch || undefined,
      });
      const clients = bookingClientsFromResponse(response);
      if (sequence !== clientSearchSequence.current) return;
      if (!Array.isArray(clients)) {
        throw new Error('Client search returned an invalid response.');
      }
      setClientOptions(
        clients.map((client) => {
          const phoneOrEmail = client.phone || client.email;
          const detail = phoneOrEmail ? ` · ${phoneOrEmail}` : '';
          return {
            label: `${client.name}${detail}`,
            value: `${client.kind}:${client.id}`,
            key: `${client.kind}:${client.id}`,
          };
        }),
      );
    } catch (error) {
      if (sequence !== clientSearchSequence.current) return;
      setClientOptions([]);
      setClientError(
        error?.response?.data?.message ||
          error?.message ||
          'Unable to search existing clients.',
      );
    } finally {
      if (sequence === clientSearchSequence.current) setClientLoading(false);
    }
  };
  const debouncedLoadClients = useMemo(
    () => debounce((search) => loadClients(search), 350),
    [selectedBranch],
  );

  useEffect(() => {
    loadClients('');
    return () => debouncedLoadClients.cancel();
    // The search callback is intentionally stable for this mounted form.
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedBranch]);

  const createClient = async (values) => {
    if (clientSaveAttempt.current || clientSaveIntent?.state === 'sending') {
      setClientError('A client save is already in progress. Wait for it to resolve before retrying.');
      return;
    }
    clientSaveAttempt.current = true;
    setClientError('');
    setClientCreateLoading(true);
    let intent = clientSaveIntent;
    try {
      if (!clientSaveIntentScope?.storageKey) {
        throw new Error('Verified Shop context is required before saving a local client.');
      }
      const storedIntentId = readClientSaveIntent(storage, clientSaveIntentScope.storageKey);
      if (!intent && storedIntentId) {
        setClientSaveIntent({ intentId: storedIntentId, state: 'needs-recovery', payload: null });
        setClientSaveIntentRecoveryError('A previous save needs a status check before another client can be saved.');
        throw new Error('Confirm the previous client save before starting another.');
      }
      const payload = intent?.payload || clientSavePayload(values, selectedBranch);
      if (['needs-recovery', 'recovering', 'conflict'].includes(intent?.state)) {
        throw new Error('Confirm the previous client save before starting another.');
      }
      if (!intent || intent.state === 'rejected' || intent.state === 'resolved') {
        const intentId = createClientSaveIntentId();
        intent = { intentId, payload, state: 'sending' };
        if (!writeClientSaveIntent(storage, clientSaveIntentScope.storageKey, intentId)) {
          intent = null;
          throw new Error('This browser could not retain the safe retry reference. No request was sent.');
        }
        setClientSaveIntent(intent);
      } else {
        intent = { ...intent, payload, state: 'sending' };
        setClientSaveIntent(intent);
      }
      const response = await userService.createBookingClient({
        ...payload,
        client_save_intent: intent.intentId,
      });
      const { client, reusedExisting } =
        bookingClientFromCreateResponse(response);
      attachClient(client, reusedExisting);
      if (values.shop_location_id) {
        infoForm.setFieldsValue({ shop_location_id: values.shop_location_id });
      }
      clearClientSaveIntent(storage, clientSaveIntentScope?.storageKey, intent.intentId);
      setClientSaveIntent(null);
      setClientSaveIntentRecoveryError('');
    } catch (error) {
      const status = Number(error?.response?.status);
      if (intent?.intentId && status === 422) {
        clearClientSaveIntent(storage, clientSaveIntentScope?.storageKey, intent.intentId);
        setClientSaveIntent(null);
      } else if (intent?.intentId && status === 409) {
        setClientSaveIntent({ ...intent, state: 'conflict' });
        setClientSaveIntentRecoveryError(
          'The save reference has a payload conflict or unavailable result. No new client will be created under another reference.',
        );
      } else if (intent?.intentId) {
        setClientSaveIntent({ ...intent, state: 'uncertain' });
      }
      setClientError(
        status === 422
          ? error?.response?.data?.message || 'The client was not saved. Correct the details and try again.'
          : status === 409
            ? error?.response?.data?.message || 'This save reference cannot safely create another client. Check its status or ask your Shop administrator.'
          : intent?.intentId
            ? 'Save not confirmed. Your client details are held in this session. Retry the same client save; do not start a second save.'
            : error?.message || 'Unable to start a safe client save.',
      );
    } finally {
      clientSaveAttempt.current = false;
      setClientCreateLoading(false);
    }
  };

  const retryClientSaveStatus = async () => {
    if (!clientSaveIntent?.intentId) return;
    try {
      const response = await userService.bookingClientSaveIntentStatus(clientSaveIntent.intentId);
      const result = response || {};
      const { client, reusedExisting } = bookingClientFromCreateResponse(result);
      if (result.save_status === 'resolved' && client?.id && client?.kind && client?.name) {
        clearClientSaveIntent(storage, clientSaveIntentScope?.storageKey, clientSaveIntent.intentId);
        attachClient(client, reusedExisting);
        setClientSaveIntent(null);
        setClientSaveIntentRecoveryError('');
      } else if (result.save_status === 'not_found') {
        const hasOriginalPayload = Boolean(clientSaveIntent.payload);
        setClientSaveIntent({
          ...clientSaveIntent,
          state: hasOriginalPayload ? 'uncertain' : 'reentry',
          payload: hasOriginalPayload ? clientSaveIntent.payload : null,
        });
        setClientSaveIntentRecoveryError(
          hasOriginalPayload
            ? 'No receipt was found; the save may still be in flight. Retry the same save with its original details.'
            : 'No receipt was found; the save may still be in flight. Re-enter the original details and retry this same save reference.',
        );
      } else {
        setClientSaveIntent({ ...clientSaveIntent, state: 'needs-recovery' });
        setClientSaveIntentRecoveryError('The save is not yet confirmed. No new client was created from this status check.');
      }
    } catch (error) {
      if (Number(error?.response?.status) === 404) {
        const hasOriginalPayload = Boolean(clientSaveIntent.payload);
        setClientSaveIntent({
          ...clientSaveIntent,
          state: hasOriginalPayload ? 'uncertain' : 'reentry',
          payload: hasOriginalPayload ? clientSaveIntent.payload : null,
        });
        setClientSaveIntentRecoveryError(
          hasOriginalPayload
            ? 'No receipt was found; the save may still be in flight. Retry the same save with its original details.'
            : 'No receipt was found; the save may still be in flight. Re-enter the original details and retry this same save reference.',
        );
      } else if (Number(error?.response?.status) === 409) {
        setClientSaveIntent({ ...clientSaveIntent, state: 'conflict' });
        setClientSaveIntentRecoveryError('This save reference has a payload conflict or unavailable result. It cannot create another client; contact your Shop administrator to resolve it.');
      } else {
        setClientSaveIntentRecoveryError('Could not check the previous save. Keep this intent and retry the status check.');
      }
    }
  };

  const handleDownloadInvoice = async () => {
    setDownloadInvoiceModal(true);
    await downloadInvoice(service_id).finally(() => {
      setDownloadInvoiceModal(false);
    });
  };

  return (
    <Row gutter={12}>
      <Col
        span={24}
        className='mb-4 d-flex justify-content-between align-items-center'
      >
        <Title level={2}>{t(title)}</Title>
        {!!service_id && (
          <Button
            htmlType='button'
            onClick={handleDownloadInvoice}
            loading={downloadInvoiceModal}
          >
            {t('download.invoice')}
          </Button>
        )}
      </Col>
      <Col span={24}>
        <Form.Item
          name='client'
          label='Client'
          rules={[{ required: true, message: t('required') }]}
        >
          <Select
            showSearch
            allowClear
            labelInValue
            filterOption={false}
            disabled={!isAdd}
            placeholder='Search existing clients...'
            options={clientOptions}
            loading={clientLoading}
            onSearch={debouncedLoadClients}
            onChange={(value) => {
              if (value?.value?.startsWith('local:')) {
                infoForm.setFieldsValue({ payment_id: undefined });
              }
            }}
            notFoundContent={
              clientLoading ? (
                <Spin size='small' />
              ) : clientError ? (
                'Unable to load clients'
              ) : (
                'No clients found'
              )
            }
          />
        </Form.Item>
      </Col>
      {clientError && (
        <Col span={24}>
          <Alert
            className='mb-2'
            type='error'
            showIcon
            message={clientError}
            action={
              <Button size='small' onClick={() => loadClients('')}>
                Retry
              </Button>
            }
          />
        </Col>
      )}
      {isAdd && (
        <Col span={24} className='mb-3'>
          <Button
            type='link'
            icon={<PlusOutlined />}
            onClick={() => {
              const pendingPayload = clientSaveIntent?.payload;
              newClientForm.setFieldsValue(pendingPayload
                ? { ...pendingPayload }
                : { shop_location_id: selectedBranch });
              setClientError('');
              setIsNewClientOpen(true);
            }}
          >
            New local client
          </Button>
        </Col>
      )}
      <Modal
        title='New local client'
        className='aa-local-client-modal'
        width='min(520px, calc(100vw - 20px))'
        visible={isNewClientOpen}
        forceRender
        onCancel={() => setIsNewClientOpen(false)}
        destroyOnClose
        footer={[
          <Button key='cancel' onClick={() => setIsNewClientOpen(false)}>
            Cancel
          </Button>,
          <Button
            key='submit'
            type='primary'
            loading={clientCreateLoading}
            disabled={['needs-recovery', 'recovering', 'conflict', 'sending'].includes(clientSaveIntent?.state)}
            onClick={() => newClientForm.submit()}
          >
            {clientSaveIntent?.state === 'sending'
              ? 'Save in progress…'
              : ['uncertain', 'reentry'].includes(clientSaveIntent?.state)
                ? 'Retry same client save'
                : 'Save to client directory'}
          </Button>,
        ]}
      >
        <Form
          form={newClientForm}
          layout='vertical'
          onFinish={createClient}
          preserve
        >
          <Typography.Paragraph type='secondary' className='mb-3'>
            Adds to this Shop&apos;s directory. Does not create a platform account.
          </Typography.Paragraph>
          {clientError && (
            <Alert
              className='mb-3'
              type={clientSaveIntent?.state === 'uncertain' ? 'warning' : 'error'}
              showIcon
              message={clientError}
            />
          )}
          {clientSaveIntentRecoveryError && (
            <Alert
              className='mb-3'
              type='warning'
              showIcon
              message={clientSaveIntentRecoveryError}
              action={<Button size='small' onClick={retryClientSaveStatus}>Check save status</Button>}
            />
          )}
          {clientSaveIntent?.payload && ['uncertain', 'sending'].includes(clientSaveIntent.state) && (
            <Alert className='mb-3' type='info' showIcon message='This save is tied to the original details. Fields stay locked until its outcome is confirmed.' />
          )}
          <Form.Item
            name='name'
            label='Name'
            rules={[{ required: true, whitespace: true, message: t('required') }]}
          >
            <Input autoComplete='name' maxLength={191} disabled={['uncertain', 'sending', 'needs-recovery', 'recovering', 'conflict'].includes(clientSaveIntent?.state)} required aria-required='true' />
          </Form.Item>
          <Form.Item name='phone' label='Phone'>
            <Input autoComplete='tel' maxLength={64} disabled={['uncertain', 'sending', 'needs-recovery', 'recovering', 'conflict'].includes(clientSaveIntent?.state)} />
          </Form.Item>
          <Form.Item
            name='email'
            label='Email (optional)'
            rules={[{ type: 'email', message: 'Enter a valid email address.' }]}
          >
            <Input autoComplete='email' maxLength={191} disabled={['uncertain', 'sending', 'needs-recovery', 'recovering', 'conflict'].includes(clientSaveIntent?.state)} />
          </Form.Item>
          {serviceLocations.length > 1 && (
            <Form.Item
              name='shop_location_id'
              label='Service branch'
              rules={[{ required: true, message: 'Select a service branch.' }]}
            >
              <Select
                placeholder='Select a service branch'
                disabled={['uncertain', 'sending', 'needs-recovery', 'recovering', 'conflict'].includes(clientSaveIntent?.state)}
                options={serviceLocations.map((location) => ({
                  label: location.translation?.title || location.address || `Branch ${location.id}`,
                  value: location.id,
                }))}
              />
            </Form.Item>
          )}
        </Form>
      </Modal>
      <Col span={24}>
        <Form.Item
          name='payment_id'
          rules={[
            {
              required: Boolean(isRegisteredClient),
              message: t('required'),
            },
            {
              validator: (_, value) => {
                if (!isRegisteredClient || !value) return Promise.resolve();
                return cashPayment && value.value === cashPayment.id
                  ? Promise.resolve()
                  : Promise.reject(new Error('Select the current Cash option.'));
              },
            },
          ]}
          label={t('payment')}
        >
          <Select
            labelInValue
            loading={cashPaymentLoading}
            placeholder={
              selectedClient?.value?.startsWith('local:')
                ? 'No payment selected'
                : t('select.payment.type')
            }
            disabled={!isAdd || selectedClient?.value?.startsWith('local:')}
          >
            {cashPayment && <Select.Option value={cashPayment.id}>{t('cash')}</Select.Option>}
          </Select>
        </Form.Item>
        {isRegisteredClient && cashPaymentError && (
          <Alert type='error' showIcon message={cashPaymentError} className='mb-3' />
        )}
        {isAdd &&
          selectedClient?.value?.startsWith('local:') &&
          !calculatedData?.status && (
            <Col span={24}>
              <Typography.Text type='secondary' className='agendaally-quiet-payment-note'>
                Local-client bookings remain unpaid until a supported collection is recorded.
              </Typography.Text>
            </Col>
          )}
      </Col>
      <Col span={24}>
        <BookingBranchSelect shopLocations={shopLocations} disabled={!isAdd} />
      </Col>
      {calculatedData?.items?.map((item) => (
        <Col span={24} key={item.id}>
          <ServiceCard item={item} />
        </Col>
      ))}
      {isAdd && (
        <Col span={24}>
          <Button
            block
            type='dashed'
            icon={<PlusOutlined />}
            disabled={isDisabled}
            onClick={() => {
              setInfoData(infoForm?.getFieldsValue());
              setViewContent('serviceForm');
            }}
          >
            {t('add.service')}
          </Button>
        </Col>
      )}
    </Row>
  );
};

export default InfoFormItems;
