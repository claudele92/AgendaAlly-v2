import moment from 'moment';
import React, { useCallback, useContext, useEffect } from 'react';
import { momentLocalizer } from 'react-big-calendar';
import { useSelector } from 'react-redux';
import bookingService from 'services/master/booking';
import { BookingContext } from '../provider';
import { useNavigate } from 'react-router-dom';
import { fetchMasterDisabledTimes } from 'redux/slices/disabledTimes';
import { fetchMasterBookingList } from 'redux/slices/booking';
import { useDispatch } from 'react-redux';
import { useQueryParams } from 'helpers/useQueryParams';
import { getHourFormat } from '../../../../helpers/getHourFormat';
import ScheduleSurface from '../../../../components/scheduling/ScheduleSurface';

const CalendarView = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const localizer = momentLocalizer(moment); // or globalizeLocalizer
  const { bookingList, loading, error } = useSelector((state) => state.booking);
  const { disabledTimes } = useSelector((state) => state.disabledTimes);
  const queryParams = useQueryParams();

  const {
    infoForm,
    setIsLoading,
    setViewContent,
    setIsModalOpen,
    setSelectedSlots,
    setCalculatedData,
    setServiceData,
  } = useContext(BookingContext);
  const hourFormat = getHourFormat();

  const showModal = ({ start, end }) => {
    setIsModalOpen(true);
    setSelectedSlots({ start, end });
  };

  useEffect(() => {
    if (queryParams.values?.notify_service_id) {
      const event = {
        id: queryParams.values?.notify_service_id,
        parent_id: queryParams.values?.notify_service_id,
      };
      handleSelectEvent(event);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [queryParams.values?.notify_service_id]);

  const handleSelectEvent = useCallback((event) => {
    if (event.disabled) {
      setViewContent('updateBlockTime');
      navigate(`?disabled_slot_id=${event.id}`);
      return;
    }
    setViewContent('updateForm');
    setIsLoading(true);
    navigate(`?service_id=${event.id}`);
    bookingService
      .getBookingById(event.parent_id)
      .then(({ data }) => {
        if (!Array.isArray(data) || !data.length) {
          throw new Error('The appointment detail response was empty.');
        }
        setServiceData(data);
        setSelectedSlots({
          start: data[data?.length - 1].start_date,
          end: data[data?.length - 1].end_date,
        });
        // Opening saved detail is a read, not a new booking quote. Keep its
        // persisted economics and support authorized unpaid local-client rows.
        setCalculatedData({
          status: true,
          items: data.map((item) => ({ ...item, booking_id: item.id })),
          total_price: data.reduce((total, item) => total + Number(item.total_price), 0),
          start_date: data[0].start_date,
        });
        infoForm.setFieldsValue({
          id: event.parent_id,
          shop: {
            label: data[0]?.shop?.translation?.title,
            value: data[0]?.shop?.id,
            key: data[0]?.shop?.id,
          },
          client: {
            label: data[0]?.local_client?.name || data[0]?.user?.full_name,
            value: data[0]?.user?.id,
            key: data[0]?.user?.id,
          },
          payment_id: data[0]?.transaction?.payment_system ? {
            label: data[0].transaction.payment_system.tag,
            value: data[0]?.transaction?.payment_system?.id,
            key: data[0]?.transaction?.payment_system?.id,
          } : undefined,
        });
      })
      .catch((error) => {
        console.log(error);
      })
      .finally(() => setIsLoading(false));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const eventStyleGetter = (event, start, end, isSelected) => {
    if (event.disabled)
      return {
        className: 'disabled-slot',
      };
    else return { className: event.status ? `status-${event.status}` : '' };
  };

  useEffect(() => {
    dispatch(fetchMasterDisabledTimes({ perPage: 100 }));
  }, [dispatch]);

  return (
    <ScheduleSurface
      localizer={localizer}
      events={[...bookingList, ...disabledTimes]}
      loading={loading}
      error={error}
      onRetry={() => dispatch(fetchMasterBookingList())}
      onSelectEvent={handleSelectEvent}
      onSelectSlot={showModal}
      eventPropGetter={eventStyleGetter}
      hourFormat={hourFormat}
    />
  );
};

export default CalendarView;
