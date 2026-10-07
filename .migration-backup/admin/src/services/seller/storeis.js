import request from '../request';

const storeisService = {
  getAll: (params) => request.get('dashboard/seller/stories', { params }),
  getById: (id, params) =>
    request.get(`dashboard/seller/stories/${id}`, { params }),
  create: (body) => request.post('dashboard/seller/stories', body),
  upload: (body) => request.post('dashboard/seller/stories/upload', body),
  update: (id, body) => request.put(`dashboard/seller/stories/${id}`, body),
  delete: (params) =>
    request.delete(`dashboard/seller/stories/delete`, { params }),
  setStatus: (id, data) =>
    request.post(`dashboard/admin/tickets/${id}/status`, data),
  getStatus: (params) =>
    request.get(`dashboard/admin/tickets/statuses`, { params }),
};

export default storeisService;
