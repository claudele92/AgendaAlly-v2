import request from '../request';

const shopRoleService = {
  getAll: (params) => request.get('dashboard/seller/roles/paginate', { params }),
  getById: (id, params) => request.get(`dashboard/seller/roles/${id}`, { params }),
  getPermissions: () => request.get('dashboard/seller/roles/permissions'),
  create: (data) => request.post('dashboard/seller/roles', data),
  update: (id, data) => request.put(`dashboard/seller/roles/${id}`, data),
  delete: (id) => request.delete(`dashboard/seller/roles/${id}`),
};

export default shopRoleService;
