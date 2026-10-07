import React, { useEffect, useState } from 'react';
import { Alert, Button, Card, Divider, Form, Input, Select, Space, Table, Tag, Typography, message } from 'antd';
import manualFinanceService from 'services/manual-finance';
import { clearManualFinanceIntent, readManualFinanceIntent, saveManualFinanceIntent } from 'helpers/manual-finance-intent.mjs';
import { formatManualFinanceAmount, vendorPayoutStateLabels as payoutLabel } from 'helpers/manual-finance-copy.mjs';

const workflowKey = 'vendor:payout-request';

export default function ManualPayoutRequests() {
  const [sources, setSources] = useState([]);
  const [rows, setRows] = useState([]);
  const [canRequest, setCanRequest] = useState(false);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [pendingIntent, setPendingIntent] = useState(() => readManualFinanceIntent(workflowKey));
  const [errorText, setErrorText] = useState('');
  const [form] = Form.useForm();

  const load = async () => {
    setLoading(true);
    setErrorText('');
    try {
      const [listResult, capabilityResult] = await Promise.all([
        manualFinanceService.list({ kind: 'payout' }),
        manualFinanceService.capabilities(),
      ]);
      setRows(listResult?.data || []);
      setCanRequest(capabilityResult?.data?.can_request_payout === true);
      if (capabilityResult?.data?.can_request_payout === true) {
        const sourceResult = await manualFinanceService.sources('payout');
        setSources(sourceResult?.data || []);
      } else {
        setSources([]);
      }
    } catch (error) {
      setErrorText(error?.response?.data?.message || error?.message || 'Payout requests could not be loaded.');
    } finally {
      setLoading(false);
    }
  };
  useEffect(() => { load(); }, []);

  const submit = async (values, retry = false) => {
    setSaving(true);
    let saved = readManualFinanceIntent(workflowKey);
    if (!retry && !saved) {
      const source = sources.find((item) => item.allocation_id === values.allocation_id);
      if (!source) { setSaving(false); return; }
      saved = saveManualFinanceIntent(workflowKey, {
        kind: 'payout',
        allocation_id: source.allocation_id,
        ...(source.context_id ? { context_id: source.context_id } : {}),
        amount_units: source.amount_units,
        currency_code: source.currency_code,
        method: values.method,
        institution: String(values.institution).trim().toLowerCase().replace(/[^a-z0-9_-]+/g, '-'),
        destination_mask: String(values.destination_mask).trim(),
      });
    }
    if (!saved) { setSaving(false); return; }
    setPendingIntent(saved);
    try {
      await manualFinanceService.create(saved.payload);
      clearManualFinanceIntent(workflowKey);
      setPendingIntent(null);
      form.resetFields();
      message.success('Payout request submitted.');
      await load();
    } catch (error) {
      setPendingIntent(readManualFinanceIntent(workflowKey));
      message.error(error?.response?.data?.message || (error?.response?.status === 409 ? 'The request could not be confirmed. The saved command is retained; retry it to safely resolve.' : 'Submission is unresolved. Its original command and details are saved for retry.'));
      await load();
    } finally { setSaving(false); }
  };

  const cancelRequest = async (row) => {
    const key = `vendor:${row.id}:cancel`;
    let intent = readManualFinanceIntent(key);
    if (!intent && row.state === 'APPROVED' && !window.confirm('I attest that no external payout execution occurred and no money has moved. Continue with cancellation?')) return;
    if (!intent) intent = saveManualFinanceIntent(key, { version: row.version, reason: 'Vendor requested cancellation.', ...(row.state === 'APPROVED' ? { no_execution_attested: true } : {}) });
    try {
      await manualFinanceService.action(row.id, 'cancel', intent.payload);
      clearManualFinanceIntent(key);
      message.success('Cancellation request recorded.');
      await load();
    } catch (error) {
      message.error(error?.response?.data?.message || 'Cancellation is unresolved. The saved action remains available for safe retry.');
      await load();
    }
  };

  const columns = [
    { title: 'Request', dataIndex: 'id', key: 'id' },
    { title: 'Status', dataIndex: 'state', key: 'state', render: (state) => <Tag color={state === 'COMPLETED' ? 'green' : state === 'REQUIRES_REVIEW' ? 'red' : state === 'APPROVED' ? 'gold' : 'blue'}>{payoutLabel[state] || 'Payout status unavailable'}</Tag> },
    { title: 'Amount', key: 'amount', render: (_, row) => formatManualFinanceAmount(row.amount_units, row.currency_code, row.money_scale) },
    { title: 'Requested', dataIndex: 'requested_at', key: 'requested_at', render: (value) => value ? new Date(value).toLocaleString() : '—' },
    { title: 'Receipt reference', key: 'reference', render: (_, row) => row.state === 'COMPLETED' ? row.external_reference || '—' : 'Not paid yet' },
    { title: 'Action', key: 'action', render: (_, row) => row.actions?.includes('cancel') || readManualFinanceIntent(`vendor:${row.id}:cancel`) ? <Button size="small" onClick={() => cancelRequest(row)}>{readManualFinanceIntent(`vendor:${row.id}:cancel`) ? 'Retry cancellation' : 'Cancel request'}</Button> : null },
  ];

  return <Card>
    <Space className="w-100 justify-content-between" wrap>
      <div><Typography.Title level={2} style={{ margin: 0, color: 'var(--text)' }}>Payout requests</Typography.Title><Typography.Text type="secondary">Request a payout from eligible Shop obligations. A request or approval is not a payment.</Typography.Text></div>
      <Button onClick={load}>Refresh</Button>
    </Space>
    <Divider />
    {errorText && <Alert className="mb-4" type="error" showIcon message={errorText} action={<Button size="small" onClick={load}>Retry</Button>} />}
    {canRequest && <section className="mb-5">
      <Typography.Title level={4}>Request a Shop payout</Typography.Title>
      {sources.length ? <Form form={form} layout="vertical" onFinish={(values) => submit(values)}>
        <Form.Item name="allocation_id" label="Eligible Shop obligation" rules={[{ required: true, message: 'Choose an eligible obligation.' }]}>
          <Select placeholder="Choose an eligible allocation" options={sources.map((source) => ({ value: source.allocation_id, label: `${source.source_type} ${source.source_id} · ${formatManualFinanceAmount(source.amount_units, source.currency_code, source.money_scale)}` }))} />
        </Form.Item>
        <Form.Item shouldUpdate noStyle>{() => {
          const selected = sources.find((source) => source.allocation_id === form.getFieldValue('allocation_id'));
          return selected ? <Alert className="mb-4" type="info" message={`Eligible amount is fixed by the recorded obligation: ${formatManualFinanceAmount(selected.amount_units, selected.currency_code, selected.money_scale)}.`} /> : null;
        }}</Form.Item>
        <div className="grid gap-x-4 md:grid-cols-2">
          <Form.Item name="method" label="Payout method" rules={[{ required: true }]}><Select options={[{ value: 'bank_transfer', label: 'Bank transfer' }, { value: 'mobile_money', label: 'Mobile money' }]} /></Form.Item>
          <Form.Item name="institution" label="Institution" rules={[{ required: true, whitespace: true }]}><Input placeholder="Bank or mobile money institution" /></Form.Item>
          <Form.Item className="md:col-span-2" name="destination_mask" label="Masked destination" rules={[{ required: true, pattern: /.*\*.*/, message: 'Enter only a masked destination containing *.' }]}><Input autoComplete="off" placeholder="•••• 4821" /></Form.Item>
        </div>
        <Button type="primary" htmlType="submit" loading={saving} disabled={!!pendingIntent}>Submit payout request</Button>
      </Form> : <Alert type="info" message="There are no eligible Shop payout obligations to request right now." />}
    </section>}
    {!canRequest && <Alert className="mb-4" type="info" showIcon message="Payout requests are not enabled for this account." />}
    {pendingIntent && <Alert className="mb-4" type="warning" showIcon message="A previous submission has no definitive response. The full payload and command identity are saved." action={<Button size="small" onClick={() => submit({}, true)} loading={saving}>Retry saved request</Button>} />}
    <Typography.Title level={4}>Your Shop payout requests</Typography.Title>
    <Table rowKey="id" columns={columns} dataSource={rows} loading={loading} scroll={{ x: 900 }} locale={{ emptyText: 'No Shop payout requests are available for this account.' }} />
    {rows.some((row) => row.state === 'REQUIRES_REVIEW') && <Alert className="mt-4" type="error" showIcon message="Payout under review — do not request or execute another payment while Finance reconciles it." />}
  </Card>;
}
