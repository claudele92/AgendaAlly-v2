import { useTranslation } from 'react-i18next';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import React, { useEffect, useState } from 'react';
import { Divider, Table, Typography } from 'antd';
import { EyeOutlined } from '@ant-design/icons';
import Filter from './filter';
import DetailModal from './detailModal';
import { disableRefetch, setMenuData } from 'redux/slices/menu';
import formatSortType from 'helpers/formatSortType';
import useDidUpdate from 'helpers/useDidUpdate';
import { fetchSellerUserMemberships } from 'redux/slices/user-memberships';
import numberToPrice from 'helpers/numberToPrice';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';

function UserMemberships() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [filters, setFilters] = useState({
    shop: {},
  });
  const { userMemberships, loading, meta, params } = useSelector(
    (state) => state.userMemberships,
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
    service_id: data?.service_id,
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
      title: t('user.id'),
      dataIndex: 'user_id',
      key: 'user_id',
      is_show: true,
    },
    {
      title: t('membership'),
      dataIndex: 'membership',
      key: 'membership',
      is_show: true,
      render: (_, record) => record?.member_ship?.translation?.title,
    },
    {
      title: t('price'),
      dataIndex: 'price',
      key: 'price',
      is_show: true,
      render: (_, record) => numberToPrice(record?.price),
    },
    {
      title: t('sessions'),
      dataIndex: 'sessions',
      key: 'sessions',
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
            {t(transaction?.status)}
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
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            onClick={(e) => {
              e.stopPropagation();
              goToShow(row);
            }}
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
    dispatch(fetchSellerUserMemberships(paramsData));
  }, [activeMenu.data]);

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(disableRefetch(activeMenu));
      dispatch(fetchSellerUserMemberships(paramsData));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  return (
    <>
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
          {t('user.memberships')}
        </Typography.Title>
        <Divider color='var(--divider)' />
        <Filter filters={filters} setFilters={setFilters} />
        <Divider color='var(--divider)' />
        <Table
          scroll={{ x: true }}
          columns={columns?.filter((item) => item.is_show)}
          dataSource={userMemberships}
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
        <DetailModal id={showId} handleCancel={() => setShowId(null)} />
      )}
    </>
  );
}

export default UserMemberships;
