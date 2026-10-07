import request from '../request';

const pickupPolicyService = {
  get: (shopId, locationId) =>
    request.get(`dashboard/shops/${shopId}/pickup-policies/${locationId}`),
  update: (shopId, locationId, body) =>
    request.put(`dashboard/shops/${shopId}/pickup-policies/${locationId}`, body),
  reset: (shopId, locationId) =>
    request.delete(`dashboard/shops/${shopId}/pickup-policies/${locationId}`),
};

export default pickupPolicyService;