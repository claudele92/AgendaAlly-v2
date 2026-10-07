import React, { useContext } from 'react';
import { BookingContext } from '../provider';
import numberToPrice from 'helpers/numberToPrice';
import { Button, Form } from 'antd';
import { t } from 'i18next';

const InfoFormFooter = () => {
  const {
    calculatedData,
    infoForm,
    setCalculatedData,
    sumInterval,
    setViewContent,
  } = useContext(BookingContext);
  const client = Form.useWatch('client', infoForm);
  const isLocalClient = client?.value?.startsWith('local:');
  const isReview = Boolean(calculatedData?.status);
  return (
    <div className='aa-booking-review-footer'>
      {isReview && (
        <>
          <div className='w-100 d-flex between my-2'>
            <strong className='font-size-5'>Calculated total</strong>
            <strong className='font-size-5'>
              {`${numberToPrice(calculatedData?.total_price)} (${sumInterval || 0}min)`}
            </strong>
          </div>
          {isLocalClient && (
            <section className='agendaally-review-payment' aria-label='Payment and collection state'>
              <span>Payment / collection state</span>
              <strong>UNPAID / UNCOLLECTED</strong>
              <p>No payment is taken when this booking is confirmed.</p>
            </section>
          )}
        </>
      )}
      <div className='w-100 d-flex gap-2'>
        <Button
          type='primary'
          className='w-100'
          onClick={infoForm.submit}
          disabled={!calculatedData?.status}
        >
          {isLocalClient && isReview ? 'Confirm unpaid booking' : t('checkout')}
        </Button>
        <Button
          type='danger'
          className='w-100'
          onClick={() => {
            setViewContent('');
            infoForm.resetFields();
            setCalculatedData({});
          }}
        >
          {t('cancel')}
        </Button>
      </div>
    </div>
  );
};

export default InfoFormFooter;
