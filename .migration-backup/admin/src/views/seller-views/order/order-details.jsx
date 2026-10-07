import React, { useEffect, useState, useRef, useCallback } from 'react';
import {
  Card,
  Table,
  Image,
  Tag,
  Button,
  Space,
  Row,
  Avatar,
  Col,
  Typography,
  Skeleton,
  Steps,
  Spin,
  Badge,
  Divider,
  Descriptions,
  Modal,
} from 'antd';
import {
  CalendarOutlined,
  EditOutlined,
  SwapOutlined,
} from '@ant-design/icons';
import { Link, useParams } from 'react-router-dom';
import orderService from 'services/seller/order';
import getImage from 'helpers/getImage';
import { shallowEqual, useDispatch, useSelector, batch } from 'react-redux';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import OrderStatusModal from './orderStatusModal';
import OrderDeliveryman from './orderDeliveryman';
import { fetchSellerDeliverymans } from 'redux/slices/deliveryman';
import { useTranslation } from 'react-i18next';
import numberToPrice from 'helpers/numberToPrice';
import { fetchRestOrderStatus } from 'redux/slices/orderStatus';
import ShowLocationsMap from './show-locations.map';
import {
  BsCalendarDay,
  BsFillPersonFill,
  BsFillTelephoneFill,
} from 'react-icons/bs';
import { MdEmail, MdLocationOn } from 'react-icons/md';
import { IMG_URL } from 'configs/app-global';
import { BiMessageDots, BiMoney } from 'react-icons/bi';
import { FiShoppingCart } from 'react-icons/fi';
import moment from 'moment';
import useDemo from 'helpers/useDemo';
import hideEmail from 'components/hideEmail';
import { useNavigate } from 'react-router-dom';
import QrCode from 'components/qr-code';
import { TrackingInfo } from 'components/tracking-info';
import { toast } from 'react-toastify';
import TransactionStatusModal from './transaction-status-modal';
import { getHourFormat } from 'helpers/getHourFormat';
import TransactionStatusChangeModal from './transactionStatusModal';

const formatPickupRange = (pickup, locale) => {
  if (!pickup?.window_start || !pickup?.window_end) return null;
  if (!pickup.timezone) return null;
  const start = new Date(pickup.window_start);
  const end = new Date(pickup.window_end);
  if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return null;
  return {
    date: new Intl.DateTimeFormat(locale || 'en', {
      weekday: 'short',
      month: 'short',
      day: 'numeric',
      timeZone: pickup.timezone,
    }).format(start),
    times: `${new Intl.DateTimeFormat(locale || 'en', {
      hour: '2-digit',
      minute: '2-digit',
      timeZone: pickup.timezone,
    }).format(start)} – ${new Intl.DateTimeFormat(locale || 'en', {
      hour: '2-digit',
      minute: '2-digit',
      timeZone: pickup.timezone,
    }).format(end)}`,
  };
};

export default function SellerOrderDetails() {
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { defaultCurrency } = useSelector(
    (state) => state.currency,
    shallowEqual,
  );
  const { statusList } = useSelector(
    (state) => state.orderStatus,
    shallowEqual,
  );
  const navigate = useNavigate();
  const data = activeMenu?.data?.data;
  const isPickupOrder = data?.delivery_type === 'pickup' || !!data?.pickup;
  const { t, i18n } = useTranslation();
  const { id } = useParams();
  const dispatch = useDispatch();
  const totalPriceRef = useRef();
  const productListRef = useRef();

  const { isDemo } = useDemo();
  const [loading, setLoading] = useState(false);
  const [orderId, setOrderId] = useState(null);
  const [orderDeliveryDetails, setOrderDeliveryDetails] = useState(null);
  const [locationsMap, setLocationsMap] = useState(null);
  const { myShop } = useSelector((state) => state.myShop, shallowEqual);
  const { defaultLang } = useSelector((state) => state.formLang, shallowEqual);
  const [isUpdating, setIsUpdating] = useState(false);
  const [transactionStatusModal, setTransactionStatusModal] = useState(false);
  const [isTransactionStatusModalOpen, setIsTransactionStatusModalOpen] =
    useState(null);
  const hourFormat = getHourFormat();
  const pickupRange = formatPickupRange(data?.pickup, i18n.language);

  const expandedRowRender = (record) => {
    if (!record?.replace_stock) return;
    const columns = [
      {
        title: t('id'),
        dataIndex: 'id',
        key: 'id',
        render: (_, row) => row?.replace_stock?.id,
      },
      {
        title: t('product.name'),
        dataIndex: 'product',
        key: 'product',
        render: (_, row) => (
          <Space direction='vertical' className='relative'>
            {row?.replace_stock?.product?.translation?.title}
            {row?.replace_stock?.extras?.map((extra) => (
              <Tag key={extra?.id}>
                {extra.group?.translation?.title}: {extra.value?.value}
              </Tag>
            ))}
          </Space>
        ),
      },
      {
        title: t('image'),
        dataIndex: 'img',
        key: 'img',
        render: (_, row) => (
          <Image
            src={getImage(row?.replace_stock?.product?.img)}
            alt='product'
            width={100}
            height='auto'
            className='rounded'
            preview
            placeholder
          />
        ),
      },
      {
        title: t('price'),
        dataIndex: 'price',
        key: 'price',
        render: (_, row) =>
          numberToPrice(row?.replace_stock?.price, defaultCurrency?.symbol),
      },
      {
        title: t('quantity'),
        dataIndex: 'replace_quantity',
        key: 'replace_quantity',
        render: (quantity, row) => (
          <span>{quantity * (row?.replace_stock?.product?.interval || 1)}</span>
        ),
      },
      {
        title: t('discount'),
        dataIndex: 'discount',
        key: 'discount',
        render: (_, row) =>
          numberToPrice(
            row?.replace_stock?.discount ?? 0,
            defaultCurrency?.symbol,
          ),
      },
      {
        title: t('tax'),
        dataIndex: 'tax',
        key: 'tax',
        render: (_, row) =>
          numberToPrice(row?.replace_stock?.tax, defaultCurrency?.symbol),
      },
      {
        title: t('total.price'),
        dataIndex: 'total_price',
        key: 'total_price',
        render: (_, row) => {
          return numberToPrice(
            row?.replace_stock?.total_price,
            defaultCurrency?.symbol,
          );
        },
      },
      {
        title: t('note'),
        dataIndex: 'note',
        key: 'note',
        render: (note) => note || '--',
      },
    ];

    return (
      <>
        <span
          style={{
            display: 'flex',
            justifyContent: 'center',
            alignItems: 'center',
          }}
        >
          <SwapOutlined
            style={{
              padding: '10px',
              margin: '10px 0 50px 0',
              border: '2px solid #cad2de',
              borderRadius: '30%',
            }}
            rotate={90}
          />
        </span>
        <Table columns={columns} dataSource={[record]} pagination={false} />
      </>
    );
  };

  const columns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      render: (_, row) => {
        return row.stock?.id;
      },
    },
    {
      title: t('product.name'),
      dataIndex: 'product',
      key: 'product',
      render: (_, row) => (
        <Space direction='vertical' className='relative'>
          {row.stock?.product?.translation?.title}
          {row.stock?.extras?.map((extra) => (
            <Tag key={extra?.id}>
              {extra.group?.translation?.title}: {extra.value?.value}
            </Tag>
          ))}
        </Space>
      ),
    },
    {
      title: t('image'),
      dataIndex: 'img',
      key: 'img',
      render: (_, row) => (
        <Image
          src={getImage(row.stock?.product?.img)}
          alt='product'
          width={100}
          height='auto'
          className='rounded'
          preview
          placeholder
        />
      ),
    },
    {
      title: t('price'),
      dataIndex: 'origin_price',
      key: 'origin_price',
      render: (_, row) =>
        row?.bonus ? (
          <Tag color='green'>{t('bonus')}</Tag>
        ) : (
          numberToPrice(row?.stock?.price, defaultCurrency?.symbol)
        ),
    },
    {
      title: t('quantity'),
      dataIndex: 'quantity',
      key: 'quantity',
      render: (quantity, row) => (
        <span>{quantity * (row?.stock?.product?.interval || 1)}</span>
      ),
    },
    {
      title: t('discount'),
      dataIndex: 'rate_discount',
      key: 'rate_discount',
      render: (_, row) =>
        numberToPrice(row?.stock?.discount, defaultCurrency?.symbol),
    },
    {
      title: t('tax'),
      dataIndex: 'tax',
      key: 'tax',
      render: (_, row) => numberToPrice(row?.tax, defaultCurrency?.symbol),
    },
    {
      title: t('total.price'),
      dataIndex: 'total_price',
      key: 'total_price',
      render: (_, row) => {
        return numberToPrice(row?.total_price, defaultCurrency?.symbol);
      },
    },
    {
      title: t('actions'),
      dataIndex: 'actions',
      key: 'actions',
      render: (_, row) => (
        <Space>
          <Button
            type={'primary'}
            icon={<SwapOutlined />}
            onClick={() => goToReplaceProduct(row)}
            disabled={
              (data?.status !== 'new' && data?.status !== 'accepted') ||
              row?.bonus
            }
          />
        </Space>
      ),
    },
  ];

  const paymentTableColumns = [
    {
      title: t('type'),
      key: 'payment_type',
      render: (row) => <span>{t(row.payment_system?.tag || 'N/A')}</span>,
    },
    {
      title: t('amount'),
      key: 'amount',
      render: (row) => (
        <span>{numberToPrice(row.price, defaultCurrency?.symbol)}</span>
      ),
    },
    {
      title: t('status'),
      key: 'status',
      render: (row) => (
        <span>
          <Tag>{t(row.status || 'N/A')}</Tag>
          {!!row && row.status !== 'split' && (
            <EditOutlined
              onClick={() => setIsTransactionStatusModalOpen(row)}
            />
          )}
        </span>
      ),
    },
  ];

  const documentColumns = [
    {
      title: t('date'),
      dataIndex: 'date',
      key: 'date',
      render: (date) => moment(date).format(`YYYY-MM-DD ${hourFormat}`),
    },
    {
      title: t('document'),
      dataIndex: 'document',
      key: 'document',
    },
    {
      title: t('number'),
      dataIndex: 'number',
      key: 'number',
    },
    {
      title: t('total.price'),
      dataIndex: 'price',
      key: 'price',
    },
  ];

  const documents = [
    {
      price: numberToPrice(data?.total_price, defaultCurrency.symbol),
      number: (
        <Link to={`/orders/generate-invoice/${data?.id}`}>#{data?.id}</Link>
      ),
      document: t('invoice'),
      date: moment(data?.created_at).format(`YYYY-MM-DD ${hourFormat}`),
    },
    {
      price: '-',
      number: (
        <Link to={`/orders/generate-invoice/${data?.id}`}>#{data?.id}</Link>
      ),
      document: t('delivery.receipt'),
      date: moment(data?.created_at).format(`YYYY-MM-DD ${hourFormat}`),
    },
  ];

  const handleCloseModal = () => {
    setOrderId(null);
    setOrderDeliveryDetails(null);
    setLocationsMap(null);
  };

  function fetchOrder() {
    setLoading(true);
    orderService
      .getById(id)
      .then(({ data }) => {
        const currency = data.currency;
        const user = data.user;
        const id = data.id;
        const price = data.price;
        const createdAt = data.created_at;
        const details = data.details.map((item) => ({
          ...item,
          title: item.shop?.translation?.title,
        }));
        dispatch(
          setMenuData({
            activeMenu,
            data: { details, currency, user, id, createdAt, price, data },
          }),
        );
      })
      .finally(() => {
        setLoading(false);
        dispatch(disableRefetch(activeMenu));
      });
  }

  const goToReplaceProduct = (row) => {
    dispatch(
      addMenu({
        url: `seller/order/details/${data?.id}/replace/${row?.stock?.id}`,
        id: 'seller_replace_product',
        name: t('replace.product'),
      }),
    );
    navigate(`replace/${row?.stock?.id}`, {
      state: { data },
    });
  };

  const goToEdit = () => {
    dispatch(
      addMenu({
        url: `seller/order/${id}`,
        id: 'order_edit',
        name: t('edit.order'),
      }),
    );
    navigate(`/seller/order/${id}`);
  };

  const handleUpdateTracking = useCallback(
    (body) => {
      setIsUpdating(true);
      orderService
        .updateTracking(data?.id, body)
        .then(() => {
          toast.success(t('successfully.updated'));
          fetchOrder();
        })
        .finally(() => {
          setIsUpdating(false);
        });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [data?.id],
  );

  useEffect(() => {
    const data = {
      shop_id: myShop.id,
    };
    if (activeMenu.refetch) {
      fetchOrder();
      batch(() => {
        dispatch(fetchRestOrderStatus({}));
        dispatch(fetchSellerDeliverymans(data));
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  const handleShowModal = () => setLocationsMap(id);

  return (
    <div className='order_details'>
      <Card
        className='order-details-info'
        title={
          <>
            <FiShoppingCart className='mr-2 icon' />
            {`${t('order')} ${data?.id ? `#${data?.id} ` : ''}`}{' '}
            {t('from.order')} {data?.user?.firstname}{' '}
            {data?.user?.lastname || ''}
          </>
        }
        extra={
          data?.status !== 'delivered' &&
          data?.status !== 'canceled' &&
          data?.type === 2 ? (
            <Space>
              {data?.status !== 'delivered' &&
              data?.status !== 'canceled' &&
              data?.type !== 1 ? (
                <Button type='primary' onClick={() => setOrderId(data?.id)}>
                  {t('change.status')}
                </Button>
              ) : null}
              <Button type='primary' icon={<EditOutlined />} onClick={goToEdit}>
                {t('edit')}
              </Button>
            </Space>
          ) : (
            ''
          )
        }
      />

      <Row gutter={24}>
        <Col span={24}>
          <Card>
            <Space className='justify-content-between w-100'>
              <Space className='align-items-start'>
                <CalendarOutlined className='order-card-icon' />
                <div className='d-flex flex-column'>
                  <Typography.Text>{t('delivery.date')}</Typography.Text>
                  {loading ? (
                    <Skeleton.Button size={16} />
                  ) : (
                    <Typography.Text className='order-card-title'>
                      {moment(data?.delivery_date).format(
                        `YYYY-MM-DD ${hourFormat}`,
                      )}
                    </Typography.Text>
                  )}
                </div>
              </Space>
              <Space
                className='align-items-start'
                onClick={() =>
                  totalPriceRef.current.scrollIntoView({ behavior: 'smooth' })
                }
              >
                <BiMoney className='order-card-icon' />
                <div className='d-flex flex-column'>
                  <Typography.Text>{t('total.price')}</Typography.Text>
                  {loading ? (
                    <Skeleton.Button size={16} loading={loading} />
                  ) : (
                    <Typography.Text className='order-card-title'>
                      {numberToPrice(
                        data?.total_price,
                        defaultCurrency?.symbol,
                      )}
                    </Typography.Text>
                  )}
                </div>
              </Space>
              <Space className='align-items-start'>
                <BiMessageDots className='order-card-icon' />
                <div className='d-flex flex-column'>
                  <Typography.Text>{t('messages')}</Typography.Text>
                  {loading ? (
                    <Skeleton.Button size={16} />
                  ) : (
                    <Typography.Text className='order-card-title'>
                      {data?.review ? 1 : 0}
                    </Typography.Text>
                  )}
                </div>
              </Space>
              <Space
                className='align-items-start'
                onClick={() =>
                  productListRef.current.scrollIntoView({ behavior: 'smooth' })
                }
              >
                <FiShoppingCart className='order-card-icon' />
                <div className='d-flex flex-column'>
                  <Typography.Text>{t('products')}</Typography.Text>
                  {loading ? (
                    <Skeleton.Button size={16} />
                  ) : (
                    <Typography.Text className='order-card-title'>
                      {data?.details?.reduce(
                        (total, item) => (total += item.quantity),
                        0,
                      )}
                    </Typography.Text>
                  )}
                </div>
              </Space>
            </Space>
          </Card>
        </Col>
        {data?.status !== 'canceled' && (
          <Col span={24}>
            <Card>
              <Steps
                current={statusList?.findIndex(
                  (item) => item.name === data?.status,
                )}
              >
                {statusList
                  ?.slice(0, -1)
                  ?.filter((item) => item?.active)
                  ?.map((item) => (
                    <Steps.Step key={item.id} title={t(item.name)} />
                  ))}
              </Steps>
            </Card>
          </Col>
        )}
        <Col
          span={
            !!data?.user ||
            (data?.type === 2 && data?.delivery_type === 'delivery')
              ? 16
              : 24
          }
        >
          <Spin spinning={loading}>
            <Card style={{ minHeight: '200px' }}>
              <h3>{t('details')}:</h3>
              <Row hidden={loading} className='mb-3 order_detail' gutter={10}>
                <Col span={12}>
                  <div>
                    {t('created.date.&.time')}:
                    <span className='ml-2'>
                      <BsCalendarDay className='mr-1' />{' '}
                      {moment(data?.created_at).format(
                        `YYYY-MM-DD ${hourFormat}`,
                      )}{' '}
                    </span>
                  </div>
                  <br />
                  {data?.delivery_type === 'dine_in' && (
                    <div>
                      {t('table')}:
                      <span className='ml-2'>{data?.table?.name}</span>
                    </div>
                  )}
                  {data?.delivery_type !== 'dine_in' && (
                    <>
                      <div>
                        {t('delivery.date.&.time')}:
                        <span className='ml-2'>
                          <BsCalendarDay className='mr-1' />{' '}
                          {moment(data?.delivery_date).format(
                            `YYYY-MM-DD ${hourFormat}`,
                          )}
                        </span>
                      </div>
                    </>
                  )}
                </Col>
                <Col span={12}>
                  <div>
                    {t('status')}:
                    <span className='ml-2'>
                      {data?.status === 'new' ? (
                        <Tag color='blue'>{t(data?.status)}</Tag>
                      ) : data?.status === 'canceled' ? (
                        <Tag color='error'>{t(data?.status)}</Tag>
                      ) : (
                        <Tag color='cyan'>{t(data?.status)}</Tag>
                      )}
                    </span>
                  </div>
                  <br />
                  <div>
                    {t('delivery.type')}:
                    <span className='ml-2'>
                      {isPickupOrder
                        ? t('shop.pickup', { defaultValue: 'Shop pickup' })
                        : data?.delivery_type}
                    </span>
                  </div>
                  {data?.delivery_type !== 'dine_in' && (
                    <>
                      <br />
                      <div>
                        {t('payment.type')}:
                        <span className='ml-2'>
                          {t(data?.transaction?.payment_system?.tag)}
                        </span>
                      </div>
                    </>
                  )}
                  <br />
                  <div>
                    {t('otp')}:
                    <span className='ml-2'>{data?.otp ?? t('N/A')}</span>
                  </div>
                </Col>
              </Row>
              <Row hidden={loading} className='mb-3 order_detail'>
                <Col span={12}>
                  <Descriptions
                    bordered
                    title={
                      isPickupOrder
                        ? t('shop.pickup', { defaultValue: 'Shop pickup' })
                        : t('delivery.address')
                    }
                  >
                    {isPickupOrder ? (
                      <>
                        <Descriptions.Item
                          label={t('pickup.at.shop', {
                            defaultValue: 'Pickup at Shop',
                          })}
                          span={3}
                        >
                          {data?.pickup?.alias || data?.shop?.translation?.title || t('shop.pickup', { defaultValue: 'Shop pickup' })}
                        </Descriptions.Item>
                        <Descriptions.Item
                          label={t('address')}
                          span={3}
                        >
                          {data?.pickup?.address || t('no.address')}
                        </Descriptions.Item>
                        <Descriptions.Item
                          label={t('pickup.timing', {
                            defaultValue: 'Pickup timing',
                          })}
                          span={3}
                        >
                          {!data?.pickup
                            ? t('pickup.details.unavailable', {
                                defaultValue: 'Pickup details unavailable',
                              })
                            : data.pickup.timing_mode === 'scheduled'
                              ? pickupRange
                                ? `${pickupRange.date ? `${pickupRange.date} · ` : ''}${pickupRange.times}${data?.pickup?.timezone ? ` (${data.pickup.timezone})` : ''}`
                                : t('pickup.window.unavailable', {
                                    defaultValue: 'Scheduled pickup details unavailable',
                                  })
                              : t('pickup.asap.order.ready', {
                                  defaultValue: 'As soon as order is ready',
                                })}
                        </Descriptions.Item>
                      </>
                    ) : !!data?.address ? (
                      <>
                        <Descriptions.Item
                          label={t('street.house.number')}
                          span={3}
                        >
                          {data?.address?.street_house_number ?? t('not.given')}
                        </Descriptions.Item>
                        <Descriptions.Item label={t('zipcode')} span={3}>
                          {data?.address?.zipcode ??
                            data?.address?.zip_code ??
                            t('not.given')}
                        </Descriptions.Item>
                      </>
                    ) : !!data?.my_address ? (
                      <>
                        {data?.my_address?.firstname && (
                          <Descriptions.Item label={t('firstname')} span={3}>
                            {data?.my_address?.firstname}
                          </Descriptions.Item>
                        )}
                        {data?.my_address?.lastname && (
                          <Descriptions.Item label={t('lastname')} span={3}>
                            {data?.my_address?.lastname}
                          </Descriptions.Item>
                        )}
                        {data?.my_address?.phone && (
                          <Descriptions.Item label={t('phone')} span={3}>
                            {data?.my_address.phone.startsWith('+') ? (
                              <a href={`tel:${data?.my_address.phone}`}>
                                {data?.my_address.phone}
                              </a>
                            ) : (
                              <a href={`tel:+${data?.my_address.phone}`}>
                                +{data?.my_address.phone}
                              </a>
                            )}
                          </Descriptions.Item>
                        )}
                        {
                          <Descriptions.Item
                            label={t('street.house.number')}
                            span={3}
                          >
                            {data?.my_address?.street_house_number ??
                              t('not.given')}
                          </Descriptions.Item>
                        }

                        <Descriptions.Item
                          label={t('street.house.number')}
                          span={3}
                        >
                          {data?.my_address?.street_house_number ??
                            t('not.given')}
                        </Descriptions.Item>

                        <Descriptions.Item label={t('zipcode')} span={3}>
                          {data?.my_address?.zipcode ?? t('not.given')}
                        </Descriptions.Item>
                        {data?.my_address?.additional_details && (
                          <Descriptions.Item
                            label={t('additional.details')}
                            span={3}
                          >
                            {data?.my_address?.additional_details}
                          </Descriptions.Item>
                        )}
                      </>
                    ) : data?.delivery_point ? (
                      <>
                        <div>
                          {t('address')}:{' '}
                          {data?.delivery_point?.address?.[defaultLang] ??
                            t('no.address')}
                        </div>
                      </>
                    ) : data?.delivery_type === 'digital' ? (
                      <div>{t('digital.delivery')}</div>
                    ) : (
                      <div>{t('no.address')}</div>
                    )}
                  </Descriptions>
                  <br />
                  <span>{t('payments')}</span>
                  <Table
                    columns={paymentTableColumns}
                    dataSource={data?.transactions}
                    pagination={false}
                  />
                  {!isPickupOrder && (
                    <Button className='map_show mt-3' onClick={handleShowModal}>
                      <MdLocationOn style={{ marginRight: '8px' }} />{' '}
                      {t('show.locations')}
                    </Button>
                  )}
                </Col>
                <Col span={12}>
                  <QrCode orderId={id} />
                </Col>
              </Row>
            </Card>
          </Spin>
              {data?.delivery_type !== 'dine_in' && (
                <Card title={t('documents')}>
                  <Table
                    columns={documentColumns}
                    dataSource={documents}
                    pagination={false}
                    loading={loading}
                  />
                </Card>
              )}
        </Col>
        <Col span={8} className='order_info'>
          {data?.delivery_type !== 'dine_in' && !isPickupOrder && (
            <>
              {data?.delivery_type !== 'dine_in' &&
                (data?.type === 1 ||
                  (data?.type === 2 && data?.delivery_type === 'delivery')) && (
                <Card
                  title={t('deliveryman')}
                  extra={
                    data?.status === 'ready' &&
                    data?.delivery_type === 'delivery' && (
                      <Button onClick={() => setOrderDeliveryDetails(data)}>
                        {t('change')}
                        <EditOutlined />
                      </Button>
                    )
                  }
                >
                  {data?.status === 'new' || data?.status === 'accepted' ? (
                    <p>{t('order_status_ready')}</p>
                  ) : (
                    ''
                  )}
                  {data?.status !== 'new' &&
                  data?.status !== 'accepted' &&
                  !data?.deliveryman ? (
                    <p>
                      {t(
                        'The supplier is not assigned or delivery type pickup',
                      )}
                    </p>
                  ) : (
                    ''
                  )}

                  {data?.deliveryman && (
                    <Space>
                      <Avatar
                        shape='square'
                        size={64}
                        src={
                          data?.deliveryman?.img
                            ? IMG_URL + data.deliveryman.img
                            : undefined
                        }
                      />
                      <div>
                        <h5>
                          {data?.deliveryman?.firstname +
                            ' ' +
                            data?.deliveryman?.lastname}
                        </h5>
                        <span className='delivery-info'>
                          <BsFillTelephoneFill />
                          {data?.deliveryman?.phone}
                        </span>

                        <div className='delivery-info'>
                          <b>
                            <MdEmail size={16} />
                          </b>
                          <span>
                            {isDemo
                              ? hideEmail(data?.deliveryman?.email)
                              : data?.deliveryman?.email}
                          </span>
                        </div>
                      </div>
                    </Space>
                  )}
                </Card>
              )}

              {data?.type === 2 && data?.delivery_type === 'delivery' && (
                <Card title={t('tracking.info')}>
                  <TrackingInfo
                    onSubmit={handleUpdateTracking}
                    isSubmitting={isUpdating}
                    initialData={{
                      track_name: data?.track_name,
                      track_id: data?.track_id,
                      track_url: data?.track_url,
                    }}
                  />
                </Card>
              )}

              {!!data?.username && (
                <Card title={t('order.receiver')}>
                  <div className='customer-info'>
                    <span className='title'>{t('name')}</span>
                    <span className='description'>
                      <BsFillPersonFill />
                      {data?.username}
                    </span>
                  </div>
                  <div className='customer-info'>
                    <span className='title'>{t('phone')}</span>
                    <span className='description'>
                      <BsFillTelephoneFill />
                      {data?.phone}
                    </span>
                  </div>
                </Card>
              )}
              {/*customer details*/}
              {!!data?.user && (
                <Card>
                  <div className='d-flex w-100 customer-info-container'>
                    {loading ? (
                      <Skeleton.Avatar size={64} shape='square' />
                    ) : (
                      <Avatar shape='square' size={64} src={data?.user?.img} />
                    )}

                    <h5 className='customer-name'>
                      {loading ? (
                        <Skeleton.Button size={20} style={{ width: 70 }} />
                      ) : (
                        data?.user?.firstname +
                        ' ' +
                        (data?.user?.lastname || '')
                      )}
                    </h5>

                    <div className='customer-info-detail'>
                      <div className='customer-info'>
                        <span className='title'>{t('phone')}</span>
                        <span className='description'>
                          <BsFillTelephoneFill />
                          {loading ? (
                            <Skeleton.Button size={16} />
                          ) : (
                            (data?.user?.phone ?? 'none')
                          )}
                        </span>
                      </div>

                      <div className='customer-info'>
                        <span className='title'>{t('email')}</span>
                        <span className='description'>
                          <MdEmail />
                          {loading ? (
                            <Skeleton.Button size={16} />
                          ) : isDemo ? (
                            hideEmail(data?.user?.email)
                          ) : (
                            data?.user?.email
                          )}
                        </span>
                      </div>
                      <div className='customer-info'>
                        <span className='title'>{t('registration.date')}</span>
                        <span className='description'>
                          <BsCalendarDay />
                          {loading ? (
                            <Skeleton.Button size={16} />
                          ) : (
                            moment(data?.user?.created_at).format(
                              `YYYY-MM-DD ${hourFormat}`,
                            )
                          )}
                        </span>
                      </div>
                      <div className='customer-info'>
                        <span className='title'>{t('orders.count')}</span>
                        <span className='description'>
                          {loading ? (
                            <Skeleton.Button size={16} />
                          ) : (
                            <Badge
                              showZero
                              style={{ backgroundColor: '#3d7de3' }}
                              count={data?.user?.o_count || 0}
                            />
                          )}
                        </span>
                      </div>
                      <div className='customer-info'>
                        <span className='title'>
                          {t('spent.since.registration')}
                        </span>
                        <span className='description'>
                          {loading ? (
                            <Skeleton.Button size={16} />
                          ) : (
                            <Badge
                              showZero
                              style={{ backgroundColor: '#48e33d' }}
                              count={numberToPrice(
                                data?.user?.o_sum,
                                defaultCurrency?.symbol,
                              )}
                            />
                          )}
                        </span>
                      </div>
                    </div>
                  </div>
                </Card>
              )}
              {data?.review && !loading && (
                <Card title={t('messages')}>
                  <div className='order-message'>
                    <span className='message'>{data?.review?.comment}</span>
                    <Space className='w-100 justify-content-end'>
                      <span className='date'>
                        {moment(data?.review?.created_at).format(
                          'YYYY-MM-DD HH:mm',
                        )}
                      </span>
                    </Space>
                  </div>
                </Card>
              )}
            </>
          )}
        </Col>
        <Col span={24}>
          <Card className='w-100 order-table' title={t('order.products')}>
            <Divider />
            <Table
              ref={productListRef}
              scroll={{ x: true }}
              columns={columns}
              dataSource={activeMenu.data?.details || []}
              loading={loading}
              rowKey={(record) => record.id}
              pagination={false}
              expandable={{
                expandedRowRender,
                expandIcon: ({ expanded, onExpand, record }) =>
                  expanded ? (
                    <SwapOutlined
                      onClick={(e) => onExpand(record, e)}
                      rotate={90}
                    />
                  ) : (
                    <SwapOutlined
                      onClick={(e) => onExpand(record, e)}
                      rotate={90}
                    />
                  ),
                defaultExpandedRowKeys: ['0'],
              }}
            />
            <Space
              size={100}
              className='d-flex justify-content-end w-100 order-table__summary'
            >
              <div>
                {!isPickupOrder && (
                  <>
                    <span>{t('delivery.fee')}:</span>
                    <br />
                  </>
                )}
                <span>{t('order.tax')}:</span>
                <br />
                <span>{t('product')}:</span>
                <br />
                <span>{t('service.fee')}:</span>
                <br />
                <span>{t('tips')}:</span>
                <br />
                <span>{t('discount')}:</span>
                <br />
                <span>{t('coupon.price')}:</span>
                <br />
                <span>{t('tax')}:</span>
                <br />
                <h3>{t('total.price')}:</h3>
              </div>
              <div>
                <span>
                  {!isPickupOrder &&
                    numberToPrice(
                      data?.delivery_fee ?? 0,
                      defaultCurrency?.symbol,
                    )}
                </span>
                {!isPickupOrder && <br />}
                <span>
                  {numberToPrice(data?.total_tax, defaultCurrency?.symbol)}
                </span>
                <br />
                <span>
                  {numberToPrice(data?.origin_price, defaultCurrency?.symbol)}
                </span>
                <br />
                <span>
                  {numberToPrice(data?.service_fee, defaultCurrency?.symbol)}
                </span>
                <br />
                <span>
                  {numberToPrice(data?.tips, defaultCurrency?.symbol)}
                </span>
                <br />
                <span>
                  -{' '}
                  {numberToPrice(data?.total_discount, defaultCurrency?.symbol)}
                </span>
                <br />
                <span>
                  - {numberToPrice(data?.coupon_price, defaultCurrency?.symbol)}
                </span>
                <br />
                <span>{numberToPrice(data?.tax, defaultCurrency?.symbol)}</span>
                <br />
                <h3 ref={totalPriceRef}>
                  {numberToPrice(data?.total_price, defaultCurrency?.symbol)}
                </h3>
              </div>
            </Space>
          </Card>
        </Col>
      </Row>
      {orderId && (
        <OrderStatusModal orderId={orderId} handleCancel={handleCloseModal} />
      )}
      {orderDeliveryDetails && (
        <OrderDeliveryman
          orderDetails={orderDeliveryDetails}
          handleCancel={handleCloseModal}
        />
      )}
      {locationsMap && (
        <ShowLocationsMap id={locationsMap} handleCancel={handleCloseModal} />
      )}
      {transactionStatusModal && (
        <TransactionStatusModal
          orderId={id}
          handleCancel={() => setTransactionStatusModal(false)}
          transactionDetails={data?.transaction}
          fetchOrder={fetchOrder}
        />
      )}
      {!!isTransactionStatusModalOpen && (
        <Modal
          visible={!!isTransactionStatusModalOpen}
          footer={false}
          onCancel={() => setIsTransactionStatusModalOpen(null)}
        >
          <TransactionStatusChangeModal
            data={isTransactionStatusModalOpen}
            onCancel={() => setIsTransactionStatusModalOpen(null)}
          />
        </Modal>
      )}
    </div>
  );
}
