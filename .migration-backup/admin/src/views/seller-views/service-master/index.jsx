import { useTranslation } from 'react-i18next';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import React, { useContext, useEffect, useState } from 'react';
import { Context } from 'context/context';
import { useNavigate } from 'react-router-dom';
import {
  Button,
  Card,
  Col,
  Divider,
  Space,
  Table,
  Tag,
  Typography,
} from 'antd';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import { toast } from 'react-toastify';
import formatSortType from 'helpers/formatSortType';
import useDidUpdate from 'helpers/useDidUpdate';
import CustomModal from 'components/modal';
import serviceMasterService from 'services/seller/service-master';
import { fetchSellerServiceMaster } from 'redux/slices/serviceMaster';
import numberToPrice from 'helpers/numberToPrice';
import OutlinedButton from 'components/outlined-button';
import FilterColumns from 'components/filter-column';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import SearchInput from '../../../components/search-input';
import { DebounceSelect } from '../../../components/search';
import userService from '../../../services/seller/user';
import servicesService from '../../../services/seller/services';

const genders = { 1: 'male', 2: 'female', 3: 'all' };

function ServiceMaster() {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const { activeMenu } = useSelector((state) => state.menu);
  const { setIsModalVisible } = useContext(Context);

  const { serviceMaster, meta, loading, params } = useSelector(
    (state) => state.serviceMaster,
    shallowEqual,
  );
  const navigate = useNavigate();

  const [id, setId] = useState(null);
  const [text, setText] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [statusPayload, setStatusPayload] = useState({
    id: null,
    active: false,
  });

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

  const [columns, setColumns] = useState([
    {
      title: t('id'),
      dataIndex: 'id',
      is_show: true,
      sorter: true,
      key: 'id',
    },
    {
      title: t('service.name'),
      dataIndex: 'serviceName',
      is_show: true,
      key: 'service.name',
      render: (_, row) => row.service?.translation?.title,
    },
    {
      title: t('master'),
      dataIndex: 'master',
      is_show: true,
      key: 'master',
      render: (master) => master?.full_name,
    },
    {
      title: t('commission.fee'),
      dataIndex: 'commission_fee',
      is_show: true,
      key: 'commission.fee',
      render: (commission_fee) => numberToPrice(commission_fee),
    },
    {
      title: t('discount'),
      dataIndex: 'discount',
      is_show: true,
      key: 'discount',
      render: (discount) => numberToPrice(discount, '%', 'after'),
    },
    {
      title: t('gender'),
      dataIndex: 'gender',
      is_show: true,
      key: 'gender',
      render: (genderId) => t(genders[genderId]),
    },
    {
      title: t('interval'),
      dataIndex: 'interval',
      is_show: true,
      key: 'interval',
      render: (interval) => <Tag>{interval}</Tag>,
    },
    {
      title: t('type'),
      dataIndex: 'type',
      is_show: true,
      key: 'type',
      render: (type) => <Tag>{t(type)}</Tag>,
    },
    {
      title: t('options'),
      dataIndex: 'options',
      key: 'options',
      is_show: true,
      render: (_, row) => {
        return (
          <div className={tableRowClasses.options}>
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
              onClick={(e) => {
                e.stopPropagation();
                goToEdit(row.id);
              }}
            >
              <EditOutlined />
            </button>
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
              onClick={() => {
                setId([row.id]);
                setIsModalVisible(true);
                setText('delete');
              }}
            >
              <DeleteOutlined />
            </button>
          </div>
        );
      },
    },
  ]);

  const clearData = () => {
    dispatch(
      setMenuData({
        activeMenu,
        data: null,
      }),
    );
  };

  const goToEdit = (id) => {
    const url = `seller/service-master/${id}`;
    dispatch(
      addMenu({
        id: 'seller-master-service-edit',
        url,
        name: t('edit.service.master'),
      }),
    );

    navigate('/' + url);
  };
  const goToAdd = () => {
    dispatch(
      addMenu({
        id: 'seller/service-master-add',
        url: `seller/service-master/add`,
        name: t('add.service.master'),
      }),
    );
    clearData();
    navigate(`/seller/service-master/add`);
  };

  async function getMasters(search) {
    const params = {
      search,
      perPage: 10,
      role: 'master',
    };
    return userService.shopUsers(params).then(({ data }) => {
      return data.map((item) => ({
        label: `${item.firstname} ${item.lastname}`,
        value: item.id,
        key: item.id,
      }));
    });
  }

  async function getServices(search) {
    const params = {
      search,
      perPage: 10,
    };
    return servicesService.getAll(params).then(({ data }) => {
      return data.map((item) => ({
        label: item.translation?.title || '-',
        value: item.id,
        key: item.id,
      }));
    });
  }

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

    serviceMasterService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setId(null);
        setIsModalVisible(false);
        setText('');
        dispatch(fetchSellerServiceMaster(paramsData));
      })
      .finally(() => setLoadingBtn(false));
  };

  const deleteSelected = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.the.product'));
    } else {
      setIsModalVisible(true);
      setText('all.delete');
    }
  };

  const changeActiveStatus = () => {
    setLoadingBtn(true);

    serviceMasterService
      .update(statusPayload.id, { active: Number(statusPayload.active) })
      .then(() => {
        setStatusPayload({ id: null, active: false });
        setIsModalVisible(false);
        setText('');
        dispatch(fetchSellerServiceMaster(paramsData));
      })
      .finally(() => setLoadingBtn(false));
  };

  const handleModalAction = () => {
    if (text === 'delete' || text === 'all.delete') {
      handleDelete();
    }
  };

  function onChangePagination(pagination, filter, sorter) {
    const { pageSize: perPage, current: page } = pagination;
    const { field: column, order } = sorter;
    const sort = formatSortType(order);
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...activeMenu.data, perPage, page, column, sort },
      }),
    );
  }

  const data = activeMenu?.data;

  const handleFilter = (item, name) => {
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...data, [name]: item },
      }),
    );
  };

  const paramsData = {
    ...params,
    search: data?.search,
    sort: data?.sort,
    column: data?.column,
    perPage: data?.perPage,
    page: data?.page,
    master_id: data?.master_id,
    service_id: data?.service_id,
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchSellerServiceMaster(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    dispatch(fetchSellerServiceMaster(paramsData));
  }, [data]);

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
            {t('service.master')}
          </Typography.Title>
          <Button
            type='primary'
            icon={<PlusOutlined />}
            onClick={goToAdd}
            style={{ width: '100%' }}
          >
            {t('add.service.master')}
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
              defaultValue={data?.search}
              resetSearch={!data?.search}
              placeholder={t('search')}
              handleChange={(search) => handleFilter(search, 'search')}
            />
          </Col>
          <Col style={{ minWidth: '189px' }}>
            <DebounceSelect
              placeholder={t('select.master')}
              fetchOptions={getMasters}
              onSelect={(master) => handleFilter(master.value, 'master_id')}
              onClear={() => handleFilter(undefined, 'master_id')}
              allowClear={true}
              style={{ width: '100%' }}
              value={data?.master_id}
            />
          </Col>
          <Col style={{ minWidth: '189px' }}>
            <DebounceSelect
              placeholder={t('select.service')}
              fetchOptions={getServices}
              onSelect={(service) => handleFilter(service.value, 'service_id')}
              onClear={() => handleFilter(undefined, 'service_id')}
              allowClear={true}
              style={{ width: '100%' }}
              value={data?.service_id}
            />
          </Col>
        </div>
        <Divider color='var(--divider)' />
        <Space className='justify-content-end align-items-start w-100 mb-3'>
          <Space>
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
          dataSource={serviceMaster}
          loading={loading}
          pagination={{
            pageSize: params.perPage,
            page: activeMenu.data?.page || 1,
            total: meta.total,
            defaultCurrent: activeMenu.data?.page,
            current: activeMenu.data?.page,
          }}
          rowKey={(record) => record.id}
          onChange={onChangePagination}
        />
      </Card>
      <CustomModal
        click={handleModalAction}
        text={text}
        setText={setId}
        loading={loadingBtn}
      />
    </>
  );
}

export default ServiceMaster;
