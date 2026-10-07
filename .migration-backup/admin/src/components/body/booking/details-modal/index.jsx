import { Row, Col, Button } from 'antd';
import MasterDescriptions from './master';
import ClientDescriptions from './client';
import ShopDescription from './shop';
import TransactionDescriptions from './transaction';
import { useTranslation } from 'react-i18next';
import { useState } from 'react';
import { downloadInvoice } from 'views/calendar/helpers';
import './responsive.css';

const BookingDetailsBody = ({ data }) => {
  const { t } = useTranslation();

  const [downloadInvoiceModal, setDownloadInvoiceModal] = useState(false);

  const handleDownloadInvoice = async () => {
    setDownloadInvoiceModal(true);
    await downloadInvoice(data?.id).finally(() => {
      setDownloadInvoiceModal(false);
    });
  };

  return (
    <Row
      className='aa-booking-details-grid'
      gutter={24}
      style={{
        display: 'flex',
        rowGap: '24px',
        flexWrap: 'wrap',
      }}
    >
      <Col span={24}>
        <ClientDescriptions userExtraData={data?.data} data={data?.user} />
      </Col>
      <Col span={12}>
        <MasterDescriptions data={data?.master} />
      </Col>
      <Col span={12}>
        <ShopDescription data={data?.shop} />
      </Col>
      <Col span={24}>
        <TransactionDescriptions data={data?.transactions} />
      </Col>
      <Col span={24}>
        {!!data?.id && (
          <Button
            loading={downloadInvoiceModal}
            onClick={handleDownloadInvoice}
            htmlType='button'
          >
            {t('download.invoice')}
          </Button>
        )}
      </Col>
    </Row>
  );
};

export default BookingDetailsBody;
