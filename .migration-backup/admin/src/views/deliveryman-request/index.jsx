import React, { useEffect, useState } from 'react';
import { Table, Space, Typography, Divider } from 'antd';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { disableRefetch, setMenuData } from 'redux/slices/menu';
import { useTranslation } from 'react-i18next';
import { fetchDeliverymanRequest } from 'redux/slices/deliveryman-request';
import useDidUpdate from 'helpers/useDidUpdate';
import formatSortType from 'helpers/formatSortType';
import { EditOutlined, EyeOutlined } from '@ant-design/icons';
import DeliverymanRequestModal from './details-modal';
import StatusChangeModal from './status-change-modal';
import requestAdminModelsService from 'services/request-models';
import { toast } from 'react-toastify';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';
import ColumnImage from 'components/column-image';

export default function DeliverymanRequest() {
  const dispatch = useDispatch();
  const { t, i18n } = useTranslation();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { deliverymanRequestData, meta, loading } = useSelector(
    (state) => state.deliverymanRequest,
    shallowEqual,
  );

  const [id, setId] = useState(null);
  const [details, setDetails] = useState(null);
  const [changeStatus, setChangeStatus] = useState(null);

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      is_show: true,
    },
    {
      title: t('name'),
      dataIndex: 'name',
      key: 'name',
      is_show: true,
      render: (_, row) =>
        `${row?.model?.firstname || ''} ${row?.model?.lastname}`,
    },
    {
      title: t('delivery.man.setting'),
      dataIndex: 'setting',
      key: 'setting',
      is_show: true,
      render: (_, data) => (
        <Space>
          <span>
            {t('brand')}: {data?.data?.brand}
            <br />
            {t('model')}: {data?.data?.model}
            <br />
            {t('number')}: {data?.data?.number}
            <br />
            {t('color')}: {data?.data?.color}
          </span>
        </Space>
      ),
    },
    {
      title: t('image'),
      dataIndex: 'image',
      key: 'image',
      is_show: true,
      render: (img, row) => (
        <ColumnImage size={50} image={row?.data?.['images[0]']} id={row?.id} />
      ),
    },
    {
      title: t('status'),
      dataIndex: 'status',
      key: 'status',
      is_show: true,
      render: (status, row) => (
        <button
          type='button'
          className={tableRowClasses.status}
          onClick={() => {
            setChangeStatus(row);
          }}
        >
          <span className={tableRowClasses[status || 'pending']}>
            {t(status)}
          </span>
          <EditOutlined />
        </button>
      ),
    },
    {
      title: t('actions'),
      key: 'actions',
      dataIndex: 'actions',
      is_show: true,
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.details}`}
            onClick={(e) => {
              e.stopPropagation();
              setDetails(row);
            }}
          >
            <EyeOutlined />
          </button>
        </div>
      ),
    },
  ];

  const [columns, setColumns] = useState(initialColumns);

  const data = activeMenu.data;

  const paramsData = {
    perPage: data?.perPage,
    page: data?.page,
    search: data?.search,
    columns: data?.columns,
  };

  useDidUpdate(() => {
    dispatch(fetchDeliverymanRequest(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu?.data]);

  useEffect(() => {
    if (activeMenu?.refetch) {
      dispatch(fetchDeliverymanRequest(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu?.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

  function onChangePagination(pagination, filter, sorter) {
    const { pageSize: perPage, current: page } = pagination;
    const { field: column, order } = sorter;
    const sort = formatSortType(order);
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...activeMenu?.data, perPage, page, column, sort },
      }),
    );
  }

  const handleChangeStatus = (id, data) => {
    const params = {
      status_note: data?.status_note,
      status: data?.status,
    };
    return requestAdminModelsService.changeStatus(id, params).then(() => {
      setChangeStatus(null);
      toast.success(t('successfully.updated'));
      dispatch(fetchDeliverymanRequest(paramsData));
      dispatch(disableRefetch(activeMenu));
    });
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
        {t('deliveryman.requests')}
      </Typography.Title>
      <Divider color='var(--divider)' />
      <Table
        scroll={{ x: true }}
        rowSelection={rowSelection}
        columns={columns?.filter((item) => item?.is_show)}
        dataSource={deliverymanRequestData}
        pagination={{
          pageSize: meta?.perPage,
          total: meta?.total,
          current: data?.page,
          page: data?.page || 1,
          defaultCurrent: data?.page,
        }}
        onChange={onChangePagination}
        rowKey={(record) => record?.id}
        loading={loading}
      />
      {details ? (
        <DeliverymanRequestModal
          data={details}
          handleClose={() => setDetails(null)}
        />
      ) : null}
      {changeStatus ? (
        <StatusChangeModal
          data={changeStatus}
          handleChange={handleChangeStatus}
          handleClose={() => setChangeStatus(null)}
        />
      ) : null}
    </Card>
  );
}
