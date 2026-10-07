import request from './request';

const countryAdminService = {
  paginate: (params) => request.get('dashboard/admin/country-admins', { params }),
  searchUser: (params) =>
    request.get('dashboard/admin/country-admins/search-user', { params }),
  create: (data) => request.post('dashboard/admin/country-admins', data),
  delete: (id) => request.delete(`dashboard/admin/country-admins/${id}`),
};

export default countryAdminService;
