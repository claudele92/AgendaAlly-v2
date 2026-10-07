import React, { useEffect, useState, useContext } from 'react';
import {
  Button,
  Space,
  Table,
  Card,
  Tabs,
  Tag,
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
import { fetchOrders as fetchSellerOrders } from 'redux/slices/sellerOrders';
import formatSortType from 'helpers/formatSortType';
import SearchInput from 'components/search-input';
import { clearOrder } from 'redux/slices/order';
import numberToPrice from 'helpers/numberToPrice';
import { DebounceSelect } from 'components/search';
import userService from 'services/seller/user';
import FilterColumns from 'components/filter-column';
import { fetchRestOrderStatus } from 'redux/slices/orderStatus';
import { Context } from 'context/context';
import { toast } from 'react-toastify';
import CustomModal from 'components/modal';
import orderService from 'services/seller/order';
import { clearItems, fetchOrders } from 'redux/slices/sellerOrders';
import { batch } from 'react-redux';
import OrderDeliveryman from './orderDeliveryman';
import OrderStatusModal from './orderStatusModal';
import OrderTypeSwitcher from './order-type-switcher';
import { useQueryParams } from 'helpers/useQueryParams';
import moment from 'moment';
import autoRefreshOrder from 'helpers/autoRefreshOrder';
import { getHourFormat } from '../../../helpers/getHourFormat';
import OutlinedButton from '../../../components/outlined-button';
import tableRowClasses from '../../../assets/scss/components/table-row.module.scss';
const { RangePicker } = DatePicker;

const { TabPane } = Tabs;

export default function SellerOrder() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { t } = useTranslation();
  const urlParams = useParams();
  const [id, setId] = useState(null);
  const [text, setText] = useState(null);
  const { setIsModalVisible } = useContext(Context);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const { defaultCurrency } = useSelector(
    (state) => state.currency,
    shallowEqual,
  );
  const { statusList } = useSelector(
    (state) => state.orderStatus,
    shallowEqual,
  );
  const orderType = urlParams?.type;
  const statuses = [
    { name: 'all', id: 0, active: true, sort: 0 },
    ...statusList,
  ];
  const [orderDeliveryDetails, setOrderDeliveryDetails] = useState(null);
  const [orderId, setOrderId] = useState(null);

  const goToShow = (row) => {
    dispatch(
      addMenu({
        url: `seller/order/details/${row.id}`,
        id: 'order_details',
        name: t('order.details'),
      }),
    );
    navigate(`/seller/order/details/${row.id}`);
  };
  const hourFormat = getHourFormat();

  const [columns, setColumns] = useState([
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      sorter: true,
      is_show: true,
    },
    {
      title: t('client'),
      dataIndex: 'user',
      key: 'user',
      is_show: true,
      render: (user) => {
        if (!user) {
          return <Tag color='red'>{t('deleted.user')}</Tag>;
        }
        return (
          <div>
            {user?.firstname || ''} {user?.lastname || ''}
          </div>
        );
      },
    },
    {
      title: t('order.status'),
      is_show: true,
      dataIndex: 'status',
      key: 'status',
      render: (status, row) => {
        const isCanEdit =
          status !== 'delivered' && status !== 'canceled' && row.type !== 1;
        return (
          <button
            type='button'
            onClick={(e) => {
              e.stopPropagation();
              if (isCanEdit) {
                setOrderId(row?.id);
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
      render: (deliveryman) => (
        <div>
          {deliveryman?.firstname || '-'} {deliveryman?.lastname || '-'}
        </div>
      ),
    },
    {
      title: t('number.of.products'),
      dataIndex: 'order_details_count',
      key: 'order_details_count',
      is_show: true,
      render: (order_details_count) => {
        return (
          <div className='text-lowercase'>
            {order_details_count || 0} {t('products')}
          </div>
        );
      },
    },
    {
      title: t('amount'),
      dataIndex: 'total_price',
      key: 'total_price',
      is_show: true,
      render: (total_price) =>
        numberToPrice(total_price, defaultCurrency?.symbol),
    },
    {
      title: t('payment.types'),
      is_show: true,
      dataIndex: 'transactions',
      key: 'transactions',
      render: (transactions) => (
        <div className={tableRowClasses.paymentStatuses}>
          {transactions?.map((transaction) => (
            <span style={{ color: 'var(--green)' }}>
              {t(transaction?.payment_system?.tag) || '-'}
            </span>
          ))}
        </div>
      ),
    },
    {
      title: t('created.at'),
      is_show: true,
      dataIndex: 'created_at',
      key: 'created_at',
      render: (created_at) =>
        moment(created_at).format(`YYYY-MM-DD ${hourFormat}`),
    },
    {
      title: t('delivery.date'),
      is_show: true,
      dataIndex: 'delivery_date',
      key: 'delivery_date',
      render: (delivery_date) =>
        moment(delivery_date).format(`YYYY-MM-DD ${hourFormat}`),
    },
    {
      title: t('options'),
      is_show: true,
      key: 'options',
      render: (_, row) => {
        return (
          <div className={tableRowClasses.options}>
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
        );
      },
    },
  ]);

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const querryParams = useQueryParams();
  const [role, setRole] = useState(querryParams.values.status || 'all');
  const immutable = activeMenu.data?.role || role;
  const { orders, meta, loading, params } = useSelector(
    (state) => state.sellerOrders,
    shallowEqual,
  );
  const [dateRange, setDateRange] = useState(null);
  console.log('dateRange', dateRange);
  const data = activeMenu?.data;
  const paramsData = {
    search: data?.search,
    sort: data?.sort,
    column: data?.column,
    perPage: data?.perPage,
    page: data?.page,
    user_id: data?.user_id,
    status: immutable === 'all' ? undefined : immutable,
    shop_id:
      activeMenu.data?.shop_id !== null ? activeMenu.data?.shop_id : null,
    delivery_type: orderType
      ? orderType
      : activeMenu.data?.delivery_type !== null
        ? activeMenu.data?.delivery_type
        : null,
    date_from: dateRange?.[0]?.format('YYYY-MM-DD') || undefined,
    date_to: dateRange?.[1]?.format('YYYY-MM-DD') || undefined,
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
    orderService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setIsModalVisible(false);
        dispatch(fetchSellerOrders(paramsData));
        setText(null);
      })
      .finally(() => setLoadingBtn(false));
  };

  useDidUpdate(() => {
    dispatch(fetchSellerOrders(paramsData));
  }, [activeMenu?.data, dateRange]);

  useEffect(() => {
    if (activeMenu?.refetch) {
      batch(() => {
        dispatch(fetchSellerOrders(paramsData));
        dispatch(fetchRestOrderStatus({}));
        dispatch(disableRefetch(activeMenu));
      });
    }

    const interval = autoRefreshOrder(() => {
      dispatch(fetchSellerOrders(paramsData));
    });
    return () => clearInterval(interval);
  }, [activeMenu?.refetch]);

  const handleFilter = (item, name) => {
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...data, [name]: item },
      }),
    );
  };

  async function getUsers(search) {
    const params = {
      search,
      perPage: 10,
    };
    return userService.getAll(params).then(({ data }) => {
      return data.map((item) => ({
        label: `${item.firstname} ${item.lastname}`,
        value: item.id,
        key: item.id,
      }));
    });
  }

  const goToAddOrder = () => {
    dispatch(clearOrder());
    dispatch(
      addMenu({
        id: 'pos.system',
        url: 'seller/pos-system',
        name: t('add.order'),
      }),
    );
    navigate('/seller/pos-system', { state: { delivery_type: orderType } });
  };

  const onChangeTab = (status) => {
    const orderStatus = status;
    dispatch(setMenuData({ activeMenu, data: { role: orderStatus, page: 1 } }));
    setRole(status);
    navigate(`?status=${orderStatus}`);
  };

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

  const allDelete = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.the.product'));
    } else {
      setIsModalVisible(true);
      setText(false);
    }
  };

  const handleClear = () => {
    batch(() => {
      dispatch(clearItems());
      dispatch(
        setMenuData({
          activeMenu,
          data: null,
        }),
      );
    });
    dispatch(fetchOrders({ status: 'all', page: data?.page, perPage: 10 }));
  };

  const handleCloseModal = () => {
    setOrderDeliveryDetails(null);
    setOrderId(null);
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
            {t('orders')}
          </Typography.Title>
          <Space style={{ rowGap: '20px', columnGap: '20px' }}>
            <OrderTypeSwitcher listType='seller/orders' />
            <Button
              type='primary'
              icon={<PlusOutlined />}
              onClick={goToAddOrder}
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
              handleChange={(search) => handleFilter(search, 'search')}
            />
          </Col>
          <Col style={{ minWidth: '189px' }}>
            <DebounceSelect
              placeholder={t('select.client')}
              fetchOptions={getUsers}
              onSelect={(user) => handleFilter(user.value, 'user_id')}
              onDeselect={() => handleFilter(null, 'user_id')}
              style={{ width: '100%' }}
              value={data?.user_id}
            />
          </Col>
          <Col style={{ minWidth: '242px' }}>
            <RangePicker
              value={dateRange}
              onChange={(values) => {
                handleFilter((prev) => ({
                  ...prev,
                  ...{
                    date_from: values?.[0]?.format('YYYY-MM-DD'),
                    date_to: values?.[1]?.format('YYYY-MM-DD'),
                  },
                }));
                setDateRange(values);
              }}
              placeholder={[t('start.date'), t('end.date')]}
              disabledDate={(current) => {
                return current && current > moment().endOf('day');
              }}
              style={{ width: '300px' }}
            />
          </Col>
          <Col>
            <Button onClick={() => handleClear()}>{t('clear')}</Button>
          </Col>
        </div>
        <Divider color='var(--divider)' />
        <Space className='justify-content-between align-items-start w-100'>
          <Tabs onChange={onChangeTab} type='card' activeKey={immutable}>
            {statuses
              .filter((ex) => ex?.active)
              .map((item) => (
                <TabPane tab={t(item?.name)} key={item?.name} />
              ))}
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
          columns={columns?.filter((item) => item.is_show)}
          dataSource={orders}
          loading={loading}
          pagination={{
            pageSize: params.perPage,
            page: activeMenu.data?.page || 1,
            total: meta?.total,
            defaultCurrent: activeMenu.data?.page,
            current: activeMenu.data?.page,
          }}
          rowKey={(record) => record.id}
          onChange={onChangePagination}
        />
      </Card>

      <CustomModal
        click={orderDelete}
        text={text ? t('delete') : t('all.delete')}
        loading={loadingBtn}
        setText={setId}
      />
      {orderId && (
        <OrderStatusModal
          orderId={orderId}
          handleCancel={handleCloseModal}
          refetchPage={() =>
            batch(() => {
              dispatch(fetchOrders(paramsData));
              dispatch(disableRefetch(activeMenu));
            })
          }
        />
      )}
      {orderDeliveryDetails && (
        <OrderDeliveryman
          orderDetails={orderDeliveryDetails}
          handleCancel={handleCloseModal}
        />
      )}
    </>
  );
}
