import React, { useEffect, useState } from 'react';
import { Card, DatePicker, Select, Space, Spin } from 'antd';
import { useDispatch, useSelector, shallowEqual } from 'react-redux';
import { useTranslation } from 'react-i18next';
import moment from 'moment';
import ChartWidget from 'components/chart-widget';
import {
  fetchChartBookingsReport,
  fetchPerformanceDashboardAdmin,
} from 'redux/slices/bookings-report';

const { RangePicker } = DatePicker;

// Booking revenue over time, for both the seller's own shop (via the
// seller booking-reports `chart` endpoint) and, platform-wide, for
// admin/superadmin (via ReportRepository::performanceDashboard's
// bookings_chart). The two endpoints return the revenue figure under a
// different key (total_price vs ended_price), so isSeller picks both the
// data source and the field to read.
const BookingsRevenueChart = ({ isSeller }) => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const [dateRange, setDateRange] = useState([
    moment().subtract(29, 'days'),
    moment(),
  ]);
  const [groupBy, setGroupBy] = useState('day');
  const { chartData, loading } = useSelector(
    (state) =>
      isSeller ? state.bookingsReport.seller : state.bookingsReport,
    shallowEqual,
  );

  useEffect(() => {
    if (!dateRange?.[0] || !dateRange?.[1]) return;
    const params = {
      date_from: dateRange[0].format('YYYY-MM-DD'),
      date_to: dateRange[1].format('YYYY-MM-DD'),
      type: groupBy,
    };
    dispatch(
      isSeller
        ? fetchChartBookingsReport(params)
        : fetchPerformanceDashboardAdmin(params),
    );
  }, [dateRange, groupBy, isSeller]);

  const priceKey = isSeller ? 'total_price' : 'ended_price';
  const categories = (chartData || []).map((item) => item.time);
  const series = [
    {
      name: t('total.revenue'),
      data: (chartData || []).map((item) => Number(item[priceKey]) || 0),
    },
  ];

  return (
    <Card
      title={t('revenue.over.time')}
      extra={
        <Space>
          <Select
            value={groupBy}
            onChange={setGroupBy}
            style={{ width: 110 }}
            options={[
              { label: t('day'), value: 'day' },
              { label: t('week'), value: 'week' },
              { label: t('month'), value: 'month' },
            ]}
          />
          <RangePicker
            value={dateRange}
            onChange={(value) => value && setDateRange(value)}
            allowClear={false}
          />
        </Space>
      }
    >
      <Spin spinning={loading}>
        <ChartWidget
          type='area'
          card={false}
          series={series}
          xAxis={categories}
          height={280}
        />
      </Spin>
    </Card>
  );
};

export default BookingsRevenueChart;
