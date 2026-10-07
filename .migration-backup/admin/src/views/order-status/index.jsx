import React, { useContext, useEffect, useState } from 'react';
import { Divider, Switch, Table, Typography } from 'antd';
import CustomModal from 'components/modal';
import { Context } from 'context/context';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { disableRefetch } from 'redux/slices/menu';
import OrderStatusService from 'services/orderStatus';
import { fetchOrderStatus } from 'redux/slices/orderStatus';
import { toast } from 'react-toastify';
import { useTranslation } from 'react-i18next';
import Card from 'components/card';
import useDidUpdate from 'helpers/useDidUpdate';

const OrderStatus = () => {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
    },
    {
      title: t('name'),
      dataIndex: 'name',
      key: 'name',
    },
    {
      title: t('active'),
      dataIndex: 'active',
      key: 'active',
      render: (active, row) => (
        <Switch
          key={row.id + active}
          onChange={() => {
            setIsModalVisible(true);
            setActiveId(row.id);
          }}
          checked={active}
          disabled={
            row.name === 'canceled' ||
            row.name === 'delivered' ||
            row.name === 'accepted'
          }
        />
      ),
    },
  ];

  const [columns, setColumns] = useState(initialColumns);

  const { setIsModalVisible } = useContext(Context);
  const [activeId, setActiveId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { statusList, loading } = useSelector(
    (state) => state.orderStatus,
    shallowEqual,
  );

  const handleActive = () => {
    setLoadingBtn(true);
    OrderStatusService.status(activeId)
      .then(() => {
        setIsModalVisible(false);
        dispatch(fetchOrderStatus({}));
        toast.success(t('successfully.updated'));
      })
      .finally(() => setLoadingBtn(false));
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchOrderStatus({}));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  return (
    <Card headerTitle={t('order.status')}>
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
        {t('order.statuses')}
      </Typography.Title>
      <Divider color='var(--divider)' />
      <Table
        scroll={{ x: true }}
        columns={columns}
        dataSource={statusList}
        rowKey={(record) => record.id}
        loading={loading}
      />
      <CustomModal
        click={handleActive}
        text={t('set.active.order.status')}
        loading={loadingBtn}
      />
    </Card>
  );
};

export default OrderStatus;
