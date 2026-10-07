import React, { useEffect, useState } from 'react';
import { Col, Divider, Space, Table, Tabs, Typography } from 'antd';
import { useNavigate } from 'react-router-dom';
import { EyeOutlined } from '@ant-design/icons';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import { useTranslation } from 'react-i18next';
import useDidUpdate from 'helpers/useDidUpdate';
import { fetchDeliverymanOrders } from 'redux/slices/orders';
import formatSortType from 'helpers/formatSortType';
import SearchInput from 'components/search-input';
import numberToPrice from 'helpers/numberToPrice';
import FilterColumns from 'components/filter-column';
import { fetchRestOrderStatus } from 'redux/slices/orderStatus';
import moment from 'moment';
import { getHourFormat } from 'helpers/getHourFormat';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';

const { TabPane } = Tabs;

export default function DeliverymanOrders() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { t } = useTranslation();
  const { defaultCurrency } = useSelector(
    (state) => state.currency,
    shallowEqual,
  );
  const { statusList } = useSelector(
    (state) => state.orderStatus,
    shallowEqual,
  );

  const statuses = [
    { name: 'all', id: '0', active: true, sort: 0 },
    ...statusList,
  ];

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [role, setRole] = useState('accepted');
  const immutable = activeMenu.data?.role || role;
  const data = activeMenu?.data;
  const paramsData = {
    search: data?.search || undefined,
    sort: data?.sort,
    column: data?.column,
    perPage: data?.perPage,
    page: data?.page,
    user_id: data?.userId,
    status: immutable === 'all' ? undefined : immutable,
  };
  const hourFormat = getHourFormat();

  const goToShow = (row) => {
    dispatch(
      addMenu({
        url: `deliveryman/order/details/${row.id}`,
        id: 'order_details',
        name: t('order.details'),
      }),
    );
    navigate(`/deliveryman/order/details/${row.id}`);
  };

  const [columns, setColumns] = useState([
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      sorter: true,
      is_show: true,
    },
    {
      title: t('client'),
      dataIndex: 'user',
      key: 'user',
      is_show: true,
      render: (user) => user?.full_name || t('N/A'),
    },
    {
      title: t('number.of.products'),
      dataIndex: 'order_details_count',
      key: 'rate',
      is_show: true,
      render: (order_details_count) => (
        <div className='text-lowercase'>
          {order_details_count || 0} {t('products')}
        </div>
      ),
    },
    {
      title: t('amount'),
      dataIndex: 'details',
      key: 'price',
      is_show: true,
      render: (_, row) =>
        numberToPrice(row?.total_price, defaultCurrency.symbol),
    },
    {
      title: t('payment.type'),
      dataIndex: 'transaction',
      key: 'transaction',
      is_show: true,
      render: (transaction) => t(transaction?.payment_system?.tag),
    },
    {
      title: 'created.at',
      dataIndex: 'created_at',
      key: 'created_at',
      is_show: true,
      render: (created_at) =>
        moment(created_at).format(`YYYY-MM-DD ${hourFormat}`),
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
  ]);

  const { orders, meta, loading, params } = useSelector(
    (state) => state.orders,
    shallowEqual,
  );

  function onChangePagination(pagination, filter, sorter) {
    const { pageSize: perPage, current: page } = pagination;
    const { field: column, order } = sorter;
    const sort = formatSortType(order);
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...activeMenu.data, perPage, page, column, sort },
      }),
    );
  }

  useDidUpdate(() => {
    dispatch(fetchDeliverymanOrders(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu?.data]);

  useEffect(() => {
    if (activeMenu?.refetch) {
      dispatch(fetchDeliverymanOrders(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu?.refetch]);

  useEffect(() => {
    dispatch(fetchRestOrderStatus({}));
  }, []);

  const handleFilter = (items) => {
    const data = activeMenu.data;
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...data, ...items },
      }),
    );
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
        {t('orders')}
      </Typography.Title>
      <Divider color='var(--divider)' />
      <div
        style={{
          display: 'flex',
          flexWrap: 'wrap',
          rowGap: '6px',
          columnGap: '6px',
        }}
      >
        <Col style={{ minWidth: '228px' }}>
          <SearchInput
            placeholder={t('search')}
            handleChange={(e) => handleFilter({ search: e })}
            defaultValue={activeMenu.data?.search}
            resetSearch={!activeMenu.data?.search}
          />
        </Col>
      </div>
      <Divider color='var(--divider)' />
      <Space className='w-100 justify-content-between align-items-start'>
        <Tabs
          onChange={(key) => {
            handleFilter({ role: key, page: 1 });
            setRole(key);
          }}
          type='card'
          activeKey={immutable}
        >
          {statuses
            .filter((item) => item.active)
            .map((item) => (
              <TabPane tab={t(item.name)} key={item.name} />
            ))}
        </Tabs>
        <Space className='gap-10'>
          <FilterColumns columns={columns} setColumns={setColumns} />
        </Space>
      </Space>
      <Table
        scroll={{ x: true }}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={orders}
        loading={loading}
        pagination={{
          pageSize: params.perPage,
          page: params.page,
          total: meta.total,
          defaultCurrent: params.page,
        }}
        rowKey={(record) => record.id}
        onChange={onChangePagination}
      />
    </Card>
  );
}
