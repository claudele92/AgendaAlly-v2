import React, { useMemo, useRef, useState } from 'react';
import { Calendar } from 'react-big-calendar';
import './schedule-surface.css';

const dateValue = (date) => {
  const value = new Date(date);
  return Number.isNaN(value.getTime())
    ? ''
    : `${value.getFullYear()}-${String(value.getMonth() + 1).padStart(2, '0')}-${String(value.getDate()).padStart(2, '0')}`;
};

const labelForStatus = (event) => {
  if (event.disabled) return 'Unavailable time';
  return String(event.status || event.type || 'Appointment').replace(/[_-]/g, ' ');
};

const eventTitle = (event) =>
  event.resource?.service ||
  event.service ||
  event.title ||
  (event.disabled ? 'Blocked time' : 'Appointment');

const ScheduleGridEvent = ({ event }) => {
  const durationMinutes =
    event.end && event.start
      ? (new Date(event.end).getTime() - new Date(event.start).getTime()) / 60000
      : 0;
  const hasRoomForDetails = durationMinutes >= 60 && !event.disabled;
  return (
    <div className={`aa-schedule__grid-event${event.disabled ? ' is-blocked' : ''}`}>
      <strong>{eventTitle(event)}</strong>
      {hasRoomForDetails && (
        <span className="aa-schedule__grid-event-details">
          {[event.resource?.client, event.resource?.master, labelForStatus(event)]
            .filter(Boolean)
            .join(' · ')}
        </span>
      )}
      {event.disabled && <span>Unavailable</span>}
    </div>
  );
};

const ScheduleSurface = ({
  localizer,
  events = [],
  loading = false,
  onSelectEvent,
  onSelectSlot,
  eventPropGetter,
  hourFormat,
  error,
  onRetry,
  workspaceLabel = 'Schedule workspace',
}) => {
  const [date, setDate] = useState(() => new Date());
  const [view, setView] = useState(() =>
    typeof window !== 'undefined' && window.matchMedia('(max-width: 700px)').matches
      ? 'agenda'
      : 'week',
  );
  const [mobileWeek, setMobileWeek] = useState(false);
  const [search, setSearch] = useState('');
  const [bookingPrompt, setBookingPrompt] = useState(false);
  const ordered = useMemo(
    () => [...events].sort((a, b) => new Date(a.start) - new Date(b.start)),
    [events],
  );
  const visibleEvents = useMemo(() => {
    const term = search.trim().toLocaleLowerCase();
    if (!term) return ordered;
    return ordered.filter((event) =>
      [
        event.title,
        event.resource?.service,
        event.resource?.client,
        event.resource?.master,
        event.service,
        event.subtitle,
        event.status,
      ]
        .filter(Boolean)
        .join(' ')
        .toLocaleLowerCase()
        .includes(term),
    );
  }, [ordered, search]);
  const selectedDay = visibleEvents.filter(
    (event) => new Date(event.start).toDateString() === date.toDateString(),
  );
  const shownEvents =
    view === 'week'
      ? visibleEvents.filter((event) => {
          const start = new Date(date);
          const weekday = start.getDay();
          start.setDate(start.getDate() - weekday);
          start.setHours(0, 0, 0, 0);
          const end = new Date(start);
          end.setDate(end.getDate() + 7);
          const eventDate = new Date(event.start);
          return eventDate >= start && eventDate < end;
        })
      : view === 'agenda'
        ? visibleEvents
        : selectedDay;

  const shiftDate = (amount) => {
    const next = new Date(date);
    next.setDate(next.getDate() + amount * (view === 'week' ? 7 : 1));
    setDate(next);
  };
  const changeDate = (value) => {
    if (!value) return;
    const [year, month, day] = value.split('-').map(Number);
    setDate(new Date(year, month - 1, day));
  };
  const moreMenu = useRef(null);
  const selectView = (next) => {
    if (moreMenu.current) moreMenu.current.open = false;
    setView(next);
    setMobileWeek(next === 'week');
    setBookingPrompt(false);
  };
  const startBookingJourney = () => {
    setView('week');
    setMobileWeek(true);
    setBookingPrompt(true);
  };
  const onCalendarNavigate = (nextDate) => setDate(nextDate);
  const eventDetail = (event) =>
    [event.resource?.client, event.resource?.master]
      .filter(Boolean)
      .join(' · ');
  const agendaGroups = useMemo(() => {
    if (view !== 'agenda') return [];
    const groups = new Map();
    shownEvents.forEach((event) => {
      const key = dateValue(event.start);
      if (!groups.has(key)) groups.set(key, []);
      groups.get(key).push(event);
    });
    return [...groups.entries()];
  }, [shownEvents, view]);
  const renderEventCards = (items) => items.map((event, index) => (
    <button
      type="button"
      className={`aa-schedule__event${event.disabled ? ' is-blocked' : ''}`}
      key={`${event.id ?? event.parent_id ?? 'event'}-${index}`}
      onClick={() => onSelectEvent?.(event)}
      data-testid={`schedule-event-${event.id ?? index}`}
    >
      <span className="aa-schedule__event-time">
        {localizer.format(new Date(event.start), hourFormat)}
        {event.end ? ` – ${localizer.format(new Date(event.end), hourFormat)}` : ''}
      </span>
      <span className="aa-schedule__event-main">
        <strong>{eventTitle(event)}</strong>
        {eventDetail(event) && <span>{eventDetail(event)}</span>}
      </span>
      <span className={`aa-schedule__status${event.disabled ? ' is-blocked' : ''}`}>{labelForStatus(event)}</span>
    </button>
  ));

  return (
    <section className="aa-schedule" aria-label="Appointment schedule">
      <div className="aa-schedule__toolbar">
        <div className="aa-schedule__date-row">
          <div className="aa-schedule__date-controls">
            <button type="button" className="aa-schedule__button aa-schedule__icon-button" onClick={() => shiftDate(-1)} aria-label="Previous date">
              <span aria-hidden="true">‹</span>
            </button>
            <button type="button" className="aa-schedule__button aa-schedule__today" onClick={() => setDate(new Date())}>Today</button>
            <button type="button" className="aa-schedule__button aa-schedule__icon-button" onClick={() => shiftDate(1)} aria-label="Next date">
              <span aria-hidden="true">›</span>
            </button>
            <label className="aa-schedule__date-label">
              <span className="aa-schedule__sr-only">Choose schedule date</span>
              <input type="date" value={dateValue(date)} onChange={(event) => changeDate(event.target.value)} />
            </label>
          </div>
          <div className="aa-schedule__view-switch" role="group" aria-label="Schedule view">
            {['week', 'day', 'agenda'].map((item) => (
              <button
                type="button"
                key={item}
                aria-pressed={view === item}
                className={view === item ? 'aa-schedule__view is-active' : 'aa-schedule__view'}
                onClick={() => selectView(item)}
              >
                {item[0].toUpperCase() + item.slice(1)}
              </button>
            ))}
          </div>
          <div className="aa-schedule__mobile-modes">
            <button type="button" className={view === 'agenda' ? 'aa-schedule__mode is-active' : 'aa-schedule__mode'} aria-pressed={view === 'agenda'} onClick={() => selectView('agenda')}>Agenda</button>
            <button type="button" className={view === 'day' ? 'aa-schedule__mode is-active' : 'aa-schedule__mode'} aria-pressed={view === 'day'} onClick={() => selectView('day')}>Day</button>
            <details className="aa-schedule__more" ref={moreMenu}>
              <summary>More</summary>
              <button type="button" aria-pressed={view === 'week'} onClick={() => selectView('week')}>Week view</button>
            </details>
          </div>
          <button type="button" className="aa-schedule__add-booking" onClick={startBookingJourney}>
            Add booking
          </button>
        </div>
        <div className="aa-schedule__tools">
          <label className="aa-schedule__search">
            <span aria-hidden="true">⌕</span>
            <span className="aa-schedule__sr-only">Search appointments</span>
            <input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Search appointments" />
          </label>
          <span className="aa-schedule__context">{workspaceLabel}</span>
        </div>
      </div>

      <div className="aa-schedule__heading-row">
        <p className="aa-schedule__heading" aria-live="polite">
          {date.toLocaleDateString(undefined, {
            ...(view === 'week' ? { month: 'long', year: 'numeric' } : { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }),
          })}
        </p>
        <span className="aa-schedule__count">{shownEvents.length} {shownEvents.length === 1 ? 'appointment' : 'appointments'}</span>
      </div>

      {loading ? (
        <div className="aa-schedule__loading" role="status" aria-label="Loading appointments">
          <span /><span /><span />
        </div>
      ) : error ? (
        <div className="aa-schedule__error" role="alert">
          <strong>Appointments could not be loaded</strong>
          <span>{error}</span>
          {onRetry && <button type="button" onClick={onRetry}>Try again</button>}
        </div>
      ) : (
        <>
          {(view === 'day' ? selectedDay : shownEvents).length === 0 && (
            <div className="aa-schedule__empty" role="status">
              <span className="aa-schedule__empty-mark" aria-hidden="true">—</span>
              <strong>{search ? 'No matching appointments' : 'Nothing scheduled here yet'}</strong>
              <span>{search ? 'Try a different name or service.' : 'Choose another date or add a booking in an available time.'}</span>
            </div>
          )}
          {view === 'week' && onSelectSlot && (
            <div className="aa-schedule__hint" role={bookingPrompt ? 'status' : undefined}>
              {bookingPrompt && <strong>Choose an open time to begin. No time is selected for you.</strong>}
              <span>Select an open time to start a booking. Blocked periods are shown separately.</span>
              {bookingPrompt && <button type="button" onClick={() => selectView('agenda')}>Back to Agenda</button>}
            </div>
          )}
          <div className={`aa-schedule__grid${view === 'week' ? ' is-week' : ''}${mobileWeek ? ' is-mobile-week' : ''}`} hidden={view === 'agenda' || (typeof window !== 'undefined' && window.matchMedia('(max-width: 700px)').matches && view !== 'week' && view !== 'day')}>
            <Calendar
              localizer={localizer}
              date={date}
              onNavigate={onCalendarNavigate}
              view={view === 'agenda' ? 'day' : view}
              onView={(nextView) => setView(nextView)}
              views={['day', 'week']}
              startAccessor="start"
              endAccessor="end"
              events={visibleEvents}
              onSelectEvent={onSelectEvent}
              onSelectSlot={onSelectSlot}
              selectable
              timeslots={2}
              step={30}
              scrollToTime={new Date(date.getFullYear(), date.getMonth(), date.getDate(), 8)}
              eventPropGetter={eventPropGetter}
              components={{ event: ScheduleGridEvent }}
              formats={{
                timeGutterFormat: hourFormat,
                eventTimeRangeFormat: ({ start, end }) =>
                  `${localizer.format(start, hourFormat)} – ${localizer.format(end, hourFormat)}`,
              }}
            />
          </div>
          <div className={`aa-schedule__agenda${view === 'agenda' || (view === 'day' && typeof window !== 'undefined' && window.matchMedia('(max-width: 700px)').matches) ? ' is-selected' : ''}`} aria-label="Appointments for selected date">
            {view === 'agenda'
              ? agendaGroups.map(([day, items]) => (
                  <section className="aa-schedule__agenda-group" key={day}>
                    <h2>{new Date(`${day}T12:00:00`).toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric' })}</h2>
                    {renderEventCards(items)}
                  </section>
                ))
              : renderEventCards(selectedDay)}
          </div>
          {(view === 'agenda' || view === 'day') && (
            <div className="aa-schedule__mobile-add">
              <button type="button" onClick={startBookingJourney}>
                Add booking
              </button>
            </div>
          )}
        </>
      )}
    </section>
  );
};

export default ScheduleSurface;