import request from './request';

const basePath = 'dashboard/seller/delivery-driver-invitations';

const deliveryDriverInvitations = {
  list: (params) => request.get(basePath, { params }),
  create: (payload) => request.post(basePath, payload),
  resend: (id) => request.post(`${basePath}/${id}/resend`),
  revoke: (id) => request.post(`${basePath}/${id}/revoke`),
  setActive: (id, active) =>
    request.post(`${basePath}/${id}/status`, { active }),
  preview: (token) =>
    request.post('delivery-driver-invitations/preview', { token }),
  register: (payload) =>
    request.post('delivery-driver-invitations/register', payload),
  accept: (token) => request.post('delivery-driver-invitations/accept', { token }),
  decline: (token) =>
    request.post('delivery-driver-invitations/decline', { token }),
  verifyEmail: (hash) =>
    request.get(`auth/verify/${encodeURIComponent(hash)}`),
  resendVerification: (email) =>
    request.post('auth/resend-verify', { email }),
};

export default deliveryDriverInvitations;