import React, { useEffect, useState } from 'react';
import { EditOutlined, PlusOutlined } from '@ant-design/icons';
import { Button, Divider, Space, Table, Typography } from 'antd';
import { batch, shallowEqual, useDispatch, useSelector } from 'react-redux';
import { disableRefetch, setMenuData } from 'redux/slices/menu';
import useDidUpdate from 'helpers/useDidUpdate';
import formatSortType from 'helpers/formatSortType';
import { useTranslation } from 'react-i18next';
import { fetchSellerWallets } from 'redux/slices/wallet';
import numberToPrice from 'helpers/numberToPrice';
import PayoutStatusModal from './payoutStatusModal';
import PayoutRequest from './payoutRequest';
import FilterColumns from 'components/filter-column';
import moment from 'moment';
import { getHourFormat } from 'helpers/getHourFormat';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';

export default function SellerPayouts() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const [modal, setModal] = useState(null);
  const [withdrawModal, setWithdrawModal] = useState(false);
  const { defaultCurrency } = useSelector(
    (state) => state.currency,
    shallowEqual,
  );
  const hourFormat = getHourFormat();

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
      render: (price) => numberToPrice(price, defaultCurrency?.symbol),
    },
    {
      title: t('status'),
      dataIndex: 'status',
      key: 'status',
      is_show: true,
    },
    {
      title: t('status'),
      dataIndex: 'status',
      is_show: true,
      render: (status, row) => (
        <button
          type='button'
          onClick={() => {
            setModal(row);
          }}
          className={tableRowClasses.status}
        >
          <span className={tableRowClasses[status || 'pending']}>
            {t(status)}
          </span>
          <EditOutlined />
        </button>
      ),
    },
    {
      title: t('note'),
      dataIndex: 'note',
      key: 'note',
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
  ];
  const [columns, setColumns] = useState(initialColumns);

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { wallets, meta, loading, params } = useSelector(
    (state) => state.wallet,
    shallowEqual,
  );

  useEffect(() => {
    if (activeMenu.refetch) {
      batch(() => {
        dispatch(fetchSellerWallets({}));
        dispatch(disableRefetch(activeMenu));
      });
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    const data = activeMenu.data;
    const paramsData = {
      sort: data?.sort,
      column: data?.column,
      perPage: data?.perPage,
      page: data?.page,
    };
    dispatch(fetchSellerWallets(paramsData));
    dispatch(disableRefetch(activeMenu));
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
            onClick={() => setWithdrawModal(true)}
            style={{ width: '100%' }}
          >
            {t('add.payout')}
          </Button>
          <FilterColumns columns={columns} setColumns={setColumns} />
        </Space>
      </Space>
      <Divider color='var(--divider)' />
      <Table
        scroll={{ x: true }}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={wallets}
        pagination={{
          pageSize: params?.perPage,
          page: params?.page,
          current: params?.page,
          total: meta?.total,
          defaultCurrent: params?.page,
        }}
        rowKey={(record) => record.id}
        onChange={onChangePagination}
        loading={loading}
      />
      {!!modal && (
        <PayoutStatusModal data={modal} handleCancel={() => setModal(null)} />
      )}
      {!!withdrawModal && (
        <PayoutRequest
          data={withdrawModal}
          handleCancel={() => setWithdrawModal(false)}
        />
      )}
    </Card>
  );
}
