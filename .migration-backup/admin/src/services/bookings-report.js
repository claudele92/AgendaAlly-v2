import request from './request';

const url = 'dashboard/admin/booking/reports';

const bookingsReportService = {
  getAllStatisticsBookings: (params) =>
    request.get(`${url}/statistic`, { params }),
  getPerformanceDashboard: (params) =>
    request.get('dashboard/admin/report/performance-dashboard', { params }),
};

export default bookingsReportService;
