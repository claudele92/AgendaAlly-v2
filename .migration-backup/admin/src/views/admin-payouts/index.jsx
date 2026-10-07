import React, { useEffect, useState } from 'react';
import { Button, Divider, Space, Table, Tabs, Typography } from 'antd';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import useDidUpdate from 'helpers/useDidUpdate';
import formatSortType from 'helpers/formatSortType';
import { useTranslation } from 'react-i18next';
import numberToPrice from 'helpers/numberToPrice';
import { EditOutlined, PlusOutlined, SettingOutlined } from '@ant-design/icons';
import PayoutRequestModal from './payoutActionModal';
import FilterColumns from 'components/filter-column';
import { useNavigate } from 'react-router-dom';
import { fetchAdminPayouts } from 'redux/slices/adminPayouts';
import PayoutStatusChangeModal from './payoutStatusChangeModal';
import moment from 'moment';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';

const { TabPane } = Tabs;

const roles = ['all', 'accepted', 'pending', 'canceled'];

export default function AdminPayouts() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const hourFormat = useSelector(
    (state) => state.globalSettings.settings.hour_format,
  );
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [role, setRole] = useState('all');
  const immutable = activeMenu.data?.role || role;
  const data = activeMenu.data;
  const paramsData = {
    sort: data?.sort,
    column: data?.column,
    perPage: data?.perPage,
    page: data?.page,
    status: role === 'all' ? undefined : immutable,
  };

  const { payoutRequests, meta, loading, params } = useSelector(
    (state) => state.adminPayouts,
    shallowEqual,
  );
  const [modal, setModal] = useState(null);
  const [selectedRow, setSelectedRow] = useState(null);

  const goToUser = (row) => {
    dispatch(
      addMenu({
        url: `/users/user/${row.uuid}`,
        id: 'user_info',
        name: t('user.info'),
      }),
    );
    navigate(`/users/user/${row.uuid}`, { state: { user_id: row.id } });
  };

  const initialColumns = [
    {
      title: t('user'),
      dataIndex: 'createdBy',
      key: 'createdBy',
      is_show: true,
      render: (user) => (
        <div className='text-hover' onClick={() => goToUser(user)}>
          {user?.firstname + ' ' + user?.lastname}
        </div>
      ),
    },
    {
      title: t('price'),
      dataIndex: 'price',
      key: 'price',
      is_show: true,
      render: (price, row) =>
        numberToPrice(price, row.currency?.symbol, row?.currency?.position),
    },
    {
      title: t('status'),
      dataIndex: 'status',
      key: 'status',
      is_show: true,
      render: (status) => (
        <div className={tableRowClasses.status}>
          <span className={tableRowClasses[status || 'new']}>{t(status)}</span>
        </div>
      ),
    },
    {
      title: t('cause'),
      dataIndex: 'cause',
      key: 'cause',
      is_show: true,
    },
    {
      title: t('created.at'),
      dataIndex: 'created_at',
      key: 'created_at',
      is_show: true,
      render: (created_at) =>
        moment(created_at).format(`YYYY-MM-DD ${hourFormat}`),
    },
    {
      title: t('answer'),
      dataIndex: 'answer',
      key: 'answer',
      is_show: true,
    },
    {
      title: t('options'),
      dataIndex: 'uuid',
      key: 'uuid',
      is_show: true,
      render: (uuid, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            onClick={(e) => {
              e.stopPropagation();
              setModal(row);
            }}
          >
            <EditOutlined />
          </button>
          {row?.status !== 'accepted' && (
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.details}`}
              onClick={(e) => {
                e.stopPropagation();
                setSelectedRow(row);
              }}
            >
              <SettingOutlined />
            </button>
          )}
        </div>
      ),
    },
  ];

  const [columns, setColumns] = useState(initialColumns);

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchAdminPayouts(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    dispatch(fetchAdminPayouts(paramsData));
  }, [activeMenu.data]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  function onChangePagination(pagination, filters, sorter) {
    const { pageSize: perPage, current: page } = pagination;
    const { field: column, order } = sorter;
    const sort = formatSortType(order);
    dispatch(
      setMenuData({ activeMenu, data: { perPage, page, column, sort } }),
    );
  }

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
          {t('payouts')}
        </Typography.Title>
        <Space>
          <Button
            type='primary'
            icon={<PlusOutlined />}
            onClick={() => setModal(true)}
            style={{ width: '100%' }}
          >
            {t('create.payout')}
          </Button>
          <FilterColumns columns={columns} setColumns={setColumns} />
        </Space>
      </Space>
      <Divider color='var(--divider)' />
      <Tabs
        className='mt-3'
        activeKey={immutable}
        onChange={(key) => {
          handleFilter({ role: key, page: 1 });
          setRole(key);
        }}
        type='card'
      >
        {roles.map((item) => (
          <TabPane tab={t(item)} key={item} />
        ))}
      </Tabs>
      <Table
        scroll={{ x: true }}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={payoutRequests}
        pagination={{
          pageSize: params.perPage,
          page: params.page,
          total: meta.total,
          defaultCurrent: params.page,
        }}
        rowKey={(record) => record.id}
        onChange={onChangePagination}
        loading={loading}
      />
      {modal && (
        <PayoutRequestModal data={modal} handleCancel={() => setModal(null)} />
      )}
      {selectedRow && (
        <PayoutStatusChangeModal
          data={selectedRow}
          statuses={roles}
          handleCancel={() => setSelectedRow(null)}
        />
      )}
    </Card>
  );
}
