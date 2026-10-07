import { useTranslation } from 'react-i18next';
import { QRCodeCanvas } from 'qrcode.react';
import { Button } from 'antd';
import { WEBSITE_URL } from 'configs/app-global';

export default function QrCode({ orderId, showLink = true, size = 2 }) {
  const { t } = useTranslation();

  const orderUrl = new URL(
    `/orders/${encodeURIComponent(orderId)}`,
    WEBSITE_URL,
  ).toString();

  return (
    <>
      <h3>{t('qr_code')}:</h3>
      <div>
        <div
          style={{
            width: `${size * 100}px`,
            height: `${size * 100}px`,
            borderRadius: '10px',
            overflow: 'hidden',
          }}
        >
          <QRCodeCanvas
            size={500}
            id='qrCode'
            includeMargin
            value={orderUrl}
            style={{
              width: '100%',
              aspectRatio: '1/1',
              height: '100%',
            }}
            bgColor='#fff'
            level='H'
          />
        </div>
        <br />
        {showLink && (
          <Button
            type='primary'
            href={orderUrl}
            target='_blank'
            rel='noreferrer'
            style={{ width: `${size * 100}px` }}
          >
            {t('link.to.order')}
          </Button>
        )}
      </div>
    </>
  );
}
