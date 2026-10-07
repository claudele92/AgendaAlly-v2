import request from './request';

const root = 'dashboard/manual-finance';
const manualFinanceService = {
  capabilities: () => request.get(`${root}/capabilities`),
  sources: (kind) => request.get(`${root}/sources`, { params: { kind } }),
  list: (params = {}) => request.get(root, { params }),
  detail: (id) => request.get(`${root}/${id}`),
  action: (id, action, payload) =>
    request.post(`${root}/${id}/${action}`, payload),
  create: (payload) => request.post(root, payload),
  upload: (id, file) => {
    const body = new FormData();
    body.append('file', file);
    return request.post(`${root}/${id}/attachments`, body, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
  },
  attachmentLink: (id, attachmentId) =>
    request.get(`${root}/${id}/attachments/${attachmentId}/link`),
};

export default manualFinanceService;
