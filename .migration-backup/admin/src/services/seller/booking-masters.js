import request from '../request';
import publicMastersService from '../rest/masters';

const url = 'dashboard/seller/booking-masters';
export default {
  getAll: (params) => request.get(url, { params }),
  getById: (id, params) => request.get(`${url}/${id}`, { params }),
  // Existing availability is a read-only, backend-authoritative contract.
  getTimes: publicMastersService.getTimes,
};