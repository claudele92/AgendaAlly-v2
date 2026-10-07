import React, { useCallback, useState, useEffect, useRef } from 'react';
import {
  onMessageListener,
  pushMessagingEnabled,
  requestForToken,
} from '../firebase';
import NotificationSound from '../assets/audio/web_whatsapp.mp3';
import { Button, notification } from 'antd';
import { useTranslation } from 'react-i18next';
import { ShoppingCartOutlined } from '@ant-design/icons';
import { del, set } from 'idb-keyval';
import { useDispatch, useSelector } from 'react-redux';
import { addMenu } from '../redux/slices/menu';
import { useNavigate } from 'react-router-dom';

export default function PushNotification({ refetch }) {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const [data, setData] = useState({ title: '', body: '' });
  const audioPlayer = useRef(null);
  const { user } = useSelector((state) => state.auth);

  const reportNotificationError = useCallback((error) => {
    console.error('Push notification handling failed.', error);
    notification.error({
      message: 'Push notification handling failed',
      description: error?.message || String(error),
    });
  }, []);

  const playNotificationSound = () => {
    const playback = audioPlayer.current?.play();
    playback?.catch((error) => {
      console.warn('Notification sound playback was blocked.', error);
    });
  };

  const deleteItem = (id) => {
    if (id === null || id === undefined || id === '') return;
    del(id)
      .then(() => refetch())
      .catch(reportNotificationError);
  };

  const goToShow = (id) => {
    let menuUrl = '';
    let navigationUrl = '';
    if (user.role === 'seller' || user.role === 'moderator') {
      menuUrl = 'seller/';
      navigationUrl = '/seller';
    }
    const matchedId = id?.match(/\d+/)?.[0];
    if (!matchedId) {
      reportNotificationError(new Error('The order notification has no valid ID.'));
      return;
    }
    deleteItem(id);
    const actualId = parseInt(matchedId, 10);
    dispatch(
      addMenu({
        url: `${menuUrl}order/details/${actualId}`,
        id: 'order_details',
        name: t('order.details'),
      }),
    );
    navigate(`${navigationUrl}/order/details/${actualId}`);
  };

  const goToParcel = (id) => {
    let menuUrl = '';
    let navigationUrl = '';
    if (user.role === 'seller' || user.role === 'moderator') {
      menuUrl = 'seller/';
      navigationUrl = '/seller';
    }
    deleteItem(id);
    dispatch(
      addMenu({
        url: `${menuUrl}parcel-orders`,
        id: 'parcel_orders',
        name: t('parcel.orders'),
      }),
    );
    navigate(`${navigationUrl}/parcel-orders`);
  };

  const notifyOrder = () => {
    playNotificationSound();
    const key = `open${Date.now()}`;
    const btn = (
      <Button
        type='primary'
        size='small'
        onClick={() => {
          // notification.close(key);
          goToShow(data.title);
        }}
      >
        {t('view.order')}
      </Button>
    );
    notification.open({
      message: t('new.order'),
      description: `${t('order.id')} #${data.title}`,
      btn,
      key,
      // onClose: close,
      duration: 0,
      icon: (
        <ShoppingCartOutlined
          style={{
            color: '#3e79f7',
          }}
        />
      ),
    });
  };

  const notifyParcel = () => {
    playNotificationSound();
    const key = `open${Date.now()}`;
    const btn = (
      <Button
        type='primary'
        size='small'
        onClick={() => {
          // notification.close(key);
          goToParcel(data.title);
        }}
      >
        {t('view.parcel')}
      </Button>
    );
    notification.open({
      message: t('new.parcel'),
      description: `${t('parcel.id')} #${data.title}`,
      btn,
      key,
      // onClose: close,
      duration: 0,
      icon: (
        <ShoppingCartOutlined
          style={{
            color: '#3e79f7',
          }}
        />
      ),
    });
  };

  const notifyDefault = () => {
    playNotificationSound();
    notification.open({
      message: data.title || 'Notification',
      description: data.body || 'You have a new notification',
    });
  };

  useEffect(() => {
    if (data?.title) {
      switch (data.type) {
        case 'new_order':
          notifyOrder();
          break;
        case 'new_parcel_order':
          notifyParcel();
          break;
        default:
          notifyDefault();
          break;
      }
    }
  }, [data]);

  useEffect(() => {
    if (!pushMessagingEnabled) return undefined;

    requestForToken().catch(reportNotificationError);
    return onMessageListener((payload) => {
      const title = payload?.notification?.title;
      const body = payload?.notification?.body;
      setData({
        title,
        body,
        type: payload?.data?.type,
      });
      set(title, body).catch(reportNotificationError);
      Promise.resolve()
        .then(() => refetch())
        .catch(reportNotificationError);
    });
  }, [refetch, reportNotificationError]);

  return (
    <div className='notification'>
      <audio
        id='notification-audio'
        ref={audioPlayer}
        src={NotificationSound}
      />
    </div>
  );
}
