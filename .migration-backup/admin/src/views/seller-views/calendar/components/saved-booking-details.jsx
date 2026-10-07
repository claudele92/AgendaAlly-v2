import React, { useEffect, useState } from 'react';
import { Alert, Button, Descriptions, Spin, Typography } from 'antd';
import moment from 'moment';
import bookingService from 'services/seller/booking';
import numberToPrice from 'helpers/numberToPrice';
import { useNavigate } from 'react-router-dom';

/** Saved authorized data only. Viewing never calculates, changes or collects. */
export default function SavedBookingDetails({ id }) {
  const [record, setRecord] = useState(null);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const [retry, setRetry] = useState(0);
  const navigate = useNavigate();
  useEffect(() => {
    let current = true;
    setRecord(null);
    setError('');
    if (!id) {
      setError('This appointment has no valid booking reference.');
      return undefined;
    }
    setLoading(true);
    bookingService.getById(id, {
      preserveAuthOnForbidden: true,
      suppressErrorToast: true,
    }).then(({ data }) => {
      if (!data?.id) throw new Error('The saved booking could not be loaded.');
      if (current) setRecord(data);
    }).catch((e) => {
      if (current) setError(e?.response?.status === 403
        ? 'You do not have access to this booking. Close this view or retry if your access has changed.'
        : e?.response?.data?.message || 'Unable to load this authorized booking.');
    }).finally(() => {
      if (current) setLoading(false);
    });
    return () => { current = false; };
  }, [id, retry]);
  const client = record?.local_client?.name ||
    [record?.user?.firstname, record?.user?.lastname].filter(Boolean).join(' ') || 'Not supplied';
  const specialist = [record?.master?.firstname, record?.master?.lastname].filter(Boolean).join(' ') || 'Not supplied';
  const start = record?.start_date ? moment.utc(record.start_date) : null;
  const end = record?.end_date ? moment.utc(record.end_date) : null;
  const duration = start?.isValid() && end?.isValid() ? end.diff(start, 'minutes') : null;
  const collection = record?.local_client_id
    ? 'UNPAID / UNCOLLECTED'
    : (record?.transaction?.status || record?.transactions?.[0]?.status || 'Collection not recorded');
  return (
    <Spin spinning={loading}>
      <Typography.Title level={4}>Saved booking details</Typography.Title>
      {error && <Alert type='error' showIcon message={error} action={
        <Button onClick={() => setRetry((n) => n + 1)}>Retry</Button>
      } />}
      {record && <>
        <Descriptions column={1} bordered size='small' style={{ overflowWrap: 'anywhere' }}>
          <Descriptions.Item label='Reference'>#{record.id}</Descriptions.Item>
          <Descriptions.Item label='Service'>{record.service_master?.service?.translation?.title || 'Not supplied'}</Descriptions.Item>
          <Descriptions.Item label='Client'>{client}</Descriptions.Item>
          <Descriptions.Item label='Specialist'>{specialist}</Descriptions.Item>
          <Descriptions.Item label='Shop'>{record.shop?.translation?.title || 'Not supplied'}</Descriptions.Item>
          <Descriptions.Item label='Date'>{start?.isValid() ? start.format('D MMM YYYY') : 'Not supplied'}</Descriptions.Item>
          <Descriptions.Item label='Time'>{start?.isValid() && end?.isValid()
            ? `${start.format('HH:mm')}–${end.format('HH:mm')}` : 'Not supplied'}</Descriptions.Item>
          <Descriptions.Item label='Saved duration'>{duration !== null ? `${duration} minutes` : 'Not supplied'}</Descriptions.Item>
          <Descriptions.Item label='Status'>{record.status || 'Not supplied'}</Descriptions.Item>
          <Descriptions.Item label='Saved total'>{record.total_price !== undefined
            ? numberToPrice(record.total_price, record.currency?.symbol) : 'Not supplied'}</Descriptions.Item>
          <Descriptions.Item label='Collection'>{collection}</Descriptions.Item>
          <Descriptions.Item label='Notes'>{Array.isArray(record.notes)
            ? (record.notes.join('\n') || 'No saved notes') : (record.notes || 'No saved notes')}</Descriptions.Item>
        </Descriptions>
        <Button className='mt-3' onClick={() => navigate(`/seller/bookings?bookingId=${record.id}`)}>
          Open existing booking controls
        </Button>
        <Typography.Paragraph type='secondary'>Available actions remain governed by the existing Bookings permissions.</Typography.Paragraph>
      </>}
    </Spin>
  );
}