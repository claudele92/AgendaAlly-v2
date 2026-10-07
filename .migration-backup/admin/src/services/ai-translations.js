import request from './request';

export const aiTranslationService = {
  translate: (data) => request.post('dashboard/admin/ai-translations', data),
  chartReport: (params) =>
    request.get('dashboard/admin/ai-translation/chart', { params }),
  listReport: (params) =>
    request.get('dashboard/admin/ai-translations', { params }),
};
