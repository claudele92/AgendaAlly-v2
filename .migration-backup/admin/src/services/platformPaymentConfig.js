import request from './request';

const platformPaymentConfigService = {
  paginate: (params) =>
    request.get('dashboard/admin/platform-payment-configs/paginate', {
      params,
    }),
  getById: (id, params) =>
    request.get(`dashboard/admin/platform-payment-configs/${id}`, { params }),
  create: (data) =>
    request.post('dashboard/admin/platform-payment-configs', data),
  update: (id, data) =>
    request.put(`dashboard/admin/platform-payment-configs/${id}`, data),
  delete: (params) =>
    request.delete(`dashboard/admin/platform-payment-configs/delete`, {
      params,
    }),
};

export default platformPaymentConfigService;
