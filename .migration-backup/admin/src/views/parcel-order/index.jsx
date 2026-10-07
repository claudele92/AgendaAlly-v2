import React, { useContext, useEffect, useState } from 'react';
import {
  Button,
  Space,
  Table,
  Tabs,
  DatePicker,
  Typography,
  Divider,
  Col,
} from 'antd';
import { useNavigate, useParams } from 'react-router-dom';
import {
  DeleteOutlined,
  EditOutlined,
  EyeOutlined,
  PlusOutlined,
} from '@ant-design/icons';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import { useTranslation } from 'react-i18next';
import useDidUpdate from 'helpers/useDidUpdate';
import { fetchParcelOrders } from 'redux/slices/parcelOrders';
import formatSortType from 'helpers/formatSortType';
import SearchInput from 'components/search-input';
import { clearOrder } from 'redux/slices/order';
import FilterColumns from 'components/filter-column';
import { toast } from 'react-toastify';
import parcelOrderService from 'services/parcelOrder';
import { Context } from 'context/context';
import CustomModal from 'components/modal';
import moment from 'moment';
import { export_url } from 'configs/app-global';
import { BiMap } from 'react-icons/bi';
import { batch } from 'react-redux';
import { useQueryParams } from 'helpers/useQueryParams';
import ParcelStatus from './parcel-status';
import ShowLocationsMap from './show-locations-map';
import ShowParcelDetails from './show-parcel-details';
import ParcelDeliveryman from './parcel-deliveryman';
import { fetchOrderStatus } from 'redux/slices/orderStatus';
import { getHourFormat } from 'helpers/getHourFormat';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';
import { PiFileArrowUpBold } from 'react-icons/pi';
import OutlinedButton from 'components/outlined-button';

const { TabPane } = Tabs;
const { RangePicker } = DatePicker;

export default function ParserOrders() {
  const { type } = useParams();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const queryParams = useQueryParams();
  const { t, i18n } = useTranslation();
  const { statusList } = useSelector(
    (state) => state.orderStatus,
    shallowEqual,
  );
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);

  const [orderDetails, setOrderDetails] = useState(null);
  const [locationsMap, setLocationsMap] = useState(null);
  const [parcelDeliveryDetails, setOrderDeliveryDetails] = useState(null);
  const hourFormat = getHourFormat();

  const statuses = [
    { name: 'all', id: '0', active: true, sort: 0 },
    ...statusList,
  ];

  const goToEdit = (row) => {
    dispatch(clearOrder());
    dispatch(
      addMenu({
        url: `parcel-orders/${row.id}`,
        id: 'edit_parcel_order',
        name: t('edit.parcel.order'),
      }),
    );
    navigate(`/parcel-orders/${row.id}`);
  };

  const goToShow = (row) => {
    queryParams.set('parcelId', row.id);
  };

  const initialColumns = [
    {
      title: t('id'),
      is_show: true,
      dataIndex: 'id',
      key: 'id',
      sorter: true,
    },
    {
      title: t('client'),
      is_show: true,
      dataIndex: 'user',
      key: 'user',
      render: (user) => `${user?.firstname || ''} ${user?.lastname || ''}`,
    },
    {
      title: t('order.status'),
      is_show: true,
      dataIndex: 'status',
      key: 'status',
      render: (status, row) => {
        const isCanEdit =
          status !== 'delivered' && status !== 'canceled' && row.type !== 2;
        return (
          <button
            type='button'
            onClick={(e) => {
              e.stopPropagation();
              if (isCanEdit) {
                setOrderDetails(row);
              }
            }}
            className={tableRowClasses.status}
            disabled={!isCanEdit}
          >
            <span className={tableRowClasses[status || 'new']}>
              {t(status)}
            </span>
            {isCanEdit && <EditOutlined size={12} />}
          </button>
        );
      },
    },
    {
      title: t('deliveryman'),
      is_show: true,
      dataIndex: 'deliveryman',
      key: 'deliveryman',
      render: (deliveryman, row) => (
        <div>
          {row.status === 'ready' &&
          row.delivery_type === 'delivery' &&
          row.type !== 2 ? (
            <Button type='link' onClick={() => setOrderDeliveryDetails(row)}>
              <Space>
                {deliveryman
                  ? `${deliveryman.firstname} ${deliveryman.lastname}`
                  : t('add.deliveryman')}
                <EditOutlined />
              </Space>
            </Button>
          ) : (
            <div>
              {deliveryman?.firstname} {deliveryman?.lastname}
            </div>
          )}
        </div>
      ),
    },
    {
      title: t('payment.type'),
      is_show: true,
      dataIndex: 'transaction',
      key: 'transaction',
      render: (transaction) => t(transaction?.payment_system?.tag),
    },
    {
      title: t('created.at'),
      is_show: true,
      dataIndex: 'created_at',
      key: 'created_at',
      render: (date) => moment(date).format(`YYYY-MM-DD ${hourFormat}`),
    },
    {
      title: t('delivery.date'),
      is_show: true,
      dataIndex: 'delivery_date',
      key: 'delivery_date',
      render: (date) => moment(date).format('YYYY-MM-DD'),
    },
    {
      title: t('actions'),
      is_show: true,
      key: 'actions',
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.location}`}
            disabled={row?.deleted_at}
            onClick={(e) => {
              e.stopPropagation();
              setLocationsMap(row.id);
            }}
          >
            <BiMap />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.details}`}
            disabled={row?.deleted_at}
            onClick={(e) => {
              e.stopPropagation();
              goToShow(row);
            }}
          >
            <EyeOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            disabled={
              row.status === 'delivered' ||
              row.status === 'canceled' ||
              row.type === 2
            }
            onClick={(e) => {
              e.stopPropagation();
              goToEdit(row);
            }}
          >
            <EditOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            disabled={row?.deleted_at}
            onClick={(e) => {
              e.stopPropagation();
              setId([row.id]);
              setIsModalVisible(true);
              setText(true);
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ];
  const [columns, setColumns] = useState(initialColumns);

  const { setIsModalVisible } = useContext(Context);
  const [downloading, setDownloading] = useState(false);
  const [role, setRole] = useState(queryParams.values.status || 'all');
  const immutable = activeMenu.data?.role || role;
  const data = activeMenu.data;

  const [id, setId] = useState(null);
  const [text, setText] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [dateRange, setDateRange] = useState(
    moment().subtract(1, 'months'),
    moment(),
  );
  const {
    data: orders,
    loading,
    params,
    meta,
  } = useSelector((state) => state.parcelOrders, shallowEqual);

  const paramsData = {
    search: data?.search || undefined,
    sort: data?.sort,
    column: data?.column,
    perPage: data?.perPage,
    page: data?.page,
    status: immutable === 'all' ? undefined : immutable,
    shop_id:
      activeMenu.data?.shop_id !== null ? activeMenu.data?.shop_id : null,
    delivery_type: type !== 'scheduled' ? type : undefined,
    delivery_date_from:
      type === 'scheduled'
        ? moment().add(1, 'day').format('YYYY-MM-DD')
        : undefined,
    date_from: dateRange ? dateRange[0]?.format('YYYY-MM-DD') : null,
    date_to: dateRange ? dateRange[1]?.format('YYYY-MM-DD') : null,
  };

  function onChangePagination(pagination, filters, sorter) {
    const { pageSize: perPage, current: page } = pagination;
    const { field: column, order } = sorter;
    const sort = formatSortType(order);
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...data, perPage, page, column, sort },
      }),
    );
  }

  const orderDelete = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };

    parcelOrderService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setIsModalVisible(false);
        dispatch(fetchParcelOrders(paramsData));
        setText(null);
        setId(null);
      })
      .finally(() => {
        setLoadingBtn(false);
      });
  };

  const handleFilter = (item, name) => {
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...data, ...{ [name]: item } },
      }),
    );
  };

  const goToOrderCreate = () => {
    batch(() => {
      dispatch(clearOrder());
      dispatch(
        addMenu({
          id: 'parcel-orders/add',
          url: 'parcel-orders/add',
          name: 'add.parcel.order',
        }),
      );
    });
    navigate('/parcel-orders/add');
  };

  const excelExport = () => {
    setDownloading(true);
    const params =
      role !== 'all'
        ? {
            status: role,
          }
        : null;

    parcelOrderService
      .export(params)
      .then((res) => {
        window.location.href = export_url + res?.data?.file_name;
      })
      .finally(() => setDownloading(false));
  };

  const onChangeTab = (status) => {
    const orderStatus = status;
    dispatch(setMenuData({ activeMenu, data: { role: orderStatus, page: 1 } }));
    setRole(status);
    navigate(`?status=${orderStatus}`);
  };

  const handleCloseModal = () => {
    setOrderDetails(null);
    setOrderDeliveryDetails(null);
    setLocationsMap(null);
    queryParams.clear();
  };

  useEffect(() => {
    if (activeMenu?.refetch) {
      batch(() => {
        dispatch(fetchParcelOrders(paramsData));
        dispatch(disableRefetch(activeMenu));
      });
    }
  }, [activeMenu?.refetch]);

  useEffect(() => {
    dispatch(fetchOrderStatus({}));
    dispatch(disableRefetch(activeMenu));
  }, []);

  useDidUpdate(() => {
    dispatch(fetchParcelOrders(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [data, dateRange, type]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

  const allDelete = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.order'));
    } else {
      setIsModalVisible(true);
      setText(false);
    }
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
            {t(`${type || 'all'}.orders`)}
          </Typography.Title>
          <Space style={{ rowGap: '20px', columnGap: '20px' }}>
            <Button
              type='primary'
              icon={<PlusOutlined />}
              onClick={goToOrderCreate}
              style={{ width: '100%' }}
            >
              {t('create.order')}
            </Button>
          </Space>
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
              placeholder={t('search.by.order.id.customer')}
              className='w-100'
              handleChange={(search) => handleFilter(search, 'search')}
            />
          </Col>
          <Col style={{ minWidth: '200px' }}>
            <RangePicker
              value={dateRange}
              onChange={(values) => setDateRange(values)}
              disabledDate={(current) => {
                return current && current > moment().endOf('day');
              }}
              style={{ width: '100%' }}
            />
          </Col>
          <OutlinedButton onClick={excelExport} loading={downloading}>
            <PiFileArrowUpBold />
            {t('export')}
          </OutlinedButton>
        </div>
        <Divider color='var(--divider)' />
        <Space className='justify-content-between align-items-start w-100'>
          <Tabs onChange={onChangeTab} type='card' activeKey={immutable}>
            {statuses
              .filter((ex) => ex.active === true)
              .map((item) => {
                return <TabPane tab={t(item.name)} key={item.name} />;
              })}
          </Tabs>
          <Space>
            <OutlinedButton onClick={allDelete} color='red'>
              {t('delete.selection')}
            </OutlinedButton>
            <FilterColumns columns={columns} setColumns={setColumns} />
          </Space>
        </Space>
        <Table
          scroll={{ x: true }}
          rowSelection={rowSelection}
          columns={columns?.filter((items) => items.is_show)}
          dataSource={orders}
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

      {orderDetails && (
        <ParcelStatus
          orderDetails={orderDetails}
          handleCancel={handleCloseModal}
          status={statusList}
        />
      )}
      {parcelDeliveryDetails && (
        <ParcelDeliveryman
          orderDetails={parcelDeliveryDetails}
          handleCancel={handleCloseModal}
        />
      )}
      {locationsMap && (
        <ShowLocationsMap id={locationsMap} handleCancel={handleCloseModal} />
      )}
      {!!queryParams?.values?.parcelId && (
        <ShowParcelDetails
          id={queryParams?.values?.parcelId}
          handleCancel={handleCloseModal}
        />
      )}
      <CustomModal
        click={orderDelete}
        text={text ? t('delete') : t('all.delete')}
        loading={loadingBtn}
        setText={setId}
      />
    </>
  );
}
