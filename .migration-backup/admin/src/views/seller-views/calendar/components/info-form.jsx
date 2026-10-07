import React, { useContext, useEffect, useState } from 'react';
import { Alert, Form } from 'antd';
import { BookingContext } from '../provider';
import InfoFormItems from '../forms/info-form';
import bookingService from 'services/seller/booking';
import shopLocationService from 'services/seller/shop-locations';
import moment from 'moment';
import { useDispatch } from 'react-redux';
import { fetchSellerBookingList } from 'redux/slices/booking';
import { clientReference } from '../helpers/client-reference.mjs';

const InfoForm = () => {
  const {
    infoForm,
    setCalculatedData,
    selectedSlots,
    calculatedData,
    setViewContent,
  } = useContext(BookingContext);
  const dispatch = useDispatch();
  const client = Form.useWatch('client', infoForm);
  const isRegisteredClient = client?.value?.startsWith('registered:');
  const payment_id = Form.useWatch('payment_id', infoForm);
  const shop_location_id = Form.useWatch('shop_location_id', infoForm);
  const [shopLocations, setShopLocations] = useState([]);
  const [shopLocationsError, setShopLocationsError] = useState('');
  const [createError, setCreateError] = useState('');

  useEffect(() => {
    shopLocationService
      .getAll({ perPage: 100 })
      .then(({ data }) => {
        if (!Array.isArray(data)) {
          throw new Error('The branch location response did not contain a list.');
        }
        setShopLocations(data);
        const serviceLocations = data.filter((location) => location.type === 2);
        if (serviceLocations.length === 1) {
          infoForm.setFieldsValue({
            shop_location_id: serviceLocations[0].id,
          });
        }
        setShopLocationsError('');
      })
      .catch((error) => {
        setShopLocations([]);
        setShopLocationsError(
          error?.response?.data?.message ||
            error?.message ||
            'Unable to load branch locations.',
        );
      });
  }, []);

  const onFinish = (values) => {
    setCreateError('');
    bookingService
      .create({
        ...clientReference(values.client),
        payment_id: isRegisteredClient ? values.payment_id?.value : undefined,
        data: calculatedData?.items.map((item) => ({
          note: item.note,
          data: item.data,
          service_master_id: item?.service_master?.id,
          shop_location_id: values.shop_location_id,
          service_extras: item?.extras?.length
            ? item?.extras?.map((item) => item?.id)
            : undefined,
          start_date: item.start_date,
        })),
        start_date: moment(selectedSlots?.start).format('YYYY-MM-DD HH:mm'),
      })
      .then(() => {
        setViewContent('');
        setCalculatedData({});
        infoForm.resetFields();
        dispatch(fetchSellerBookingList());
      })
      .catch((error) => {
        setCreateError(
          error?.response?.data?.message ||
          'The booking was not created. Check the selected time and try again.',
        );
      });
  };

  const needsBranch = shopLocations.filter((l) => l.type === 2).length > 1;

  return (
    <Form form={infoForm} layout='vertical' onFinish={onFinish}>
      {createError && <Alert className='mb-3' type='error' showIcon message={createError} />}
      {shopLocationsError && (
        <Alert
          className='mb-3'
          type='error'
          showIcon
          message={shopLocationsError}
        />
      )}
      <InfoFormItems
        isAdd
        isDisabled={
          !(client && (isRegisteredClient ? payment_id : true)) ||
          (needsBranch && !shop_location_id) ||
          Boolean(shopLocationsError)
        }
        shopLocations={shopLocations}
      />
    </Form>
  );
};

export default InfoForm;
