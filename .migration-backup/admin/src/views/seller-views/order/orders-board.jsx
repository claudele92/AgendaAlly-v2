import React, { useEffect, useState, useContext } from 'react';
import {
  Button,
  Space,
  Card,
  DatePicker,
  Typography,
  Divider,
  Col,
} from 'antd';
import { useNavigate, useParams } from 'react-router-dom';
import {
  ClearOutlined,
  PlusCircleOutlined,
  PlusOutlined,
} from '@ant-design/icons';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import { useTranslation } from 'react-i18next';
import useDidUpdate from 'helpers/useDidUpdate';
import { fetchOrders as fetchSellerOrders } from 'redux/slices/sellerOrders';
import SearchInput from 'components/search-input';
import { DebounceSelect } from 'components/search';
import userService from 'services/seller/user';
import { fetchRestOrderStatus } from 'redux/slices/orderStatus';
import { Context } from 'context/context';
import { toast } from 'react-toastify';
import orderService from 'services/seller/order';
import Incorporate from './dnd/Incorporate';
import {
  clearItems,
  fetchAcceptedOrders,
  fetchCanceledOrders,
  fetchDeliveredOrders,
  fetchNewOrders,
  fetchOnAWayOrders,
  fetchReadyOrders,
  fetchCookingOrders,
  fetchPauseOrders,
} from 'redux/slices/sellerOrders';
import { batch } from 'react-redux';
import OrderDeliveryman from './orderDeliveryman';
import OrderTypeSwitcher from './order-type-switcher';
import { clearOrder } from 'redux/slices/order';
import ShowLocationsMap from './show-locations.map';
import DownloadModal from './downloadModal';
import CustomModal from 'components/modal';
import moment from 'moment';
import autoRefreshOrder from 'helpers/autoRefreshOrder';
const { RangePicker } = DatePicker;

export default function SellerOrdersBoard() {
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { t } = useTranslation();
  const [id, setId] = useState(null);
  const { setIsModalVisible } = useContext(Context);
  const [text, setText] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [locationsMap, setLocationsMap] = useState(null);
  const [dowloadModal, setDowloadModal] = useState(null);
  const [orderDeliveryDetails, setOrderDeliveryDetails] = useState(null);
  const [type, setType] = useState(null);
  const [dateRange, setDateRange] = useState(null);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const urlParams = useParams();
  const orderType = urlParams?.type;

  const data = activeMenu?.data;

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
        dispatch(clearItems());
        toast.success(t('successfully.deleted'));
        setIsModalVisible(false);
        fetchOrderAllItem({ status: type });
        setText(null);
      })
      .finally(() => {
        setId(null);
        setLoadingBtn(false);
      });
  };

  useDidUpdate(() => {
    dispatch(clearItems());
    fetchOrderAllItem();
  }, [data]);

  useEffect(() => {
    if (activeMenu?.refetch) {
      const params = {
        status: data?.status,
        perPage: 10,
        delivery_type: orderType,
      };
      dispatch(fetchSellerOrders(params));
      dispatch(fetchRestOrderStatus({}));
      dispatch(disableRefetch(activeMenu));
    }

    const interval = autoRefreshOrder(() => {
      dispatch(clearItems());
      fetchOrderAllItem();
    });

    return () => clearInterval(interval);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu?.refetch]);

  const handleFilter = (item, name) => {
    batch(() => {
      dispatch(clearItems());
      dispatch(
        setMenuData({
          activeMenu,
          data: { ...data, [name]: item },
        }),
      );
    });
  };

  async function getUsers(search) {
    const params = {
      search,
      perPage: 10,
    };
    return userService.getAll(params).then(({ data }) => {
      return data.map((item) => ({
        label: `${item.firstname} ${item.lastname || ''}`,
        value: item.id,
      }));
    });
  }

  const fetchOrdersCase = (params) => {
    const paramsWithType = {
      ...params,
      delivery_type: orderType,
      delivery_date_from:
        type === 'scheduled'
          ? moment().add(1, 'day').format('YYYY-MM-DD')
          : undefined,

      search: data?.search ? data.search : undefined,
      user_id: data?.client_id,
      status: params?.status,
      shop_id:
        activeMenu.data?.shop_id !== null ? activeMenu.data?.shop_id : null,
      date_from: dateRange?.[0]?.format('YYYY-MM-DD') || undefined,
      date_to: dateRange?.[1]?.format('YYYY-MM-DD') || undefined,
    };
    switch (params.status) {
      case 'new':
        dispatch(fetchNewOrders(paramsWithType));
        break;
      case 'accepted':
        dispatch(fetchAcceptedOrders(paramsWithType));
        break;
      case 'ready':
        dispatch(fetchReadyOrders(paramsWithType));
        break;
      case 'on_a_way':
        dispatch(fetchOnAWayOrders(paramsWithType));
        break;
      case 'delivered':
        dispatch(fetchDeliveredOrders(paramsWithType));
        break;
      case 'canceled':
        dispatch(fetchCanceledOrders(paramsWithType));
        break;
      case 'cooking':
        dispatch(fetchCookingOrders(paramsWithType));
        break;
      case 'pause':
        dispatch(fetchPauseOrders(paramsWithType));
        break;
      default:
        console.log(`Sorry, we are out of`);
    }
  };

  const fetchOrderAllItem = () => {
    fetchOrdersCase({ status: 'new' });
    fetchOrdersCase({ status: 'accepted' });
    fetchOrdersCase({ status: 'ready' });
    fetchOrdersCase({ status: 'on_a_way' });
    fetchOrdersCase({ status: 'delivered' });
    fetchOrdersCase({ status: 'canceled' });
    fetchOrdersCase({ status: 'cooking' });
    fetchOrdersCase({ status: 'pause' });
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
    fetchOrderAllItem();
  };

  const handleCloseModal = () => {
    setOrderDeliveryDetails(null);
    setLocationsMap(null);
    setDowloadModal(null);
  };
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
          {t(`${type || 'all'}.orders`)}
        </Typography.Title>
        <Space style={{ rowGap: '20px', columnGap: '20px' }}>
          <OrderTypeSwitcher listType='seller/orders-board' />
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
            placeholder={t('search')}
            handleChange={(search) => handleFilter(search, 'search')}
            defaultValue={activeMenu.data?.search}
            resetSearch={!data?.search}
          />
        </Col>
        <Col style={{ minWidth: '160px' }}>
          <DebounceSelect
            placeholder={t('select.client')}
            fetchOptions={getUsers}
            onSelect={(user) => handleFilter(user.value, 'client_id')}
            onDeselect={() => handleFilter(null, 'client_id')}
            style={{ minWidth: 200 }}
            value={data?.client_id}
          />
        </Col>
        {type !== 'scheduled' && (
          <Col style={{ minWidth: '242px' }}>
            <RangePicker
              defaultValue={dateRange}
              onChange={(values) => {
                handleFilter(JSON.stringify(values), 'data_time');
                setDateRange(values);
              }}
              disabledDate={(current) => {
                return current && current > moment().endOf('day');
              }}
              allowClear={true}
              style={{ width: '300px' }}
              placeholder={[t('start.date'), t('end.date')]}
              onClear={() => {
                dispatch(clearItems());
                setDateRange(null);
              }}
            />
          </Col>
        )}
        <Col>
          <Button icon={<ClearOutlined />} onClick={handleClear}>
            {t('clear')}
          </Button>
        </Col>
      </div>
      <Divider color='var(--divider)' />
      <Incorporate
        goToShow={goToShow}
        fetchOrderAllItem={fetchOrderAllItem}
        fetchOrders={fetchOrdersCase}
        setLocationsMap={setLocationsMap}
        setId={setId}
        setIsModalVisible={setIsModalVisible}
        setText={setText}
        setDowloadModal={setDowloadModal}
        type={type}
        setType={setType}
        orderType={orderType}
      />
      <CustomModal
        click={orderDelete}
        text={text ? t('delete') : t('all.delete')}
        loading={loadingBtn}
        setText={setId}
        setActive={setId}
      />
      {orderDeliveryDetails && (
        <OrderDeliveryman
          orderDetails={orderDeliveryDetails}
          handleCancel={handleCloseModal}
        />
      )}
      {locationsMap && (
        <ShowLocationsMap id={locationsMap} handleCancel={handleCloseModal} />
      )}
      {dowloadModal && (
        <DownloadModal id={dowloadModal} handleCancel={handleCloseModal} />
      )}
    </Card>
  );
}
