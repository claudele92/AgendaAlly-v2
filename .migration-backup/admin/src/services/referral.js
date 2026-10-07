import request from './request';

const referralService = {
  get: (params) => request.get('dashboard/admin/referrals', { params }),
  update: (body) => request.post(`dashboard/admin/referrals`, body),
};

export default referralService;
