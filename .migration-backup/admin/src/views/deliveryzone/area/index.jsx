import React, { useContext, useEffect, useState } from 'react';
import { Button, Col, Divider, Space, Switch, Table, Typography } from 'antd';
import { Context } from 'context/context';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { disableRefetch, setMenuData } from 'redux/slices/menu';
import { toast } from 'react-toastify';
import { useTranslation } from 'react-i18next';
import { fetchArea } from 'redux/slices/deliveryzone/area';
import SearchInput from 'components/search-input';
import useDidUpdate from 'helpers/useDidUpdate';
import areaService from 'services/deliveryzone/area';
import { useSearchParams } from 'react-router-dom';
import AreaForm from './area-form';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';

const Area = () => {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const [searchParams] = useSearchParams();
  const { setIsModalVisible } = useContext(Context);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const data = activeMenu?.data;
  const city_id = searchParams.get('city_id');

  const { list, meta, loading } = useSelector(
    (state) => state.deliveryArea,
    shallowEqual,
  );
  const [selectedId, setSeletedId] = useState(false);
  const [visible, setVisible] = useState(false);
  const [id, setId] = useState(null);

  const handleActive = (id) => {
    setSeletedId(id);
    areaService
      .status(id)
      .then(() => {
        setIsModalVisible(false);
        dispatch(fetchArea({ city_id }));
        toast.success(t('successfully.updated'));
      })
      .finally(() => {
        setSeletedId(null);
      });
  };

  const handleDelete = (id) => {
    areaService.delete(id).then(() => {
      dispatch(fetchArea({}));
      toast.success(t('successfully.deleted'));
    });
  };

  const handleEdit = (id) => {
    setVisible(true);
    setId(id);
  };

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
    },
    {
      title: t('name'),
      dataIndex: 'translation',
      key: 'translation',
      render: (translation) => translation?.title,
    },
    {
      title: t('active'),
      dataIndex: 'active',
      key: 'active',
      render: (active, row) => {
        return (
          <Switch
            key={row.id}
            onChange={() => handleActive(row.id)}
            checked={active}
            loading={Boolean(selectedId === row.id)}
          />
        );
      },
    },
    {
      title: t('actions'),
      dataIndex: 'actions',
      is_show: true,
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            onClick={(e) => {
              e.stopPropagation();
              handleEdit(row?.id);
            }}
          >
            <EditOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            onClick={() => {
              handleDelete(row?.id);
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ];
  const [columns, setColumns] = useState(initialColumns);

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchArea({ city_id }));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    const paramsData = {
      search: data?.search || undefined,
      city_id,
    };
    dispatch(fetchArea(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu?.data]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;
    dispatch(fetchArea({ perPage: pageSize, page: current, city_id }));
  };
  const handleFilter = (item, name) => {
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...data, [name]: item },
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
          {t('areas')}
        </Typography.Title>
        <Button
          type='primary'
          icon={<PlusOutlined />}
          onClick={() => setVisible(true)}
          style={{ width: '100%' }}
        >
          {t('add.area')}
        </Button>
      </Space>
      <Divider color='var(--divider)' />
      <Space
        className='w-100 justify-content-end align-items-center'
        style={{ rowGap: '6px', columnGap: '6px', marginBottom: '20px' }}
      >
        <Col style={{ minWidth: '228px' }}>
          <SearchInput
            placeholder={t('search')}
            defaultValue={data?.search}
            resetSearch={!data?.search}
            className='w-100'
            handleChange={(search) => handleFilter(search, 'search')}
          />
        </Col>
      </Space>
      <Table
        columns={columns}
        dataSource={list}
        pagination={{
          pageSize: meta?.per_page,
          page: meta?.current_page,
          current: meta?.current_page,
          total: meta.total,
        }}
        rowKey={(record) => record.id}
        loading={loading}
        onChange={onChangePagination}
      />
      <AreaForm
        visible={visible}
        setVisible={setVisible}
        id={id}
        setId={setId}
      />
    </Card>
  );
};

export default Area;
