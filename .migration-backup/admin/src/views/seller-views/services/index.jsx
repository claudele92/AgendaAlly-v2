import React, { useState, useEffect, useContext } from 'react';
import {
  Card,
  Space,
  Table,
  Tag,
  Button,
  Image,
  Typography,
  Divider,
  Col,
} from 'antd';
import { useTranslation } from 'react-i18next';
import servicesService from 'services/seller/services';
import { shallowEqual, useSelector, useDispatch, batch } from 'react-redux';
import useDidUpdate from 'helpers/useDidUpdate';
import { useNavigate } from 'react-router-dom';
import { fetchSellerServices } from 'redux/slices/services';
import { addMenu, disableRefetch } from 'redux/slices/menu';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import Filter from './filter';
import StatusChangeModal from './status-change-modal';
import { Context } from 'context/context';
import CustomModal from 'components/modal';
import { toast } from 'react-toastify';
import { statuses } from './statuses';
import { IMG_URL } from 'configs/app-global';
import ServicesModal from './services-modal';
import OutlinedButton from '../../../components/outlined-button';
import FilterColumns from '../../../components/filter-column';
import tableRowClasses from '../../../assets/scss/components/table-row.module.scss';

const colors = ['blue', 'red', 'gold', 'volcano', 'cyan', 'lime'];

const Services = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const dispatch = useDispatch();

  const { services, loading, meta, params } = useSelector(
    (state) => state.servicesSlice,
    shallowEqual,
  );
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);

  const { setIsModalVisible } = useContext(Context);

  const debounceTimeout = 400;
  const [id, setId] = useState(null);
  const [text, setText] = useState(null);
  const [itemData, setItemData] = useState(null);
  const [filters, setFilters] = useState({
    search: '',
    category: {},
  });
  const [debouncedFilters, setDebouncedFilters] = useState(filters);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [isServiceModalOpen, setIsServiceModalOpen] = useState(false);

  const paramsData = {
    ...params,
    search: filters?.search,
    category_id: filters?.category?.value,
  };

  if (!filters?.search?.length) delete paramsData?.search;

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

  const [columns, setColumns] = useState([
    { title: t('id'), dataIndex: 'id', is_show: true, key: 'id' },
    {
      title: t('image'),
      dataIndex: 'img',
      is_show: false,
      render: (img) => {
        return (
          <Image
            width={100}
            src={img ? IMG_URL + img : 'https://via.placeholder.com/150'}
            placeholder
            style={{ borderRadius: 4 }}
          />
        );
      },
    },
    {
      title: t('title'),
      dataIndex: 'slug',
      is_show: true,
      key: 'translation',
      render: (_, row) => row?.translation?.title,
    },
    {
      title: t('translations'),
      dataIndex: 'translations',
      is_show: false,
      key: 'translations',
      render: (_, row) => (
        <Space>
          {row.locales?.map((item, index) => (
            <Tag className='text-uppercase' color={[colors[index]]}>
              {item}
            </Tag>
          ))}
        </Space>
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
          onClick={() => setItemData(row)}
        >
          <span className={tableRowClasses[status || 'pending']}>
            {t(status)}
          </span>
          <EditOutlined />
        </button>
      ),
    },
    {
      title: t('interval'),
      dataIndex: 'interval',
      key: 'interval',
      is_show: true,
    },
    {
      title: t('type'),
      dataIndex: 'type',
      key: 'type',
      is_show: true,
      render: (type) => t(type),
    },
    {
      title: t('category'),
      dataIndex: 'category',
      key: 'category',
      is_show: true,
      render: (_, row) => row?.category?.translation?.title,
    },
    {
      title: t('options'),
      is_show: true,
      key: 'options',
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            onClick={(e) => {
              e.stopPropagation();
              goToEdit(row?.id);
            }}
          >
            <EditOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            onClick={() => {
              setId([row?.id]);
              setIsModalVisible(true);
              setText(true);
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ]);

  useEffect(() => {
    if (activeMenu.refetch) {
      batch(() => {
        dispatch(fetchSellerServices(paramsData));
        dispatch(disableRefetch(activeMenu));
      });
    }
    // eslint-disable-next-line
  }, [activeMenu.refetch]);

  useEffect(() => {
    const debounceTimer = setTimeout(() => {
      setDebouncedFilters(filters);
    }, debounceTimeout);

    return () => {
      clearTimeout(debounceTimer);
    };
  }, [filters, debounceTimeout]);

  useDidUpdate(() => {
    dispatch(fetchSellerServices(paramsData));
  }, [debouncedFilters]);

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;

    const params = {
      ...paramsData,
      perPage: pageSize,
      page: current,
    };

    dispatch(fetchSellerServices(params));
  };

  const deleteSelected = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.the.product'));
    } else {
      setIsModalVisible(true);
      setText(false);
    }
  };

  const handleDelete = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };

    servicesService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setId(null);
        setIsModalVisible(false);
        setText(false);
        dispatch(fetchSellerServices(paramsData));
      })
      .finally(() => setLoadingBtn(false));
  };

  const handleChangeStatus = (values) => {
    const body = {
      category_id: values?.category?.id,
      status: values?.status,
      status_note: values?.status_note,
    };

    return servicesService
      .statusChange(values?.id, body)
      .then(() => dispatch(fetchSellerServices(paramsData)));
  };

  const goToAdd = (data) => {
    const url = 'seller/services/add';
    dispatch(
      addMenu({
        id: 'create.service',
        url,
        name: t('add.service'),
        data,
      }),
    );
    navigate(`/${url}`, {
      state: { params: paramsData, selectedService: data },
    });
  };

  const goToEdit = (id) => {
    const url = `seller/services/${id}`;
    dispatch(
      addMenu({
        id: 'edit.service',
        url,
        name: t('edit.service'),
      }),
    );
    navigate(`/${url}`, { state: { params: paramsData } });
  };

  return (
    <>
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
            {t(`services`)}
          </Typography.Title>
          <Space style={{ rowGap: '20px', columnGap: '20px' }}>
            <Button
              type='primary'
              icon={<PlusOutlined />}
              onClick={() => setIsServiceModalOpen(true)}
              style={{ width: '100%' }}
            >
              {t('add.service')}
            </Button>
          </Space>
        </Space>
        <Divider color='var(--divider)' />
        <Filter filters={filters} setFilters={setFilters} />
        <Divider color='var(--divider)' />
        <Space className='w-100 justify-content-end mb-3'>
          <Space className='gap-10'>
            <OutlinedButton onClick={deleteSelected} color='red'>
              {t('delete.selection')}
            </OutlinedButton>
            <FilterColumns columns={columns} setColumns={setColumns} />
          </Space>
        </Space>

        <Table
          scroll={{ x: true }}
          rowSelection={rowSelection}
          columns={columns?.filter((item) => item.is_show)}
          rowKey={(record) => record?.id}
          dataSource={services}
          loading={loading}
          pagination={{
            pageSize: meta?.per_page || 10,
            page: meta.current_page || 1,
            total: meta.total || 0,
            current: meta?.current_page || 1,
          }}
          onChange={onChangePagination}
        />
      </Card>
      {itemData && (
        <StatusChangeModal
          statuses={statuses}
          data={itemData}
          visible={itemData}
          handleSubmit={handleChangeStatus}
          handleCancel={() => setItemData(null)}
        />
      )}
      {isServiceModalOpen && (
        <ServicesModal
          visible={isServiceModalOpen}
          handleClose={() => setIsServiceModalOpen(false)}
          handleSubmit={(data) => goToAdd(data)}
        />
      )}
      <CustomModal
        click={handleDelete}
        text={text ? t('delete') : t('all.delete')}
        setText={setId}
        loading={loadingBtn}
      />
    </>
  );
};

export default Services;
