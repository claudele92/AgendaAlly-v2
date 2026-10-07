import React, { useContext, useEffect, useState } from 'react';
import {
  Button,
  Card,
  Col,
  Divider,
  Space,
  Switch,
  Table,
  Typography,
} from 'antd';
import { Context } from 'context/context';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import { toast } from 'react-toastify';
import { useTranslation } from 'react-i18next';
import { fetchRegion } from 'redux/slices/deliveryzone/region';
import SearchInput from 'components/search-input';
import useDidUpdate from 'helpers/useDidUpdate';
import regionService from 'services/deliveryzone/region';
import { useNavigate } from 'react-router-dom';
import RegionForm from './region-form';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import tableRowClasses from '../../../assets/scss/components/table-row.module.scss';

const Region = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { setIsModalVisible } = useContext(Context);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const data = activeMenu?.data;
  const { list, meta, loading } = useSelector(
    (state) => state.deliveryRegion,
    shallowEqual,
  );
  const [selectedId, setSeletedId] = useState(false);
  const [visible, setVisible] = useState(false);
  const [deleting, setDeleting] = useState(false);
  const [id, setId] = useState(null);
  const showRegion = (row) => {
    dispatch(
      addMenu({
        url: `deliveryzone/country?region_id=${row.id}`,
        id: 'country',
        name: t('country'),
      }),
    );
    navigate(`/deliveryzone/country?region_id=${row.id}`);
  };
  const handleActive = (id) => {
    setSeletedId(id);
    regionService
      .status(id)
      .then(() => {
        setIsModalVisible(false);
        dispatch(fetchRegion());
        toast.success(t('successfully.updated'));
      })
      .finally(() => {
        setSeletedId(null);
      });
  };
  const handleDelete = (id) => {
    setDeleting(id);
    regionService
      .delete(id)
      .then(() => {
        dispatch(fetchRegion());
        toast.success(t('successfully.deleted'));
      })
      .finally(() => {
        setDeleting(false);
      });
  };
  const handleEdit = (id) => {
    setVisible(true);
    setId(id);
  };
  const columns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
    },
    {
      title: t('name'),
      dataIndex: 'translation',
      key: 'translation',
      render: (translation, row) => (
        <div
          style={{
            cursor: 'pointer',
            textDecoration: 'underline',
          }}
          onClick={() => showRegion(row)}
        >
          {translation?.title || '-'}
        </div>
      ),
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
      title: t('options'),
      dataIndex: 'options',
      is_show: true,
      render: (_, row) => {
        return (
          <div className={tableRowClasses.options}>
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
              onClick={(e) => {
                e.stopPropagation();
                handleEdit(row.id);
              }}
            >
              <EditOutlined />
            </button>
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
              onClick={() => handleDelete(row.id)}
              disabled={Boolean(deleting === row.id)}
            >
              <DeleteOutlined />
            </button>
          </div>
        );
      },
    },
  ];

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchRegion());
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    const paramsData = {
      search: data?.search,
    };
    dispatch(fetchRegion(paramsData));
  }, [activeMenu?.data]);

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;
    dispatch(fetchRegion({ perPage: pageSize, page: current }));
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
          {t('regions')}
        </Typography.Title>
        <Button
          type='primary'
          icon={<PlusOutlined />}
          onClick={() => setVisible(true)}
          style={{ width: '100%' }}
        >
          {t('add.region')}
        </Button>
      </Space>
      <Divider color='var(--divider)' />
      <div
        style={{
          display: 'flex',
          flexWrap: 'wrap',
          rowGap: '6px',
          columnGap: '6px',
        }}
      >
        <Col style={{ minWidth: '253px' }}>
          <SearchInput
            placeholder={t('search')}
            handleChange={(search) => handleFilter(search, 'search')}
            defaultValue={data?.search}
            resetSearch={!data?.search}
          />
        </Col>
      </div>
      <Divider color='var(--divider)' />
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

      <RegionForm
        visible={visible}
        setVisible={setVisible}
        id={id}
        setId={setId}
      />
    </Card>
  );
};

export default Region;
