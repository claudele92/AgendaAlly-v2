import { Button, Card, Divider, Space, Table, Tag, Typography } from 'antd';
import numberToPrice from 'helpers/numberToPrice';
import moment from 'moment';
import React, { useCallback, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useDispatch } from 'react-redux';
import { useSelector } from 'react-redux';
import { disableRefetch } from 'redux/slices/menu';
import userService from 'services/user';
import { PlusOutlined } from '@ant-design/icons';
import { getHourFormat } from 'helpers/getHourFormat';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import formatSortType from 'helpers/formatSortType';
import WalletTopUp from './top-up';

const SellerWallet = () => {
  const [user, setUser] = useState(null);
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const [loading, setLoading] = useState(false);
  const [open, setOpen] = useState(false);
  const { activeMenu } = useSelector((state) => state.menu);
  const hourFormat = getHourFormat();
  const getProfile = useCallback((params) => {
    setLoading(true);
    userService
      .profileShow(params)
      .then((res) => {
        setUser(res.data);
      })
      .finally(() => {
        setLoading(false);
      });
  }, []);

  useEffect(() => {
    if (activeMenu?.refetch) {
      getProfile({});
    }
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu?.refetch]);

  const columns = [
    {
      title: t('created.at'),
      dataIndex: 'created_at',
      render: (created_at) =>
        moment(created_at).format(`YYYY-MM-DD ${hourFormat}`),
      sorter: true,
    },
    {
      title: t('order.status'),
      is_show: true,
      dataIndex: 'status',
      key: 'status',
      render: (status) => {
        return (
          <div className={tableRowClasses.status}>
            <div className={tableRowClasses[status]}>
              <span className={tableRowClasses[status || 'new']}>
                {t(status)}
              </span>
            </div>
          </div>
        );
      },
    },
    {
      title: t('type'),
      dataIndex: 'type',
      render: (type) => t(type),
    },
    {
      title: t('note'),
      dataIndex: 'note',
    },
  ];

  function onChangePagination(pagination, filter, sorter) {
    const { field: column, order } = sorter;
    const sort = formatSortType(order);
    getProfile({ column, sort });
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
          {t('user.wallet')}
        </Typography.Title>
        {!loading ? (
          <Button
            type='primary'
            icon={<PlusOutlined />}
            onClick={() => setOpen(true)}
            style={{ width: '100%' }}
          >
            {t('top.up')}
          </Button>
        ) : null}
      </Space>
      <Divider color='var(--divider)' />
      {user?.wallet ? (
        <>
          <Typography.Title>
            {numberToPrice(user?.wallet?.price)}
          </Typography.Title>
          <Table
            loading={loading}
            dataSource={user?.wallet?.histories}
            columns={columns}
            onChange={onChangePagination}
          />
        </>
      ) : (
        <Table dataSource={[]} columns={columns} loading={loading} />
      )}
      {open && (
        <WalletTopUp
          refetch={getProfile}
          open={open}
          handleCancel={() => setOpen(false)}
        />
      )}
    </Card>
  );
};

export default SellerWallet;
