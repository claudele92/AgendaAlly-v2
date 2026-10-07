import React, { useEffect, useState } from 'react';
import { Button, Descriptions, Modal, Tag } from 'antd';
import { useTranslation } from 'react-i18next';
import Loading from '../../components/loading';
import transactionService from '../../services/transaction';
import numberToPrice from '../../helpers/numberToPrice';
import moment from 'moment';
import { getHourFormat } from '../../helpers/getHourFormat';
import CanonicalFinancePanel from 'components/payment/canonical-finance-panel';
import FinancialOperationsPanel from 'components/payment/financial-operations-panel';

export default function TransactionShowModal({ id, handleCancel }) {
  const [data, setData] = useState({});
  const [loading, setLoading] = useState(false);
  const { t } = useTranslation();
  const hourFormat = getHourFormat();

  function fetchTransaction(transactionId) {
    setLoading(true);
    return transactionService
      .getById(transactionId)
      .then((res) => setData(res.data))
      .finally(() => setLoading(false));
  }

  useEffect(() => {
    fetchTransaction(id);
  }, [id]);

  return (
    <Modal
      className='transaction-show-modal'
      visible={!!id}
      title={t('transaction')}
      onCancel={handleCancel}
      footer={
        <Button type='default' onClick={handleCancel}>
          {t('cancel')}
        </Button>
      }
    >
      {!loading || Number(data.id) === Number(id) ? (
        <>
        <Descriptions bordered column={1} className='transaction-show-modal__details'>
          <Descriptions.Item label={t('transaction.id')}>
            {data.id}
          </Descriptions.Item>
          <Descriptions.Item label={t('client')}>
            {data.user?.firstname} {data.user?.lastname || ''}
          </Descriptions.Item>
          <Descriptions.Item label={t('price')}>
            {numberToPrice(data.price, data.payable?.order?.currency?.symbol)}
          </Descriptions.Item>
          <Descriptions.Item label={t('payment.type')}>
            {t(data.payment_system?.tag)}
          </Descriptions.Item>
          <Descriptions.Item label={t('created.at')}>
            {moment(data.created_at).format(`DD.MM.YYYY ${hourFormat}`)}
          </Descriptions.Item>
          <Descriptions.Item label={t('status')}>
            {data.status === 'progress' ? (
              <Tag color='gold'>{t(data.status)}</Tag>
            ) : data.status === 'rejected' ? (
              <Tag color='error'>{t(data.status)}</Tag>
            ) : (
              <Tag color='cyan'>{t(data.status)}</Tag>
            )}
          </Descriptions.Item>
          <Descriptions.Item label={t('status.description')}>
            {data.status_description}
          </Descriptions.Item>
          <Descriptions.Item label={t('note')}>
            {data.note}
          </Descriptions.Item>
        </Descriptions>
        <CanonicalFinancePanel canonicalFinance={data.canonical_finance} />
        <FinancialOperationsPanel
          canonicalFinance={data.canonical_finance}
          role='admin'
          onRefresh={() => fetchTransaction(id)}
        />
        </>
      ) : (
        <Loading />
      )}
    </Modal>
  );
}
