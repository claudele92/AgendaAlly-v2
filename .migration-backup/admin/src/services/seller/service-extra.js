import request from '../request';

const url = 'dashboard/seller/service-extras';

const sellerServiceExtraService = {
  create: (data) => request.post(url, data),
  getAll: (params) => request.get(url, { params }),
  delete: (params) => request.delete(`${url}/delete`, { params }),
  getById: (id) => request.get(`${url}/${id}`),
  update: (id, data) => request.put(`${url}/${id}`, data),
};

export default sellerServiceExtraService;
