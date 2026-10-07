import { Card, Col, Divider, Space, Table, Tabs, Tag, Typography } from 'antd';
import { useTranslation } from 'react-i18next';
import { DeleteOutlined, EditOutlined, EyeOutlined } from '@ant-design/icons';
import React, { Fragment, useContext, useEffect, useState } from 'react';
import { batch, shallowEqual, useDispatch, useSelector } from 'react-redux';
import useDidUpdate from 'helpers/useDidUpdate';
import { fetchSellerBookings } from 'redux/slices/bookings';
import { disableRefetch } from 'redux/slices/menu';
import StatusModal from './status-modal';
import bookingService from 'services/seller/booking';
import SearchInput from 'components/search-input';
import CustomModal from 'components/modal';
import { toast } from 'react-toastify';
import { Context } from 'context/context';
import numberToPrice from 'helpers/numberToPrice';
import moment from 'moment';
import { useQueryParams } from 'helpers/useQueryParams';
import BookingDetailsModal from './details-modal';
import { getHourFormat } from '../../../helpers/getHourFormat';
import tableRowClasses from '../../../assets/scss/components/table-row.module.scss';
import './responsive.css';

const { TabPane } = Tabs;
const bookingTypes = ['all', 'offline_in', 'offline_out', 'online'];

export default function Booking() {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const queryParams = useQueryParams();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { bookings, params, loading, meta } = useSelector(
    (state) => state.bookings.seller,
    shallowEqual,
  );
  const { setIsModalVisible } = useContext(Context);
  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [paramsData, setParamsData] = useState({
    ...params,
  });
  const [bookingData, setBookingData] = useState(null);
  const hourFormat = getHourFormat();
  const columns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      is_show: true,
    },
    {
      title: t('name.client'),
      dataIndex: 'user',
      key: 'name.client',
      is_show: true,
      render: (user) => `${user?.firstname || ''} ${user?.lastname || ''}`,
    },
    {
      title: t('contact'),
      dataIndex: 'master',
      key: 'contact',
      is_show: true,
      render: (master) =>
        master.phone ? (
          <a href={`tel:+${master?.phone}`}> +{master?.phone}</a>
        ) : (
          t('unknown')
        ),
    },
    {
      title: t('service'),
      dataIndex: 'service_master',
      key: 'service',
      is_show: true,
      render: (serviceMaster) => serviceMaster?.service?.translation?.title,
    },
    {
      title: t('start.date'),
      dataIndex: 'start_date',
      key: 'start_date',
      is_show: true,
      render: (startDate) =>
        moment.utc(startDate).format(`YYYY-MM-DD ${hourFormat}`),
    },
    {
      title: t('payment.type'),
      is_show: true,
      dataIndex: 'transaction',
      key: 'transaction',
      render: (transaction) => (
        <div className={tableRowClasses.paymentStatuses}>
          <span style={{ color: 'var(--green)' }}>
            {t(transaction?.payment_system?.tag) || '-'}
          </span>
        </div>
      ),
    },
    {
      title: t('master'),
      dataIndex: 'master',
      key: 'master',
      is_show: true,
      render: (master) =>
        `${master?.firstname || ''} ${master?.lastname || ''}`,
    },
    {
      title: t('type'),
      dataIndex: 'type',
      key: 'type',
      is_show: false,
      render: (type) => t(type),
    },
    {
      title: t('order.status'),
      is_show: true,
      dataIndex: 'status',
      key: 'status',
      render: (status, row) => {
        const isCanEdit = status !== 'ended' && status !== 'canceled';
        return (
          <button
            type='button'
            onClick={(e) => {
              e.stopPropagation();
              if (isCanEdit) {
                setBookingData({ status, id: row?.id });
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
      title: t('price'),
      dataIndex: 'total_price',
      key: 'price',
      is_show: true,
      render: (total_price, row) =>
        numberToPrice(
          total_price,
          row?.currency?.symbol,
          row?.currency?.position,
        ),
    },
    {
      title: t('options'),
      key: 'options',
      is_show: true,
      render: (row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.details}`}
            disabled={row?.deleted_at}
            onClick={(e) => {
              e.stopPropagation();
              queryParams.set('bookingId', row?.id);
            }}
          >
            <EyeOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            disabled={row?.deleted_at}
            onClick={(e) => {
              setIsModalVisible(true);
              setId([row?.id]);
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ];

  useEffect(() => {
    if (activeMenu.refetch) {
      batch(() => {
        dispatch(fetchSellerBookings(paramsData));
        dispatch(disableRefetch(activeMenu));
      });
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    dispatch(fetchSellerBookings(paramsData));
  }, [paramsData]);

  const handleFilter = (type, value) => {
    setParamsData((prevParams) => {
      if (value === 'all') {
        const newParams = { ...prevParams };
        delete newParams[type];
        return { ...newParams, page: 1 };
      } else {
        return { ...prevParams, [type]: value, page: 1 };
      }
    });
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

    bookingService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setId(null);
        setIsModalVisible(false);
        setParamsData((prev) => ({ ...prev, page: 1 }));
      })
      .finally(() => setLoadingBtn(false));
  };

  const onChangePagination = (pagination) => {
    const { pageSize, current } = pagination;
    const params = {
      ...paramsData,
      perPage: pageSize,
      page: current,
    };
    setParamsData((prev) => ({ ...prev, ...params }));
  };

  const handleChangeStatus = (id, params) => {
    return bookingService.changeStatus(id, params).then(() =>
      batch(() => {
        dispatch(fetchSellerBookings(paramsData));
        dispatch(disableRefetch(activeMenu));
      }),
    );
  };

  return (
    <Fragment>
      <Card className='aa-seller-bookings-card'>
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
            {t('bookings')}
          </Typography.Title>
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
              handleChange={(e) => {
                setTimeout(() => {
                  handleFilter('search', e);
                }, 500);
              }}
              placeholder={t('search')}
            />
          </Col>
        </div>
        <Divider color='var(--divider)' />
        <Tabs type='card' onChange={(value) => handleFilter('type', value)}>
          {bookingTypes.map((type) => (
            <TabPane tab={t(type)} key={type} />
          ))}
        </Tabs>
        <div
          className='aa-seller-bookings-table'
          role='region'
          aria-label={t('bookings')}
          tabIndex={0}
        >
          <Table
            columns={columns?.filter((column) => column?.is_show)}
            dataSource={bookings}
            loading={loading}
            scroll={{ x: true }}
            pagination={{
              pageSize: meta?.per_page,
              current: meta?.current_page,
              total: meta?.total,
            }}
            onChange={onChangePagination}
          />
        </div>
      </Card>
      {bookingData && (
        <StatusModal
          data={bookingData}
          handleClose={() => setBookingData(null)}
          handleSubmit={handleChangeStatus}
        />
      )}
      <CustomModal
        click={handleDelete}
        text={t('are.you.sure')}
        loading={loadingBtn}
      />
      <BookingDetailsModal />
    </Fragment>
  );
}
