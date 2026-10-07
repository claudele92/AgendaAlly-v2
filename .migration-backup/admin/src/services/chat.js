import request from './request';

const chatService = {
  getUser: (params) => request.get(`dashboard/user/chat-users`, { params }),
  notifyNewMessage: (params) =>
    request('dashboard/user/notify/send', { params }),
};

export default chatService;
