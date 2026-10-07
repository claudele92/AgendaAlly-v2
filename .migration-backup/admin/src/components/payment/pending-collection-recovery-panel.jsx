import React, { useCallback, useEffect, useMemo, useState } from 'react';
import {
  Alert,
  Button,
  Checkbox,
  Descriptions,
  Modal,
  Space,
  Table,
  Tag,
  Typography,
} from 'antd';
import request from '../../services/request';
import FinancialOperationsPanel from './financial-operations-panel';
import './pending-collection-recovery-panel.css';

function errorText(error, fallback) {
  return error?.response?.data?.message ||
    error?.response?.data?.error ||
    fallback;
}

export default function PendingCollectionRecoveryPanel() {
  const [attempts, setAttempts] = useState([]);
  const [loading, setLoading] = useState(false);
  const [busyId, setBusyId] = useState(null);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [showTerminal, setShowTerminal] = useState(false);
  const [selectedAttempt, setSelectedAttempt] = useState(null);

  const loadAttempts = useCallback(async () => {
    setLoading(true);
    setError('');
    try {
      const response = await request.get('dashboard/collection-attempts');
      const result = response?.data?.data || response?.data || {};
      setAttempts(Array.isArray(result.attempts) ? result.attempts : []);
    } catch (requestError) {
      setError(errorText(requestError, 'Could not load collection attempts. Please retry.'));
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadAttempts();
  }, [loadAttempts]);

  const reconcile = async (attempt) => {
    setBusyId(attempt.id);
    setError('');
    setNotice('');
    try {
      const response = await request.post(`dashboard/collection-attempts/${attempt.id}/reconcile`, {});
      const result = response?.data?.data || response?.data || {};
      if (result.status === false) {
        setNotice(`Attempt ${attempt.id} remains pending or unknown. No successful collection is confirmed.`);
      }
      await loadAttempts();
    } catch (requestError) {
      setError(errorText(requestError, 'Collection reconciliation is unavailable outside approved testing.'));
    } finally {
      setBusyId(null);
    }
  };

  const visibleAttempts = useMemo(
    () => showTerminal
      ? attempts
      : attempts.filter((attempt) => !['SUCCESS', 'FAILURE'].includes(String(attempt.state || '').toUpperCase())),
    [attempts, showTerminal],
  );

  const columns = [
    {
      title: 'Attempt',
      dataIndex: 'id',
      key: 'id',
      width: 90,
    },
    {
      title: 'Status',
      dataIndex: 'state',
      key: 'state',
      width: 130,
      render: (state) => <Tag color={['SUCCESS', 'FAILURE'].includes(String(state || '').toUpperCase()) ? 'default' : 'gold'}>{state || 'Unknown'}</Tag>,
    },
    {
      title: 'Provider',
      dataIndex: 'provider',
      key: 'provider',
      width: 130,
      render: (provider) => provider || 'Not reported',
    },
    {
      title: 'Original reference',
      dataIndex: 'provider_reference',
      key: 'provider_reference',
      render: (reference) => reference || 'Not reported',
    },
    {
      title: 'Provider payment ID',
      dataIndex: 'provider_payment_id',
      key: 'provider_payment_id',
      render: (reference) => reference || 'Not reported',
    },
    {
      title: 'Allocation / shop / country',
      key: 'scope',
      render: (_, row) => (
        <Typography.Text>
          {row.allocation_id ?? '—'} / {row.shop_id ?? '—'} / {row.country_id ?? '—'}
        </Typography.Text>
      ),
    },
    {
      title: 'Currency · scale',
      key: 'money',
      render: (_, row) => `${row.currency_code || 'Not reported'} · ${row.money_scale ?? 'scale unavailable'}`,
    },
    {
      title: 'Claimed / created',
      key: 'dates',
      render: (_, row) => (
        <Space direction='vertical' size={0}>
          <span>{row.claimed_at || 'Not claimed'}</span>
          <Typography.Text type='secondary'>{row.created_at || 'Creation time unavailable'}</Typography.Text>
        </Space>
      ),
    },
    {
      title: 'Recovery',
      key: 'actions',
      fixed: 'right',
      width: 205,
      render: (_, row) => {
        const terminal = ['SUCCESS', 'FAILURE'].includes(String(row.state || '').toUpperCase());
        return (
          <Space wrap>
            {!terminal && (
              <Button size='small' loading={busyId === row.id} onClick={() => reconcile(row)}>
                Reconcile
              </Button>
            )}
            {row.allocation_id && (
              <Button size='small' onClick={() => setSelectedAttempt(row)}>
                Allocation finance
              </Button>
            )}
          </Space>
        );
      },
    },
  ];

  return (
    <>
      <section aria-label='Pending collection recovery' className='mt-4'>
        <Space align='center' className='w-100 justify-content-between' wrap>
          <div>
            <Typography.Title level={4} style={{ marginBottom: 2 }}>Pending collection recovery</Typography.Title>
            <Typography.Text type='secondary'>
              Original Product Cart collection attempts, including records without a completed Order transaction.
            </Typography.Text>
          </div>
          <Space wrap>
            <Checkbox checked={showTerminal} onChange={(event) => setShowTerminal(event.target.checked)}>
              Include terminal history
            </Checkbox>
            <Button onClick={loadAttempts} loading={loading}>Refresh attempts</Button>
          </Space>
        </Space>
        {error && (
          <Alert
            className='mt-3'
            type='error'
            showIcon
            message='Collection recovery unavailable'
            description={error}
            closable
            onClose={() => setError('')}
          />
        )}
        {notice && (
          <Alert
            className='mt-3'
            type='warning'
            showIcon
            message='Collection status not confirmed'
            description={notice}
            closable
            onClose={() => setNotice('')}
          />
        )}
        <Table
          className='mt-3'
          size='small'
          scroll={{ x: 1250 }}
          rowKey={(record) => record.id}
          columns={columns}
          dataSource={visibleAttempts}
          loading={loading}
          pagination={{ pageSize: 8, showSizeChanger: true, pageSizeOptions: ['8', '16', '32'] }}
          locale={{ emptyText: error ? 'Collection attempt list could not be loaded.' : 'No collection attempts in this view.' }}
        />
      </section>

      <Modal
        title={`Allocation finance${selectedAttempt?.allocation_id ? ` · ${selectedAttempt.allocation_id}` : ''}`}
        visible={!!selectedAttempt}
        onCancel={() => setSelectedAttempt(null)}
        footer={<Button onClick={() => setSelectedAttempt(null)}>Close</Button>}
        className='pending-collection-recovery-modal'
      >
        {selectedAttempt && (
          <>
            <Descriptions bordered size='small' column={1} className='mb-3 pending-collection-recovery-modal__details'>
              <Descriptions.Item label='Original collection attempt'>{selectedAttempt.id}</Descriptions.Item>
              <Descriptions.Item label='Attempt state'>{selectedAttempt.state || 'Unknown'}</Descriptions.Item>
              <Descriptions.Item label='Provider reference'>{selectedAttempt.provider_reference || 'Not reported'}</Descriptions.Item>
              <Descriptions.Item label='Provider payment ID'>{selectedAttempt.provider_payment_id || 'Not reported'}</Descriptions.Item>
            </Descriptions>
            <FinancialOperationsPanel
              canonicalFinance={{
                allocation_id: selectedAttempt.allocation_id,
                currency_code: selectedAttempt.currency_code,
                money_scale: selectedAttempt.money_scale,
              }}
              role='admin'
            />
          </>
        )}
      </Modal>
    </>
  );
}