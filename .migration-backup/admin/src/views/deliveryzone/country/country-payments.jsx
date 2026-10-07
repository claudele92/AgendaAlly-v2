import React, { useEffect, useState } from 'react';
import { Button, Space, Switch, Table } from 'antd';
import { useTranslation } from 'react-i18next';
import { toast } from 'react-toastify';
import paymentService from 'services/payment';
import countryService from 'services/deliveryzone/country';

export default function CountryPayments({ countryId }) {
  const { t } = useTranslation();
  const [loading, setLoading] = useState(true);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [rows, setRows] = useState([]);

  useEffect(() => {
    setLoading(true);
    Promise.all([paymentService.getAll(), countryService.show(countryId)])
      .then(([paymentsRes, countryRes]) => {
        const countryPayments = countryRes.data?.payments || [];
        setRows(
          paymentsRes.data.map((payment) => {
            const existing = countryPayments.find((p) => p.id === payment.id);
            return {
              payment_id: payment.id,
              tag: payment.tag,
              active: existing ? !!existing.country_active : false,
            };
          }),
        );
      })
      .finally(() => setLoading(false));
  }, [countryId]);

  const toggle = (paymentId) => {
    setRows((prev) =>
      prev.map((row) =>
        row.payment_id === paymentId ? { ...row, active: !row.active } : row,
      ),
    );
  };

  const handleSave = () => {
    setLoadingBtn(true);
    countryService
      .updatePayments(countryId, {
        payments: rows.map(({ payment_id, active }) => ({
          payment_id,
          active,
        })),
      })
      .then(() => toast.success(t('successfully.updated')))
      .finally(() => setLoadingBtn(false));
  };

  const columns = [
    {
      title: t('title'),
      dataIndex: 'tag',
      key: 'tag',
    },
    {
      title: t('active'),
      dataIndex: 'active',
      key: 'active',
      render: (active, row) => (
        <Switch checked={active} onChange={() => toggle(row.payment_id)} />
      ),
    },
  ];

  return (
    <Space direction='vertical' className='w-100'>
      <Table
        columns={columns}
        dataSource={rows}
        rowKey='payment_id'
        loading={loading}
        pagination={false}
      />
      <Button type='primary' loading={loadingBtn} onClick={handleSave}>
        {t('submit')}
      </Button>
    </Space>
  );
}
