import React, { useContext, useEffect, useState } from 'react';
import { Button, Col, Divider, Space, Switch, Table, Typography } from 'antd';
import { Context } from 'context/context';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { disableRefetch, setMenuData } from 'redux/slices/menu';
import { toast } from 'react-toastify';
import { useTranslation } from 'react-i18next';
import { fetchCity } from 'redux/slices/deliveryzone/city';
import SearchInput from 'components/search-input';
import useDidUpdate from 'helpers/useDidUpdate';
import cityService from 'services/deliveryzone/city';
import { useSearchParams } from 'react-router-dom';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';
import CityForm from './city-form';

const City = () => {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  let [searchParams] = useSearchParams();
  const { setIsModalVisible } = useContext(Context);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const data = activeMenu?.data;
  const country_id = searchParams.get('country_id');

  const { list, meta, loading } = useSelector(
    (state) => state.deliveryCity,
    shallowEqual,
  );
  const [selectedId, setSeletedId] = useState(false);
  const [visible, setVisible] = useState(false);
  const [id, setId] = useState(null);

  const handleActive = (id) => {
    setSeletedId(id);
    cityService
      .status(id)
      .then(() => {
        setIsModalVisible(false);
        dispatch(fetchCity({ country_id }));
        toast.success(t('successfully.updated'));
      })
      .finally(() => {
        setSeletedId(null);
      });
  };

  const handleDelete = (id) => {
    cityService.delete(id).then(() => {
      dispatch(fetchCity());
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
      render: (active, row) => (
        <Switch
          key={row.id}
          onChange={() => handleActive(row.id)}
          checked={active}
          loading={Boolean(selectedId === row.id)}
        />
      ),
    },
    {
      title: t('actions'),
      dataIndex: 'actions',
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
      dispatch(fetchCity({ country_id }));
      dispatch(disableRefetch(activeMenu));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    const paramsData = {
      search: data?.search,
      country_id,
    };
    dispatch(fetchCity(paramsData));
  }, [activeMenu?.data]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;
    dispatch(fetchCity({ perPage: pageSize, page: current, country_id }));
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
          {t('cities')}
        </Typography.Title>
        <Button
          type='primary'
          icon={<PlusOutlined />}
          onClick={() => setVisible(true)}
          style={{ width: '100%' }}
        >
          {t('add.city')}
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
          pageSize: meta.per_page,
          page: meta.current_page,
          total: meta.total,
        }}
        rowKey={(record) => record.id}
        loading={loading}
        onChange={onChangePagination}
      />
      <CityForm
        visible={visible}
        setVisible={setVisible}
        id={id}
        setId={setId}
      />
    </Card>
  );
};

export default City;
