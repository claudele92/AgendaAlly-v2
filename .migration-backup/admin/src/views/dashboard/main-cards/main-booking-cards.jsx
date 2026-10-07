import React, { useMemo } from 'react';
import cls from './main-cards.module.scss';
import { Button, Image, Typography } from 'antd';
import TotalOrdersIcon from 'assets/images/total-orders.svg';
import { setMenu } from 'redux/slices/menu';
import OrderCard from '../card';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import NewOrdersIcon from 'assets/images/new-orders.svg';
import ConfirmedOrdersIcon from 'assets/images/confirmed-orders.svg';
import ReadyOrdersIcon from 'assets/images/ready-orders.svg';
import DeliveredOrdersIcon from 'assets/images/delivered-orders.svg';
import CanceledOrdersIcon from 'assets/images/canceled-orders.svg';
import { useNavigate } from 'react-router-dom';
import BookingsPercentStatistics from '../bookings-percent';
import BookingsTotalStatistics from '../bookings-total';
import { useNavigationScope } from 'context/navigation-scope';
import { resolveDashboardBookingsPath } from '../dashboard-statistics-role.mjs';

export function MainBookingCards() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { user } = useSelector((state) => state.auth, shallowEqual);
  const navigationScope = useNavigationScope();
  const { statisticsData, seller, master, loading } = useSelector(
    (state) => state.bookingsReport,
    shallowEqual,
  );
  const bookingPath = useMemo(
    () => resolveDashboardBookingsPath(user, navigationScope),
    [navigationScope, user],
  );

  const data =
    user?.role === 'seller'
      ? seller.statisticsData
      : user?.role === 'master'
        ? master.statisticsData
        : statisticsData;

  const cardList = [
    {
      title: t('new'),
      number: data?.new?.count,
      img: NewOrdersIcon,
      color: 'var(--dark-blue)',
      id: 'new_bookings',
    },
    {
      title: t('booked'),
      number: data?.booked?.count,
      img: ConfirmedOrdersIcon,
      color: 'var(--dark-blue)',
      id: 'booked_bookings',
    },
    {
      title: t('progress'),
      number: data?.progress?.count,
      img: ReadyOrdersIcon,
      color: 'var(--dark-blue)',
      id: 'progress_bookings',
    },
    {
      title: t('cancelled'),
      number: data?.canceled?.count,
      img: CanceledOrdersIcon,
      color: 'var(--green)',
      id: 'cancelled_bookings',
    },
    {
      title: t('ended'),
      number: data?.ended?.count,
      img: DeliveredOrdersIcon,
      color: 'var(--primary)',
      id: 'ended_bookings',
    },
  ];

  return (
    <div className={cls.container}>
      <Typography.Title level={2} className='mb-4'>
        {t('bookings.count.statistics')}
      </Typography.Title>
      <div className={cls.wrapper}>
        {loading ? (
          <div className={`${cls.mainCard} skeleton`} />
        ) : (
          <div className={cls.mainCard}>
            <div className={cls.header}>
              <span className={cls.title}>{t('total.bookings')}</span>
              <div className={cls.circle} />
            </div>
            <div className={cls.body}>
              <span className={cls.number}>{data?.total_count || 0}</span>
              <div className={cls.iconContainer}>
                <div className={cls.icon}>
                  <Image src={TotalOrdersIcon} preview={false} />
                </div>
              </div>
            </div>
            {bookingPath && (
              <Button
                type='primary'
                className='w-100'
                onClick={() => {
                  dispatch(
                    setMenu({
                      id: 'bookings',
                      url: bookingPath,
                      name: t('all'),
                      refetch: true,
                    }),
                  );
                  navigate(`/${bookingPath}`);
                }}
              >
                {t('all.bookings')}
              </Button>
            )}
          </div>
        )}
        <div className={cls.cards}>
          {cardList.map((item) => (
            <OrderCard key={item.id} data={item} loading={loading} />
          ))}
        </div>
      </div>
      <Typography.Title level={2} className='my-4'>
        {t('bookings.percent.statistics')}
      </Typography.Title>
      <BookingsPercentStatistics isSeller={user?.role === 'seller'} />
      <Typography.Title level={2} className='my-4'>
        {t('bookings.total.statistics')}
      </Typography.Title>
      <BookingsTotalStatistics isSeller={user?.role === 'seller'} />
    </div>
  );
}
