import { useTranslation } from 'react-i18next';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import React, { useEffect, useState } from 'react';
import numberToPrice from 'helpers/numberToPrice';
import { Divider, Table, Typography } from 'antd';
import { EyeOutlined } from '@ant-design/icons';
import formatSortType from 'helpers/formatSortType';
import { disableRefetch, setMenuData } from 'redux/slices/menu';
import useDidUpdate from 'helpers/useDidUpdate';
import Filter from './filter';
import DetailModal from './detailModal';
import { fetchUserGiftCards } from 'redux/slices/user-gift-cards';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';

function UserGiftCards() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [filters, setFilters] = useState({
    shop: {},
  });
  const { userGiftCards, loading, meta, params } = useSelector(
    (state) => state.userGiftCards,
    shallowEqual,
  );
  const { defaultCurrency } = useSelector(
    (state) => state.currency,
    shallowEqual,
  );
  const [showId, setShowId] = useState(null);

  const data = activeMenu.data;

  const paramsData = {
    sort: data?.sort,
    column: data?.column,
    perPage: data?.perPage,
    page: data?.page,
    shop_id: data?.shop_id,
    user_id: data?.user_id,
  };

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      sorter: true,
      is_show: true,
    },
    {
      title: t('user'),
      dataIndex: 'user',
      key: 'user',
      is_show: true,
      render: (_, record) =>
        `${record.user?.firstname || ''} ${record.user?.lastname || ''}`,
    },
    {
      title: t('gift.card'),
      dataIndex: 'giftCart',
      key: 'gift.card',
      is_show: true,
      render: (_, record) => record?.giftCart?.translation?.title,
    },
    {
      title: t('time'),
      dataIndex: 'time',
      key: 'time',
      is_show: true,
      render: (_, record) => record?.giftCart?.time,
    },
    {
      title: t('price'),
      dataIndex: 'price',
      key: 'price',
      is_show: true,
      render: (_, record) =>
        numberToPrice(
          record?.price,
          defaultCurrency?.symbol,
          defaultCurrency?.position,
        ),
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
            {t(transaction?.status || 'N/A')}
          </span>
        </div>
      ),
    },
    {
      title: t('actions'),
      key: 'actions',
      is_show: true,
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.details}`}
            onClick={() => goToShow(row)}
          >
            <EyeOutlined />
          </button>
        </div>
      ),
    },
  ];

  const [columns, setColumns] = useState(initialColumns);

  const goToShow = (row) => {
    setShowId(row.id);
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
    dispatch(fetchUserGiftCards(paramsData));
  }, [activeMenu.data]);

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(disableRefetch(activeMenu));
      dispatch(fetchUserGiftCards({}));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

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
        {t('user.gift.cards')}
      </Typography.Title>
      <Divider color='var(--divider)' />
      <Filter filters={filters} setFilters={setFilters} />
      <Divider color='var(--divider)' />
      <Table
        scroll={{ x: true }}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={userGiftCards}
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
      {showId && (
        <DetailModal id={showId} handleCancel={() => setShowId(null)} />
      )}
    </Card>
  );
}

export default UserGiftCards;
