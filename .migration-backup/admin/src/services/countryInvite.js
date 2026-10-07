import request from './request';

const countryInviteService = {
  getAll: (params) =>
    request.get('dashboard/admin/country-invites/paginate', { params }),
  searchUser: (query, params) =>
    request.get('dashboard/admin/country-invites/search-user', {
      params: { ...params, query },
    }),
  create: (data) => request.post('dashboard/admin/country-invites', data),
  createAccount: (data) =>
    request.post('dashboard/admin/country-invites/create-account', data),
  changeStatus: (id, status, params) =>
    request.post(
      `dashboard/admin/country-invites/${id}/status/change`,
      { status },
      { params },
    ),
  delete: (params) =>
    request.delete('dashboard/admin/country-invites/delete', { params }),
};

export default countryInviteService;
