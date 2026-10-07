import request from '../request';

const staffInviteService = {
  getAll: (params) =>
    request.get('dashboard/seller/shops/invites/paginate', { params }),
  getById: (id, params) =>
    request.get(`dashboard/seller/shops/invites/${id}`, { params }),
  searchUser: (query) =>
    request.get('dashboard/seller/shop/invitation/search-user', {
      params: { query },
    }),
  create: (data) => request.post('dashboard/seller/shop/invitation/link', data),
  update: (id, data) =>
    request.put(`dashboard/seller/shops/invites/${id}`, data),
  changeStatus: (id, status) =>
    request.post(`dashboard/seller/shops/invites/${id}/status/change`, {
      status,
    }),
  delete: (params) =>
    request.delete('dashboard/seller/shop/invitations/delete', { params }),
};

export default staffInviteService;
