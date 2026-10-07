import { Button, Divider, Space, Table, Typography } from 'antd';
import { useTranslation } from 'react-i18next';
import FilterColumns from 'components/filter-column';
import React, { useEffect, useState } from 'react';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import formatSortType from 'helpers/formatSortType';
import { disableRefetch, setMenuData } from 'redux/slices/menu';
import { fetchShopSubscriptions } from 'redux/slices/shop-subscriptions';
import numberToPrice from 'helpers/numberToPrice';
import { EditOutlined, EyeOutlined } from '@ant-design/icons';
import StatusModal from './status-modal';
import ShopSubscriptionModal from './detail-show-modal';
import useDidUpdate from 'helpers/useDidUpdate';
import Card from 'components/card';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';

function ShopSubscriptions() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { shopSubscriptions, loading, params, meta } = useSelector(
    (state) => state.shopSubscriptions,
    shallowEqual,
  );
  const { defaultCurrency } = useSelector(
    (state) => state.currency,
    shallowEqual,
  );

  const [showId, setShowId] = useState(null);
  const [transactionDetail, setTransactionDetail] = useState(null);

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      sorter: true,
      is_show: true,
    },
    {
      title: t('price'),
      dataIndex: 'price',
      key: 'price',
      is_show: true,
      render: (price) =>
        numberToPrice(
          price,
          defaultCurrency?.symbol,
          defaultCurrency?.position,
        ),
    },
    {
      title: t('type'),
      dataIndex: 'type',
      key: 'type',
      is_show: true,
    },
    {
      title: t('shop'),
      dataIndex: 'shop',
      key: 'shop',
      is_show: true,
      render: (shop) => shop?.translation?.title,
    },
    {
      title: t('subscription.title'),
      dataIndex: 'subscriptionTitle',
      key: 'subscriptionTitle',
      is_show: true,
    },
    {
      title: t('transaction'),
      dataIndex: 'transaction',
      key: 'transaction',
      is_show: true,
      render: (transaction) => (
        <div className={tableRowClasses.paymentStatuses}>
          <span
            className={`${tableRowClasses.paymentStatus} ${tableRowClasses[transaction?.status || 'progress']}`}
          >
            {t(transaction?.status || 'not.paid')}
          </span>
          {transaction?.status && (
            <EditOutlined onClick={() => setTransactionDetail(transaction)} />
          )}
        </div>
      ),
    },
    {
      title: t('options'),
      key: 'options',
      is_show: true,
      render: (_, row) => {
        return (
          <Button icon={<EyeOutlined />} onClick={() => goToShow(row.id)} />
        );
      },
    },
  ];

  const [columns, setColumns] = useState(initialColumns);

  const data = activeMenu.data;

  const paramsData = {
    ...data,
    page: data?.page,
    perPage: data?.perPage,
  };

  const goToShow = (id) => {
    setShowId(id);
  };

  function onChangePagination(pagination, filters, sorter) {
    const { pageSize: perPage, current: page } = pagination;
    const { field: column, order } = sorter;
    const sort = formatSortType(order);
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...data, perPage, page, column, sort },
      }),
    );
  }

  useDidUpdate(() => {
    dispatch(fetchShopSubscriptions(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu.data]);

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchShopSubscriptions(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  return (
    <>
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
            {t('shop.subscriptions')}
          </Typography.Title>
          <FilterColumns columns={columns} setColumns={setColumns} />
        </Space>
        <Divider color='var(--divider)' />
        <Table
          scroll={{ x: true }}
          columns={columns?.filter((item) => item.is_show)}
          dataSource={shopSubscriptions}
          loading={loading}
          pagination={{
            pageSize: params.perPage,
            page: activeMenu.data?.page || 1,
            total: meta.total,
            defaultCurrent: activeMenu.data?.page,
            current: activeMenu.data?.page,
          }}
          rowKey={(record) => record.id}
          onChange={onChangePagination}
        />
      </Card>

      {showId && (
        <ShopSubscriptionModal
          id={showId}
          handleCancel={() => setShowId(null)}
        />
      )}

      {transactionDetail && (
        <StatusModal
          transactionDetails={transactionDetail}
          handleCancel={() => setTransactionDetail(null)}
        />
      )}
    </>
  );
}

export default ShopSubscriptions;
