import moment from 'moment';
import React, { useCallback, useContext, useEffect } from 'react';
import { momentLocalizer } from 'react-big-calendar';
import { useSelector } from 'react-redux';
import { BookingContext } from '../provider';
import { useNavigate } from 'react-router-dom';
import { fetchMasterDisabledTimesAsSeller } from 'redux/slices/disabledTimes';
import { fetchSellerBookingList } from 'redux/slices/booking';
import { useDispatch } from 'react-redux';
import { getHourFormat } from 'helpers/getHourFormat';
import ScheduleSurface from '../../../../components/scheduling/ScheduleSurface';
import { useNavigationScope } from 'context/navigation-scope';
import { getVerifiedCalendarScope } from 'views/calendar/helpers/calendar-context.mjs';

const CalendarView = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const localizer = momentLocalizer(moment); // or globalizeLocalizer
  const { bookingList, loading, error } = useSelector((state) => state.booking);
  const { user } = useSelector((state) => state.auth);
  const { disabledTimes } = useSelector((state) => state.disabledTimes);
  const navigationScope = useNavigationScope();
  const verifiedScope = getVerifiedCalendarScope(user, navigationScope);
  const shopLabel =
    verifiedScope?.shop?.translation?.title ||
    verifiedScope?.shop?.name ||
    'Authorized Shop schedule';
  const {
    setViewContent,
    setIsModalOpen,
    setSelectedSlots,
    setSavedBookingId,
  } = useContext(BookingContext);
  const hourFormat = getHourFormat();

  const showModal = ({ start, end }) => {
    setIsModalOpen(true);
    setSelectedSlots({ start, end });
  };
  const retryCalendar = () => {
    dispatch(fetchSellerBookingList());
    dispatch(fetchMasterDisabledTimesAsSeller({ perPage: 100 }));
  };

  const handleSelectEvent = useCallback((event) => {
    if (event.disabled) {
      setViewContent('updateBlockTime');
      navigate(`?disabled_slot_id=${event.id}`);
      return;
    }
    setSavedBookingId(event.id);
    setViewContent('savedDetails');
    navigate(`?booking_id=${event.id}`);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const eventStyleGetter = (event, start, end, isSelected) => {
    if (event.disabled) {
      return {
        className: 'disabled-slot',
      };
    }

    return {
      className: event.status ? `status-${event.status}` : '',
    };
  };

  useEffect(() => {
    dispatch(fetchMasterDisabledTimesAsSeller({ perPage: 100 }));
  }, [dispatch]);

  return (
    <ScheduleSurface
      localizer={localizer}
      events={[...bookingList, ...disabledTimes]}
      loading={loading}
      error={error}
      onRetry={retryCalendar}
      onSelectEvent={handleSelectEvent}
      onSelectSlot={showModal}
      eventPropGetter={eventStyleGetter}
      hourFormat={hourFormat}
      workspaceLabel={shopLabel}
    />
  );
};

export default CalendarView;
