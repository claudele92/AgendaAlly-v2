import request from '../request';

export const aiTranslationService = {
  translate: (data) => request.post('dashboard/seller/ai-translations', data),
  chartReport: (params) =>
    request.get('dashboard/seller/ai-translation/chart', { params }),
  listReport: (params) =>
    request.get('dashboard/seller/ai-translations', { params }),
};
