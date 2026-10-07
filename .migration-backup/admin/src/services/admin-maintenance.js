import request from './request';

const adminMaintenanceService = {
  systemInformation: (params) =>
    request.get('dashboard/admin/settings/system/information', { params }),
  backupHistory: (params) =>
    request.post('dashboard/admin/backup/history', {}, { params }),
  getBackupHistory: (params) =>
    request.get('dashboard/admin/backup/history', { params }),
};

export default adminMaintenanceService;