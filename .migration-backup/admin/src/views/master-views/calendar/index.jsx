import React, { useEffect } from 'react';
import ActionTypeSelection from './components/action-type-selection';
import 'react-big-calendar/lib/css/react-big-calendar.css';
import BookingContextProvider from './provider';
import { useDispatch } from 'react-redux';
import { fetchMasterBookingList } from 'redux/slices/booking';
import CalendarView from './components/calendar';
import ServiceViews from './components/service-views';
import AddForm from './components/add-form';
import FormDetail from './components/form-detail';
import UpdateStatus from './components/update-status';
import { Card, Divider, Space, Typography } from 'antd';
import { useTranslation } from 'react-i18next';

const MyCalendar = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();

  useEffect(() => {
    dispatch(fetchMasterBookingList());
  }, [dispatch]);

  return (
    <BookingContextProvider>
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
            {t('booking.calendar')}
          </Typography.Title>
        </Space>
        <Divider color='var(--divider)' />
        <CalendarView />
        <ActionTypeSelection />
        <ServiceViews />
        <AddForm />
        <FormDetail />
        <UpdateStatus />
      </Card>
    </BookingContextProvider>
  );
};

export default MyCalendar;
