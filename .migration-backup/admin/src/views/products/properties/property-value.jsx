import React, { useState, useEffect } from 'react';
import { Button, Space, Table, Image, Typography, Divider, Col } from 'antd';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import { toast } from 'react-toastify';
import { useTranslation } from 'react-i18next';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { fetchPropertyValues } from 'redux/slices/propertyValue';
import propertyService from 'services/property';
import PropertyValueModal from './property-value-modal';
import PropertyDeleteModal from './property-delete-modal';
import { IMG_URL } from 'configs/app-global';
import { disableRefetch, setMenuData } from 'redux/slices/menu';
import FilterColumns from 'components/filter-column';
import { InfiniteSelect } from 'components/infinite-select';
import useDidUpdate from 'helpers/useDidUpdate';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';

export default function PropertyValue() {
  const { t, i18n } = useTranslation();
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
      dataIndex: 'group',
      key: 'group',
      is_show: true,
      render: (group) => group?.translation?.title,
    },
    {
      title: t('value'),
      dataIndex: 'value',
      key: 'value',
      is_show: true,
      render: (value, row) => (
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
      title: t('options'),
      is_show: true,
      render: (record) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
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
            onClick={() => {
              setId([record?.id]);
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
        dispatch(fetchPropertyValues({}));
      })
      .finally(() => setLoadingBtn(false));
  };
  useDidUpdate(() => {
    dispatch(fetchPropertyValues({ group_id: activeMenu?.data?.group_id }));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu.data]);

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchPropertyValues({}));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  async function fetchPropertyGroups({ search, page }) {
    const params = {
      search: search?.length === 0 ? undefined : search,
      page: page,
    };
    return propertyService.getAllGroups(params).then((res) => {
      return res.data.map((item) => ({
        label: item.translation?.title,
        value: item.id,
      }));
    });
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
          {t('property.values')}
        </Typography.Title>
        <Button
          type='primary'
          icon={<PlusOutlined />}
          onClick={() => setModal({})}
          style={{ width: '100%' }}
        >
          {t('add.property')}
        </Button>
      </Space>
      <Divider color='var(--divider)' />
      <Space
        className='w-100 justify-content-end align-items-center'
        style={{ rowGap: '6px', columnGap: '6px', marginBottom: '20px' }}
      >
        <Col style={{ minWidth: '170px' }}>
          <InfiniteSelect
            placeholder={t('select.group')}
            fetchOptions={fetchPropertyGroups}
            loading={loading}
            className='w-100'
            onChange={(e) => handleFilter({ group_id: e?.value })}
            value={activeMenu.data?.group_id}
          />
        </Col>
        <FilterColumns columns={columns} setColumns={setColumns} />
      </Space>
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
