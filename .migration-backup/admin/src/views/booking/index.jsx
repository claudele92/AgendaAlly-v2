import { Button, Card, Col, Divider, Space, Table, Typography } from 'antd';
import { useTranslation } from 'react-i18next';
import { DeleteOutlined, EditOutlined, EyeOutlined } from '@ant-design/icons';
import React, { useContext, useEffect, useState } from 'react';
import { batch, shallowEqual, useDispatch, useSelector } from 'react-redux';
import useDidUpdate from 'helpers/useDidUpdate';
import { fetchBookings } from 'redux/slices/bookings';
import { disableRefetch } from 'redux/slices/menu';
import StatusModal from './status-modal';
import bookingService from 'services/booking';
import SearchInput from 'components/search-input';
import CustomModal from 'components/modal';
import { toast } from 'react-toastify';
import { Context } from 'context/context';
import numberToPrice from 'helpers/numberToPrice';
import moment from 'moment/moment';
import BookingDetailsModal from './details-modal';
import { useQueryParams } from 'helpers/useQueryParams';
import { getHourFormat } from 'helpers/getHourFormat';
import FilterColumns from 'components/filter-column';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import './responsive.css';

const bookingTypes = ['all', 'offline_in', 'offline_out', 'online'];

export default function Booking() {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const hourFormat = getHourFormat();
  const { bookings, params, loading, meta } = useSelector(
    (state) => state.bookings,
    shallowEqual,
  );
  const queryParams = useQueryParams();
  const { setIsModalVisible } = useContext(Context);
  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [paramsData, setParamsData] = useState({
    ...params,
  });
  const [bookingData, setBookingData] = useState(null);

  const [columns, setColumns] = useState([
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
          <a target='_blank' href={`tel:+${master?.phone}`}>
            {' '}
            +{master?.phone}
          </a>
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
      dataIndex: 'transactions',
      key: 'transactions',
      render: (transactions) => (
        <div className={tableRowClasses.paymentStatuses}>
          {transactions?.map((transaction) => (
            <span key={transaction?.id} style={{ color: 'var(--green)' }}>
              {t(transaction?.payment_system?.tag)}
            </span>
          ))}
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
      title: t('shop'),
      dataIndex: 'shop',
      key: 'shop',
      is_show: true,
      render: (shop) => `${shop?.translation?.title || '--'}`,
    },
    {
      title: t('type'),
      dataIndex: 'type',
      key: 'type',
      is_show: false,
      render: (type) => t(type),
    },
    {
      title: t('status'),
      dataIndex: 'status',
      key: 'status',
      is_show: true,
      render: (status, row) => {
        return (
          <div className={tableRowClasses.paymentStatuses}>
            <span
              className={`${tableRowClasses.paymentStatus} ${tableRowClasses[status || 'progress']}`}
            >
              {t(status)}
            </span>
            {status !== 'ended' && status !== 'canceled' && (
              <EditOutlined
                onClick={(e) => {
                  e.stopPropagation();
                  setBookingData({ status, id: row?.id });
                }}
              />
            )}
          </div>
        );
      },
    },
    {
      title: t('price'),
      dataIndex: 'total_price',
      key: 'price',
      is_show: true,
      render: (price, row) =>
        numberToPrice(price, row?.currency?.symbol, row?.currency?.position),
    },
    {
      title: t('options'),
      key: 'options',
      is_show: true,
      render: (row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
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
            onClick={() => {
              setIsModalVisible(true);
              setId([row.id]);
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
        dispatch(fetchBookings(paramsData));
        dispatch(disableRefetch(activeMenu));
      });
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    dispatch(fetchBookings(paramsData));
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
        dispatch(fetchBookings(paramsData));
        dispatch(disableRefetch(activeMenu));
      }),
    );
  };

  return (
    <Card className='mvp-bookings-page'>
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
        <Col style={{ minWidth: 0, width: '253px', maxWidth: '100%' }}>
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
      <div className='mvp-bookings-controls'>
        <div className='mvp-booking-type-buttons' role='group' aria-label='Booking type'>
          {bookingTypes.map((type) => (
            <Button key={type} aria-pressed={(paramsData.type || 'all') === type} type={(paramsData.type || 'all') === type ? 'primary' : 'default'} onClick={() => handleFilter('type', type)}>
              {t(type)}
            </Button>
          ))}
        </div>
        <FilterColumns columns={columns} setColumns={setColumns} />
      </div>
      <Card>
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
    </Card>
  );
}
