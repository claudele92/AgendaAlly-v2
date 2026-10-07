import React, { useContext } from 'react';
import { Form, Input, Spin } from 'antd';
import { BookingContext } from '../provider';
import UpdateFormItems from '../forms/info-form';
import bookingService from 'services/seller/booking';
import { clientReference } from '../helpers/client-reference.mjs';

const UpdateForm = () => {
  const {
    infoForm,
    setCalculatedData,
    setIsUpdate,
    setOpenService,
    isLoalding,
    calculatedData,
  } = useContext(BookingContext);

  const client = Form.useWatch('client', infoForm);
  const payment_id = Form.useWatch('payment_id', infoForm);
  const isRegisteredClient = client?.value?.startsWith('registered:');

  const onFinish = (values) => {
    const body = {
      ...clientReference(values.client),
      payment_id: isRegisteredClient ? values.payment_id?.value : undefined,
      data: calculatedData?.items.map((item) => ({
        note: item.note,
        data: item.data,
        service_master_id: item.service_master?.id,
        shop_location_id: item.shop_location_id,
      })),
      start_date: calculatedData?.start_date,
    };
    bookingService
      .update(values.id, body)
      .then(() => {
        setIsUpdate(false);
        infoForm.resetFields();
        setCalculatedData({});
      })
      .catch((error) => {
        console.error(error);
      });
  };

  return (
    <Spin spinning={isLoalding}>
      <Form form={infoForm} layout='vertical' onFinish={onFinish}>
        <Form.Item name='id' className='d-none'>
          <Input />
        </Form.Item>
        <UpdateFormItems
          title='update.booking'
          setOpenService={setOpenService}
          isDisabled={!(client && (isRegisteredClient ? payment_id : true))}
        />
      </Form>
    </Spin>
  );
};

export default UpdateForm;
