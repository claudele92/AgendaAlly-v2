import request from '../request';

const url = 'rest/settings';

const restSettingsService = {
  getAll: (params) => request.get(url, { params }),
};

export default restSettingsService;
