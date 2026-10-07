import { Divider, Table, Typography } from 'antd';
import React, { useEffect, useState } from 'react';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import { disableRefetch } from 'redux/slices/menu';
import { fetchMySubscriptions } from 'redux/slices/my-subscriptions';
import { fetchSellerAdverts } from 'redux/slices/advert';
import numberToPrice from 'helpers/numberToPrice';
import Card from 'components/card';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import moment from 'moment/moment';
import { getHourFormat } from 'helpers/getHourFormat';
import useDidUpdate from 'helpers/useDidUpdate';

function MySubscriptions() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const hourFormat = getHourFormat();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { mySubscriptions, loading, meta } = useSelector(
    (state) => state.mySubscriptions,
    shallowEqual,
  );

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      is_show: true,
      sorter: (a, b) => a.id - b.id,
    },
    {
      title: t('name'),
      dataIndex: 'subscription',
      is_show: true,
      render: (subscription) => subscription.title,
    },
    {
      title: t('price'),
      dataIndex: 'price',
      is_show: true,
      render: (price) => numberToPrice(price),
    },
    {
      title: t('expired.at'),
      dataIndex: 'expired_at',
      is_show: true,
      render: (_, row) =>
        moment(row?.expired_at).format(`YYYY-MM-DD ${hourFormat}`),
    },
    {
      title: t('transaction'),
      dataIndex: 'transaction',
      is_show: true,
      render: (transaction) => (
        <div className={tableRowClasses.paymentStatuses}>
          <span
            className={`${tableRowClasses.paymentStatus} ${tableRowClasses[transaction?.status || 'progress']}`}
          >
            {t(transaction?.status || 'not.paid')}
          </span>
        </div>
      ),
    },
  ];

  const [columns, setColumns] = useState(initialColumns);

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchMySubscriptions({}));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;
    dispatch(fetchSellerAdverts({ perPage: pageSize, page: current }));
    dispatch(disableRefetch(activeMenu));
  };

  return (
    <Card>
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
        {t('my.subscriptions')}
      </Typography.Title>
      <Divider color='var(--divider)' />
      <Table
        scroll={{ x: true }}
        dataSource={mySubscriptions}
        columns={columns?.filter((item) => item.is_show)}
        rowKey={(record) => record.id}
        loading={loading}
        pagination={{
          pageSize: meta?.per_page,
          page: meta?.current_page,
          total: meta?.total,
        }}
        onChange={onChangePagination}
      />
    </Card>
  );
}

export default MySubscriptions;
