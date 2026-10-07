import request from './request';

const countryRoleService = {
  getAll: (params) =>
    request.get('dashboard/admin/country-roles/paginate', { params }),
  getById: (id, params) =>
    request.get(`dashboard/admin/country-roles/${id}`, { params }),
  getPermissions: () =>
    request.get('dashboard/admin/country-roles/permissions'),
  create: (data) => request.post('dashboard/admin/country-roles', data),
  update: (id, data) =>
    request.put(`dashboard/admin/country-roles/${id}`, data),
  delete: (id, params) =>
    request.delete(`dashboard/admin/country-roles/${id}`, { params }),
};

export default countryRoleService;
