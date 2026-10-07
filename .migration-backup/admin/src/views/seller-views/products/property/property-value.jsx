import React, { useState, useEffect } from 'react';
import { Button, Space, Table, Image, Typography, Divider } from 'antd';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import { toast } from 'react-toastify';
import { useTranslation } from 'react-i18next';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { fetchSellerPropertyValue } from 'redux/slices/propertyValue';
import propertyService from 'services/seller/property';
import PropertyValueModal from './property-value-modal';
import PropertyDeleteModal from './property-delete-modal';
import { IMG_URL } from 'configs/app-global';
import { disableRefetch } from 'redux/slices/menu';
import FilterColumns from 'components/filter-column';
import Card from 'components/card';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';

export default function SellerPropertyValue() {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { propertyValues, loading } = useSelector(
    (state) => state.propertyValue,
    shallowEqual,
  );

  const [id, setId] = useState(null);
  const [modal, setModal] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      is_show: true,
    },
    {
      title: t('title'),
      dataIndex: 'extra_group_id',
      key: 'extra_group_id',
      is_show: true,
      render: (_, row) => row.group?.translation?.title,
    },
    {
      title: t('value'),
      dataIndex: 'value',
      key: 'value',
      is_show: true,
      render: (_, row) => (
        <Space className='extras'>
          {row.group.type === 'color' ? (
            <div
              className='extra-color-wrapper-contain'
              style={{ backgroundColor: row.value }}
            />
          ) : null}
          {row.group.type === 'image' ? (
            <Image
              width={100}
              src={IMG_URL + row.value}
              className='borderRadius'
            />
          ) : null}
          {row.group.type === 'image' ? null : <span>{row.value}</span>}
        </Space>
      ),
    },
    {
      title: t('actions'),
      is_show: true,
      render: (record) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            disabled={!record?.group?.shop_id}
            onClick={(e) => {
              e.stopPropagation();
              setModal(record);
            }}
          >
            <EditOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            disabled={!record?.group?.shop_id}
            onClick={() => {
              setId([record.id]);
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ];
  const [columns, setColumns] = useState(initialColumns);

  const handleCancel = () => setModal(null);

  const deleteProperty = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };
    propertyService
      .deleteValue(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setId(null);
        dispatch(fetchSellerPropertyValue({}));
      })
      .finally(() => setLoadingBtn(false));
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchSellerPropertyValue({}));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

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
          {t('property.values')}
        </Typography.Title>
        <Space>
          <Button
            type='primary'
            icon={<PlusOutlined />}
            onClick={() => setModal({})}
            style={{ width: '100%' }}
          >
            {t('add.property.value')}
          </Button>
          <FilterColumns columns={columns} setColumns={setColumns} />
        </Space>
      </Space>
      <Divider color='var(--divider)' />
      <Table
        scroll={{ x: true }}
        loading={loading}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={propertyValues}
        rowKey={(record) => record.id}
        pagination={false}
      />
      {modal && (
        <PropertyValueModal modal={modal} handleCancel={handleCancel} />
      )}
      {id && (
        <PropertyDeleteModal
          id={id}
          click={deleteProperty}
          text={t('delete.property')}
          loading={loadingBtn}
          handleClose={() => setId(null)}
        />
      )}
    </Card>
  );
}
