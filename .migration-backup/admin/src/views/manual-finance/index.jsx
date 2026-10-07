import React, { useEffect, useMemo, useState } from 'react';
import {
  Alert,
  Button,
  Checkbox,
  Descriptions,
  Divider,
  Form,
  Input,
  Modal,
  Select,
  Space,
  Table,
  Tag,
  Typography,
  Upload,
  message,
} from 'antd';
import { ReloadOutlined, UploadOutlined } from '@ant-design/icons';
import moment from 'moment';
import Card from 'components/card';
import manualFinanceService from 'services/manual-finance';
import { manualFinanceStateLabels as stateLabels } from 'helpers/manual-finance-copy.mjs';
import {
  clearManualFinanceIntent,
  readManualFinanceIntent,
  saveManualFinanceIntent,
} from 'helpers/manual-finance-intent.mjs';

const { TextArea } = Input;
const stateColors = { REQUESTED: 'blue', APPROVED: 'gold', REQUIRES_REVIEW: 'red', COMPLETED: 'green', REJECTED: 'default', CANCELLED: 'default' };

const amount = (row) => {
  const scale = Number(row.money_scale || 0);
  const units = String(row.amount_units || '0');
  const negative = units.startsWith('-');
  const digits = negative ? units.slice(1) : units;
  const padded = digits.padStart(scale + 1, '0');
  return `${negative ? '-' : ''}${padded.slice(0, padded.length - scale)}${scale ? `.${padded.slice(-scale)}` : ''} ${row.currency_code}`;
};
const date = (value) => value ? moment(value).format('YYYY-MM-DD HH:mm') : '—';

export default function ManualFinance() {
  const [rows, setRows] = useState([]);
  const [capabilities, setCapabilities] = useState(null);
  const [filters, setFilters] = useState({ kind: undefined, state: undefined, claimed: undefined, aged: undefined, shop_id: '' });
  const [loading, setLoading] = useState(true);
  const [errorText, setErrorText] = useState('');
  const [activeRow, setActiveRow] = useState(null);
  const [detail, setDetail] = useState(null);
  const [actionName, setActionName] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [attachmentId, setAttachmentId] = useState(null);
  const [form] = Form.useForm();
  const [pendingIntent, setPendingIntent] = useState(null);

  const load = async () => {
    setLoading(true);
    setErrorText('');
    try {
      const [listResult, capabilityResult] = await Promise.all([
        manualFinanceService.list(Object.fromEntries(Object.entries(filters).filter(([, value]) => value !== '' && value !== undefined))),
        manualFinanceService.capabilities(),
      ]);
      setRows(listResult?.data || []);
      setCapabilities(capabilityResult?.data || null);
    } catch (error) {
      setErrorText(error?.response?.data?.message || error?.message || 'Finance requests could not be loaded.');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { load(); }, [filters.kind, filters.state, filters.claimed, filters.aged, filters.shop_id]);

  const openDetail = async (row) => {
    setActiveRow(row);
    setActionName('');
    try {
      const result = await manualFinanceService.detail(row.id);
      setDetail(result?.data || result);
    } catch (error) {
      message.error(error?.response?.data?.message || 'Request details could not be loaded.');
      setDetail(row);
    }
  };

  const openAction = (row, action) => {
    setActiveRow(row);
    setActionName(action);
    const key = `admin:${row.id}:${action}`;
    setPendingIntent(readManualFinanceIntent(key));
    setAttachmentId(null);
    form.resetFields();
    form.setFieldsValue({ target_state: 'COMPLETED' });
  };

  const perform = async (values, retrySaved = false) => {
    if (!activeRow || !actionName) return;
    setSubmitting(true);
    const key = `admin:${activeRow.id}:${actionName}`;
    let payload;
    if (retrySaved || readManualFinanceIntent(key)) {
      const saved = readManualFinanceIntent(key);
      if (!saved) return;
      payload = saved.payload;
    } else {
      payload = { version: activeRow.version, reason: values.reason || '' };
      if (actionName === 'cancel' && activeRow.state === 'APPROVED') payload.no_execution_attested = values.no_execution_attested === true;
      if (actionName === 'reconcile') payload.target_state = values.target_state;
      if (actionName === 'complete' || actionName === 'reconcile') {
        payload.evidence = {
          allocation_id: activeRow.allocation_id,
          amount_units: activeRow.amount_units,
          currency_code: activeRow.currency_code,
          beneficiary_id: activeRow.beneficiary_id,
          method: activeRow.method,
          institution: activeRow.institution,
          destination_mask: activeRow.destination_mask,
          external_reference: values.external_reference,
          executed_at: values.executed_at,
          state: actionName === 'complete' || values.target_state === 'COMPLETED' ? 'SUCCESS' : 'NO_MOVEMENT',
          attested: values.attested === true,
          ...(attachmentId ? { attachment_id: attachmentId } : {}),
        };
      }
      payload = saveManualFinanceIntent(key, payload).payload;
    }
    setPendingIntent(readManualFinanceIntent(key));
    try {
      await manualFinanceService.action(activeRow.id, actionName, payload);
      clearManualFinanceIntent(key);
      setPendingIntent(null);
      setAttachmentId(null);
      setActionName('');
      setActiveRow(null);
      message.success('Finance action recorded.');
      await load();
    } catch (error) {
      setPendingIntent(readManualFinanceIntent(key));
      message.error(error?.response?.data?.message || (error?.response?.status === 409 ? 'This request changed elsewhere. Saved command retained; review the current record before retrying.' : 'Action response is unresolved. Saved command retained for a safe retry.'));
      await load();
    } finally {
      setSubmitting(false);
    }
  };

  const retryPending = () => perform({}, true);

  const uploadAttachment = async (file) => {
    if (!activeRow || !['complete', 'reconcile'].some((action) => activeRow.actions?.includes(action))) return false;
    if (file.size > 2 * 1024 * 1024 || !['application/pdf', 'image/png', 'image/jpeg'].includes(file.type)) {
      message.error('Choose a PDF, PNG or JPEG file no larger than 2 MB.');
      return false;
    }
    try {
      const result = await manualFinanceService.upload(activeRow.id, file);
      if (result?.data?.id) setAttachmentId(result.data.id);
      message.success('Evidence attached.');
      return false;
    } catch (error) {
      message.error(error?.response?.data?.message || 'Evidence could not be attached.');
      return false;
    }
  };

  const viewAttachment = async (attachmentId) => {
    try {
      const result = await manualFinanceService.attachmentLink(activeRow.id, attachmentId);
      const url = result?.data?.url;
      if (url) window.location.assign(url);
      else message.error('Evidence link is unavailable.');
    } catch (error) {
      message.error(error?.response?.data?.message || 'Evidence link is unavailable.');
    }
  };

  const columns = useMemo(() => [
    { title: 'Request', dataIndex: 'id', key: 'id', render: (value, row) => <Button type="link" title={value} style={{ padding: 0 }} onClick={() => openDetail(row)}>{String(value).slice(0, 8)}…</Button> },
    { title: 'Type', dataIndex: 'kind', key: 'kind', render: (value) => value === 'refund' ? 'Refund' : 'Payout' },
    { title: 'Requester', dataIndex: 'requester_id', key: 'requester' },
    { title: 'Beneficiary', dataIndex: 'beneficiary_id', key: 'beneficiary' },
    { title: 'Shop / source', key: 'source', render: (_, row) => <span>Shop {row.shop_id ?? '—'} · Allocation {row.allocation_id || '—'}</span> },
    { title: 'Amount', key: 'amount', render: (_, row) => amount(row) },
    { title: 'State', dataIndex: 'state', key: 'state', render: (value) => <Tag color={stateColors[value]}>{stateLabels[value] || value}</Tag> },
    { title: 'Age / requested', dataIndex: 'requested_at', key: 'requested_at', render: (value) => <span>{value ? moment(value).fromNow() : '—'}<br />{date(value)}</span> },
    { title: 'Approved / completed', key: 'timestamps', render: (_, row) => <span>{date(row.approved_at)}<br />{date(row.completed_at)}</span> },
    { title: 'Method snapshot', key: 'method', render: (_, row) => <span>{row.method || '—'}<br />{row.institution || '—'} · {row.destination_mask || '—'}</span> },
    { title: 'Execution claim', key: 'claim', render: (_, row) => row.claim_actor_id ? <span>Claimed by {row.claim_actor_id}<br />{date(row.claimed_at)}</span> : 'Unclaimed' },
    { title: 'External reference', dataIndex: 'external_reference', key: 'reference', render: (value, row) => row.state === 'COMPLETED' ? value || '—' : 'Not completed' },
    { title: 'Actions', key: 'actions', fixed: 'right', render: (_, row) => {
      const known = ['approve', 'reject', 'cancel', 'claim', 'complete', 'reconcile', 'review'];
      const available = new Set((row.actions || []).filter((action) => known.includes(action)));
      const pending = known.filter((action) => readManualFinanceIntent(`admin:${row.id}:${action}`));
      return <Space wrap>{[...new Set([...available, ...pending])].map((action) => <Button key={action} size="small" onClick={() => openAction(row, action)}>{pending.includes(action) ? `Retry saved ${action}` : ({ approve: 'Approve', reject: 'Reject', cancel: 'Cancel', claim: 'Claim execution', complete: 'Record completion', reconcile: 'Reconcile', review: 'Review' })[action] || action}</Button>)}</Space>;
    } },
  ].map((column) => ({ ...column, width: ({
    id: 150, kind: 90, requester: 90, beneficiary: 100, source: 150,
    amount: 140, state: 250, requested_at: 180, timestamps: 180,
    method: 200, claim: 180, reference: 200, actions: 330,
  })[column.key] || 180 })), []);

  const actionTitle = ({ approve: 'Approve request', reject: 'Reject request', cancel: 'Cancel request', claim: 'Claim external execution', complete: 'Record completion', reconcile: 'Reconcile request', review: 'Send to review' })[actionName] || actionName;
  const confirmAction = () => form.validateFields().then((values) => perform(values));

  return (
    <Card>
      <Space className="w-100 justify-content-between align-items-start" wrap>
        <div><Typography.Title level={2} style={{ color: 'var(--text)', margin: 0 }}>Manual finance requests</Typography.Title><Typography.Text type="secondary">Permission-scoped requests and explicit execution responsibility. This queue never calls a bank or payment provider.</Typography.Text></div>
        <Button icon={<ReloadOutlined />} onClick={load}>Refresh</Button>
      </Space>
      <Divider color="var(--divider)" />
      {capabilities?.finance === false && <Alert type="warning" showIcon message="Finance access is not currently granted for this account." />}
      {errorText && <Alert className="mb-3" type="error" showIcon message={errorText} action={<Button size="small" onClick={load}>Retry</Button>} />}
      <Space wrap className="mb-4">
        <Select allowClear placeholder="All request types" style={{ width: 170 }} value={filters.kind} onChange={(kind) => setFilters((old) => ({ ...old, kind }))} options={[{ value: 'refund', label: 'Refunds' }, { value: 'payout', label: 'Payouts' }]} />
        <Select allowClear placeholder="All states" style={{ width: 190 }} value={filters.state} onChange={(state) => setFilters((old) => ({ ...old, state }))} options={Object.entries(stateLabels).map(([value, label]) => ({ value, label }))} />
        <Select allowClear placeholder="Claim status" style={{ width: 170 }} value={filters.claimed} onChange={(claimed) => setFilters((old) => ({ ...old, claimed }))} options={[{ value: 'true', label: 'Claimed' }, { value: 'false', label: 'Unclaimed' }]} />
        <Select allowClear placeholder="Age" style={{ width: 150 }} value={filters.aged} onChange={(aged) => setFilters((old) => ({ ...old, aged }))} options={[{ value: 'true', label: 'Aged' }, { value: 'false', label: 'Not aged' }]} />
        <Input placeholder="Shop ID" value={filters.shop_id} onChange={(event) => setFilters((old) => ({ ...old, shop_id: event.target.value }))} style={{ width: 130 }} />
      </Space>
      <Table rowKey="id" tableLayout="fixed" columns={columns} dataSource={rows} loading={loading} scroll={{ x: 2400 }} locale={{ emptyText: 'No manual finance requests are visible in this permission scope.' }} />

      <Modal title={actionTitle} visible={!!actionName} forceRender onCancel={() => { setActionName(''); setPendingIntent(null); }} onOk={pendingIntent ? retryPending : confirmAction} confirmLoading={submitting} okText={pendingIntent ? 'Retry saved action' : actionTitle} width={640}>
        {activeRow && <div className="mb-4">
          {actionName === 'approve' && <Alert type="warning" showIcon message="APPROVAL DOES NOT MEAN MONEY HAS MOVED." description="This approval authorizes the next manual step only. It does not mean this request has been paid or refunded." />}
          {activeRow.state === 'REQUIRES_REVIEW' && <Alert className="mb-3" type="error" showIcon message="DO NOT PAY AGAIN — RECONCILIATION REQUIRED." />}
          {activeRow.claim_actor_id && actionName !== 'claim' && <Alert className="mb-3" type="warning" showIcon message={`Execution responsibility is claimed by operator ${activeRow.claim_actor_id}. Another operator must not execute externally.`} />}
          {pendingIntent && <Alert className="mb-3" type="warning" showIcon message="An unresolved command is saved in this browser session. Retrying will replay the exact saved command and payload." />}
          <p><strong>{amount(activeRow)}</strong> · {activeRow.kind} · {stateLabels[activeRow.state] || activeRow.state}</p>
        </div>}
        {!pendingIntent && <Form form={form} layout="vertical">
          {['approve', 'reject', 'review'].includes(actionName) && <Form.Item name="reason" label="Finance reason" rules={[{ required: true, whitespace: true, message: 'A reason is required.' }]}><TextArea rows={3} maxLength={1000} /></Form.Item>}
          {actionName === 'cancel' && activeRow?.state === 'APPROVED' && <Form.Item name="no_execution_attested" valuePropName="checked" rules={[{ validator: (_, value) => value ? Promise.resolve() : Promise.reject(new Error('Attest that no external execution occurred.')) }]}><Checkbox>I attest that no money movement occurred and this request has no execution claim.</Checkbox></Form.Item>}
          {(actionName === 'complete' || actionName === 'reconcile') && <>
            {actionName === 'reconcile' && <Form.Item name="target_state" label="Reconciliation outcome" rules={[{ required: true }]}><Select options={[{ value: 'COMPLETED', label: 'Confirm external success — complete once' }, { value: 'REJECTED', label: 'Confirm no movement — reject' }, { value: 'APPROVED', label: 'Confirm no movement — authorize again' }]} /></Form.Item>}
            <Descriptions size="small" bordered column={1} className="mb-3">
              <Descriptions.Item label="Allocation / amount">{activeRow.allocation_id} · {amount(activeRow)}</Descriptions.Item>
              <Descriptions.Item label="Beneficiary">{activeRow.beneficiary_id}</Descriptions.Item>
              <Descriptions.Item label="Method / destination">{activeRow.method} · {activeRow.institution} · {activeRow.destination_mask}</Descriptions.Item>
            </Descriptions>
            <Form.Item name="external_reference" label="External receipt / attempt reference" rules={[{ required: true, whitespace: true }]}><Input maxLength={160} /></Form.Item>
            <Form.Item name="executed_at" label="Execution / verification time (ISO 8601)" rules={[{ required: true, pattern: /^\d{4}-\d{2}-\d{2}T.+(?:Z|[+-]\d{2}:\d{2})$/, message: 'Enter an ISO datetime with timezone, e.g. 2025-03-01T14:30:00Z.' }]}><Input placeholder="2025-03-01T14:30:00Z" /></Form.Item>
            <Form.Item name="attested" valuePropName="checked" rules={[{ validator: (_, value) => value ? Promise.resolve() : Promise.reject(new Error('Attestation is required.')) }]}><Checkbox>I attest this evidence matches the fixed allocation, amount, currency, beneficiary and method snapshot. No provider is called by this action.</Checkbox></Form.Item>
            {activeRow.actions?.some((action) => ['complete', 'reconcile'].includes(action)) && <Upload beforeUpload={uploadAttachment} showUploadList={false} accept=".pdf,.png,.jpg,.jpeg"><Button icon={<UploadOutlined />}>Attach PDF / PNG / JPEG evidence (max 2 MB)</Button></Upload>}
          </>}
          {actionName === 'claim' && <Alert type="info" message="Claiming records responsibility only. It does not execute or initiate a payment." />}
        </Form>}
      </Modal>

      <Modal title="Finance request details" visible={!!detail} footer={<Button onClick={() => { setDetail(null); setActiveRow(null); }}>Close</Button>} onCancel={() => { setDetail(null); setActiveRow(null); }} width={760}>
        {detail && <><Descriptions bordered size="small" column={2}>
          <Descriptions.Item label="Request ID">{detail.id}</Descriptions.Item><Descriptions.Item label="Type">{detail.kind}</Descriptions.Item>
          <Descriptions.Item label="Requester">{detail.requester_id}</Descriptions.Item><Descriptions.Item label="Beneficiary">{detail.beneficiary_id}</Descriptions.Item>
          <Descriptions.Item label="Shop">{detail.shop_id ?? '—'}</Descriptions.Item><Descriptions.Item label="Source allocation">{detail.allocation_id}</Descriptions.Item>
          <Descriptions.Item label="Amount">{amount(detail)}</Descriptions.Item><Descriptions.Item label="State"><Tag color={stateColors[detail.state]}>{stateLabels[detail.state] || detail.state}</Tag></Descriptions.Item>
          <Descriptions.Item label="Requested">{date(detail.requested_at)}</Descriptions.Item><Descriptions.Item label="Approved">{date(detail.approved_at)}</Descriptions.Item>
          <Descriptions.Item label="Completed">{date(detail.completed_at)}</Descriptions.Item><Descriptions.Item label="Method snapshot">{detail.method} · {detail.institution} · {detail.destination_mask}</Descriptions.Item>
          <Descriptions.Item label="Claim owner">{detail.claim_actor_id || 'Unclaimed'}</Descriptions.Item><Descriptions.Item label="External reference">{detail.state === 'COMPLETED' ? detail.external_reference || '—' : 'Not completed'}</Descriptions.Item>
        </Descriptions>
          {(detail.actions || []).includes('evidence-view') && (detail.attachments || []).map((file) => <Button key={file.id} type="link" onClick={() => viewAttachment(file.id)}>View evidence {file.id}</Button>)}
          <Divider orientation="left">Safe finance events</Divider>
          <ul>{(detail.events || []).map((event) => <li key={event.id || `${event.state}-${event.created_at}`}>{event.state || event.action} · {date(event.created_at)}{event.reason ? ` · ${event.reason}` : ''}</li>)}</ul>
        </>}
      </Modal>
    </Card>
  );
}
