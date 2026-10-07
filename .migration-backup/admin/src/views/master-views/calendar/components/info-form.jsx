import React, { useContext, useEffect, useState } from 'react';
import { Alert, Form } from 'antd';
import { BookingContext } from '../provider';
import InfoFormItems from '../forms/info-form';
import bookingService from 'services/master/booking';
import shopLocationService from 'services/master/shop-locations';
import moment from 'moment';
import { useDispatch } from 'react-redux';
import { fetchMasterBookingList } from 'redux/slices/booking';

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
  const payment_id = Form.useWatch('payment_id', infoForm);
  const shop_location_id = Form.useWatch('shop_location_id', infoForm);
  const [shopLocations, setShopLocations] = useState([]);
  const [shopLocationsError, setShopLocationsError] = useState('');

  useEffect(() => {
    shopLocationService
      .getAll({ perPage: 100 })
      .then(({ data }) => {
        if (!Array.isArray(data)) {
          throw new Error('The branch location response did not contain a list.');
        }
        setShopLocations(data);
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
    bookingService
      .create({
        user_id: values.client.value,
        payment_id: values.payment_id.value,
        data: calculatedData?.items.map((item) => ({
          note: item.note,
          data: item.data,
          service_master_id: item?.service_master?.id,
          shop_location_id: values.shop_location_id,
        })),
        start_date: moment(selectedSlots?.start).format('YYYY-MM-DD HH:mm'),
      })
      .then(() => {
        setViewContent('');
        setCalculatedData({});
        infoForm.resetFields();
        dispatch(fetchMasterBookingList());
      })
      .catch((error) => {
        console.error(error);
      });
  };

  const needsBranch = shopLocations.filter((l) => l.type === 2).length > 1;

  return (
    <Form form={infoForm} layout='vertical' onFinish={onFinish}>
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
          !(client && payment_id) ||
          (needsBranch && !shop_location_id) ||
          Boolean(shopLocationsError)
        }
        shopLocations={shopLocations}
      />
    </Form>
  );
};

export default InfoForm;
