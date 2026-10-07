import React, { useCallback, useEffect, useRef, useState } from 'react';
import {
  Alert,
  Button,
  Card,
  Checkbox,
  DatePicker,
  Descriptions,
  Form,
  Input,
  InputNumber,
  Select,
  Space,
  Tag,
  Typography,
} from 'antd';
import moment from 'moment';
import request from '../../services/request';
import './financial-operations-panel.css';
import { operationRequestKey } from './operation-intent.mjs';

const { TextArea } = Input;

function exactUnits(value, scale) {
  if (typeof value !== 'string' || !/^-?\d+$/.test(value)) return 'Unavailable';
  const places = Number(scale);
  if (!Number.isInteger(places) || places < 0 || places > 100) return `${value} native units`;
  const negative = value.startsWith('-');
  const digits = (negative ? value.slice(1) : value).padStart(places + 1, '0');
  if (!places) return `${negative ? '-' : ''}${digits}`;
  return `${negative ? '-' : ''}${digits.slice(0, -places)}.${digits.slice(-places)}`;
}

function messageFrom(error) {
  return error?.response?.data?.message || error?.response?.data?.error || error?.message || 'The operation could not be completed.';
}

export default function FinancialOperationsPanel({ canonicalFinance, role = 'admin', onRefresh }) {
  const allocationId = canonicalFinance?.allocation_id;
  const [operations, setOperations] = useState([]);
  const [attempts, setAttempts] = useState([]);
  const [contexts, setContexts] = useState([]);
  const [permissions, setPermissions] = useState({ can_process: false, can_request_payout: false });
  const [balances, setBalances] = useState({});
  const [snapshotMoney, setSnapshotMoney] = useState({});
  const [loading, setLoading] = useState(false);
  const [busy, setBusy] = useState('');
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [reservationNotice, setReservationNotice] = useState('');
  const [reservationForm] = Form.useForm();
  const [payoutForm] = Form.useForm();
  const retryKeys = useRef({});

  const reload = useCallback(async () => {
    if (!allocationId) return;
    setLoading(true);
    setError('');
    try {
      const response = await request.get(`dashboard/payment-operations/${allocationId}`);
      const result = response?.data?.data || response?.data || {};
      setOperations(Array.isArray(result.operations) ? result.operations : []);
      setAttempts(Array.isArray(result.attempts) ? result.attempts : []);
      setContexts(Array.isArray(result.contexts) ? result.contexts : []);
      setPermissions({
        can_process: result.can_process === true,
        can_request_payout: result.can_request_payout === true,
      });
      setBalances(result.balances || {});
      setSnapshotMoney({
        currency_code: result.currency_code,
        money_scale: result.money_scale,
      });
    } catch (requestError) {
      setError(messageFrom(requestError));
    } finally {
      setLoading(false);
    }
  }, [allocationId]);

  useEffect(() => { reload(); }, [reload]);

  const refreshAll = async () => {
    await reload();
    if (onRefresh) await onRefresh();
  };

  const postOperation = async (kind, amount, contextId) => {
    const normalized = String(amount || '');
    const payload = {
      kind,
      amount_units: normalized,
      request_key: operationRequestKey(retryKeys.current, { allocationId, kind, amount: normalized, contextId }),
      ...(contextId ? { context_id: Number(contextId) } : {}),
    };
    setBusy(`create-${kind}`);
    setError('');
    try {
      const response = await request.post(`dashboard/payment-operations/${allocationId}`, payload);
      const receipt = response?.data?.data || response?.data || {};
      setReservationNotice(`Reservation ${receipt.id || 'recorded'} retained. Repeating unchanged values retries this same request. No external payment was made.`);
      await refreshAll();
    } catch (requestError) {
      setError(messageFrom(requestError));
    } finally {
      setBusy('');
    }
  };

  const postAction = async (operationId, action, payload = {}) => {
    const key = `${operationId}-${action}`;
    setBusy(key);
    setError('');
    try {
      await request.post(`dashboard/payment-operations/${operationId}/${action}`, payload);
      await refreshAll();
    } catch (requestError) {
      setError(messageFrom(requestError));
    } finally {
      setBusy('');
    }
  };

  const reconcileCollectionAttempt = async (attemptId) => {
    setBusy(`attempt-${attemptId}-reconcile`);
    setError('');
    setNotice('');
    try {
      const response = await request.post(`dashboard/collection-attempts/${attemptId}/reconcile`, {});
      const result = response?.data?.data || response?.data || {};
      if (result.status === false) {
        setNotice('Collection reconciliation is pending or unknown. No successful collection is confirmed.');
      }
      await refreshAll();
    } catch (requestError) {
      setError(
        requestError?.response?.data?.message ||
        requestError?.response?.data?.error ||
        'Collection reconciliation is unavailable outside approved testing.',
      );
    } finally {
      setBusy('');
    }
  };

  const submitReceipt = async (operationId, values) => {
    if (!values.attested) return;
    setBusy(`${operationId}-receipt`);
    setError('');
    try {
      await request.post(`dashboard/payment-operations/${operationId}/receipt`, {
        source: 'cash_receipt',
        receipt_reference: values.receipt_reference.trim(),
        document_sha256: values.document_sha256.trim(),
        retained_evidence: values.retained_evidence.trim(),
        received_at: values.received_at.toISOString(),
      });
      await refreshAll();
    } catch (requestError) {
      setError(messageFrom(requestError));
    } finally {
      setBusy('');
    }
  };

  if (!allocationId) return null;
  const isAdmin = role === 'admin';
  const mayProcess = isAdmin && permissions.can_process;
  const mayPayout = permissions.can_request_payout;
  const currency = canonicalFinance?.currency_code ?? snapshotMoney.currency_code ?? 'Currency unavailable';
  const scale = canonicalFinance?.money_scale ?? snapshotMoney.money_scale;
  const currencyText = `${currency} · scale ${scale ?? 'unreported'}`;
  const money = (value) => `${exactUnits(value, scale)} ${currency}`;
  const amountRules = [
    { required: true, message: 'Enter a positive whole number of native units.' },
    {
      validator: (_, value) => (/^[1-9]\d*$/.test(String(value ?? ''))
        ? Promise.resolve()
        : Promise.reject(new Error('Use a positive integer string with no decimal point.'))),
    },
  ];
  const relevantContexts = (kind) => contexts.filter((context) => {
    if (kind === 'refund') return true;
    return true;
  });

  return (
    <Card
      size='small'
      title='Financial operations'
      className='mt-3 financial-operations-card'
      extra={<Button size='small' onClick={refreshAll} loading={loading}>Refresh</Button>}
    >
      <div className='financial-operations-panel'>
      <Typography.Paragraph type='secondary'>
        Allocation {allocationId}. All amounts use exact integer native units ({currencyText}); no currency conversion or rounding is performed.
      </Typography.Paragraph>
      {error && <Alert className='mb-3' type='error' showIcon message='Operation failed' description={error} closable onClose={() => setError('')} />}
      {reservationNotice && <Alert className='mb-3' type='info' showIcon message='Reservation recorded, not externally paid' description={reservationNotice} />}
      {(mayProcess || mayPayout) && <Button className='mb-3' disabled={!!busy} onClick={() => {
        retryKeys.current = {};
        reservationForm.resetFields();
        payoutForm.resetFields();
        setReservationNotice('');
      }}>Start a new request</Button>}
      {notice && <Alert className='mb-3' type='warning' showIcon message='Collection status not confirmed' description={notice} closable onClose={() => setNotice('')} />}
      <Alert
        className='mb-3'
        type='warning'
        showIcon
        message='External payout not ready'
        description='Payout requests can be recorded where authorized, but no destination rail is configured and funds will not be sent.'
      />
      <Descriptions bordered size='small' column={{ xs: 1, sm: 2 }} className='mb-3'>
        <Descriptions.Item label='Vendor payable'>{money(balances.vendor_payable)}</Descriptions.Item>
        <Descriptions.Item label='Commission receivable'>{money(balances.commission_receivable)}</Descriptions.Item>
      </Descriptions>

      {mayProcess && (
        <Space direction='vertical' style={{ width: '100%' }} size='middle'>
          <Form form={reservationForm} layout='inline' onFinish={() => {}} className='financial-operations-panel__form'>
            <Form.Item label='Reserve refund' name='refund_amount' rules={amountRules} validateTrigger='onSubmit'>
              <InputNumber min={1} precision={0} stringMode placeholder='Integer units' style={{ width: 150 }} />
            </Form.Item>
            <Form.Item name='refund_context' label='Payment context' rules={[{ required: true, message: 'Select the original payment context.' }]}>
              <Select
                placeholder='Select context'
                className='financial-operations-panel__context-select'
                style={{ width: 230 }}
                options={relevantContexts('refund').map((context) => ({
                  value: context.id,
                  label: `${context.provider_tag || context.provider || 'Provider'} · ${context.collection_mode || 'mode unavailable'} · ${context.custody_type || 'custody unavailable'} · ${context.amount || 'amount unavailable'} ${currency} · ${context.state || 'state unavailable'}`,
                }))}
              />
            </Form.Item>
            <Button
              type='primary'
              loading={busy === 'create-refund'}
              onClick={() => reservationForm.validateFields(['refund_amount', 'refund_context']).then((values) => postOperation('refund', String(values.refund_amount), values.refund_context)).catch(() => {})}
            >Reserve refund</Button>
          </Form>
          <Form form={reservationForm} layout='inline' className='financial-operations-panel__form'>
            <Form.Item label='Reserve receivable' name='receivable_amount' rules={amountRules}>
              <Input placeholder='Positive integer units' inputMode='numeric' />
            </Form.Item>
            <Button
              onClick={() => reservationForm.validateFields(['receivable_amount']).then((values) => postOperation('receivable', values.receivable_amount)).catch(() => {})}
              loading={busy === 'create-receivable'}
            >Reserve receivable</Button>
          </Form>
        </Space>
      )}

      {mayPayout && (
        <Form form={payoutForm} layout='inline' className='mt-3 financial-operations-panel__form'>
          <Form.Item label='Request payout' name='payout_amount' rules={amountRules}>
            <Input placeholder='Positive integer units' inputMode='numeric' />
          </Form.Item>
          <Button
            type='primary'
            loading={busy === 'create-payout'}
            onClick={() => payoutForm.validateFields(['payout_amount']).then((values) => postOperation('payout', values.payout_amount)).catch(() => {})}
          >Request payout</Button>
        </Form>
      )}

      <Typography.Title level={5} className='mt-4'>Operation history</Typography.Title>
      {loading && !operations.length ? (
        <Typography.Text type='secondary'>Loading retained operations…</Typography.Text>
      ) : operations.length ? operations.map((operation) => (
        <Card size='small' key={operation.id} className='mb-2' title={
          <Space><span>{operation.kind}</span><Tag>{operation.state}</Tag></Space>
        }>
          <Descriptions size='small' column={{ xs: 1, sm: 2 }}>
            <Descriptions.Item label='Operation ID'>{operation.id}</Descriptions.Item>
            <Descriptions.Item label='Amount'>{money(operation.amount_units)}</Descriptions.Item>
            <Descriptions.Item label='Provider'>{operation.provider || 'Not reported'}</Descriptions.Item>
            <Descriptions.Item label='Context'>{operation.context_id ?? 'Not reported'}</Descriptions.Item>
            <Descriptions.Item label='Original payment'>{operation.original_payment_id ?? 'Not reported'}</Descriptions.Item>
            <Descriptions.Item label='External reference'>{operation.external_reference || 'Not reported'}</Descriptions.Item>
            <Descriptions.Item label='Claimed at'>{operation.claimed_at || 'Not claimed'}</Descriptions.Item>
            <Descriptions.Item label='Created at'>{operation.created_at || 'Not reported'}</Descriptions.Item>
          </Descriptions>
          <Space wrap className='mt-2 financial-operations-panel__actions'>
            {mayProcess && operation.kind === 'refund' && (
              <>
                <Button
                  disabled
                  title='Provider dispatch is disabled outside approved isolated testing.'
                  onClick={() => postAction(operation.id, 'dispatch')}
                >Dispatch disabled</Button>
                <Button onClick={() => postAction(operation.id, 'reconcile')} loading={busy === `${operation.id}-reconcile`}>Reconcile / recover</Button>
              </>
            )}
            {mayProcess && operation.kind === 'receivable' && operation.state === 'RESERVED' && !operation.claimed_at && (
              <Form
                layout='vertical'
                onFinish={(values) => submitReceipt(operation.id, values)}
                initialValues={{ received_at: moment(), attested: false }}
                style={{ width: '100%' }}
                className='financial-operations-panel__receipt-form'
              >
                <Alert className='my-2' type='warning' message='Cash receipt evidence only' description='This records an actual retained offline cash receipt. It does not simulate or perform an Admin Wallet debit.' />
                <Form.Item name='receipt_reference' label='Receipt reference' rules={[{ required: true }, { max: 255 }]}>
                  <Input />
                </Form.Item>
                <Form.Item name='document_sha256' label='Receipt document SHA-256' rules={[{ required: true }, { pattern: /^[a-fA-F0-9]{64}$/, message: 'Enter exactly 64 hexadecimal characters.' }]}>
                  <Input maxLength={64} />
                </Form.Item>
                <Form.Item name='retained_evidence' label='Document provenance / actual receipt text' rules={[{ required: true }, { max: 4000 }]}>
                  <TextArea rows={3} maxLength={4000} showCount />
                </Form.Item>
                <Form.Item name='received_at' label='Received at' rules={[{ required: true }]}>
                  <DatePicker showTime style={{ width: '100%' }} />
                </Form.Item>
                <Form.Item name='attested' valuePropName='checked' rules={[{ validator: (_, value) => value ? Promise.resolve() : Promise.reject(new Error('Confirm that this is a real cash receipt.')) }]}>
                  <Checkbox>I attest this is a real cash receipt and the evidence above is retained.</Checkbox>
                </Form.Item>
                <Button htmlType='submit' type='primary' loading={busy === `${operation.id}-receipt`}>Record cash receipt evidence</Button>
              </Form>
            )}
            {mayPayout && operation.kind === 'payout' && operation.state === 'RESERVED' && !operation.claimed_at && (
              <Button danger onClick={() => postAction(operation.id, 'cancel')} loading={busy === `${operation.id}-cancel`}>Cancel request</Button>
            )}
            {mayProcess && operation.kind !== 'payout' && operation.state === 'RESERVED' && !operation.claimed_at && (
              <Button danger onClick={() => postAction(operation.id, 'cancel')} loading={busy === `${operation.id}-cancel`}>Cancel reservation</Button>
            )}
          </Space>
        </Card>
      )) : (
        <Alert type='info' showIcon message='No financial operations recorded' description='Operations appear here only after a server-confirmed reservation or evidence action.' />
      )}

      <Typography.Title level={5} className='mt-4'>Original collection attempts</Typography.Title>
      {attempts.length ? attempts.map((attempt) => {
        const terminal = ['SUCCESS', 'FAILURE'].includes(String(attempt.state || '').toUpperCase());
        return (
          <Card
            size='small'
            key={attempt.id}
            className='mb-2'
            title={<Space><span>{attempt.provider || 'Provider not reported'}</span><Tag>{attempt.state || 'State unavailable'}</Tag></Space>}
          >
            <Descriptions size='small' column={{ xs: 1, sm: 2 }}>
              <Descriptions.Item label='Provider reference'>{attempt.provider_reference || 'Not reported'}</Descriptions.Item>
              <Descriptions.Item label='Provider payment ID'>{attempt.provider_payment_id || 'Not reported'}</Descriptions.Item>
              <Descriptions.Item label='Claimed at'>{attempt.claimed_at || 'Not claimed'}</Descriptions.Item>
              <Descriptions.Item label='Created at'>{attempt.created_at || 'Not reported'}</Descriptions.Item>
              <Descriptions.Item label='Updated at'>{attempt.updated_at || 'Not reported'}</Descriptions.Item>
            </Descriptions>
            {mayProcess && !terminal && (
              <Button
                size='small'
                className='mt-2 financial-operations-panel__action'
                loading={busy === `attempt-${attempt.id}-reconcile`}
                onClick={() => reconcileCollectionAttempt(attempt.id)}
              >Reconcile collection status</Button>
            )}
          </Card>
        );
      }) : (
        <Typography.Text type='secondary'>No original collection attempts are retained for this allocation.</Typography.Text>
      )}
      </div>
    </Card>
  );
}