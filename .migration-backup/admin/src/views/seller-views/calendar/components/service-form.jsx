import React, { useContext, useState } from 'react';
import { Form, Typography } from 'antd';
import { defaultCenter } from 'configs/app-global';
import { BookingContext } from '../provider';
import servicesService from 'services/seller/services';
import mastersService from 'services/seller/booking-masters';
import ServiceFormItems from '../forms/service-form';
import bookingService from 'services/seller/booking';
import moment from 'moment';
import { useSelector } from 'react-redux';
import { useNavigationScope } from 'context/navigation-scope';
import { getVerifiedCalendarShopId } from 'views/calendar/helpers/calendar-context.mjs';
import { clientReference } from '../helpers/client-reference.mjs';

const hasId = (id) => id !== null && id !== undefined && id !== '';

const ServiceForm = () => {
  const [form] = Form.useForm();
  const {
    infoData,
    selectedSlots,
    setViewContent,
    calculatedData,
    setCalculatedData,
  } = useContext(BookingContext);
  const { defaultLang } = useSelector((state) => state.formLang);
  const [selectedService, setSelectedService] = useState(null);
  const [selectedMaster, setSelectedMaster] = useState(null);
  const [location, setLocation] = useState(defaultCenter);
  const [value, setValue] = useState('');
  const [masterTimes, setMasterTimes] = useState([]);
  const { user } = useSelector((state) => state.auth);
  const navigationScope = useNavigationScope();
  const shopId = getVerifiedCalendarShopId(user, navigationScope);
  const shop = hasId(shopId) ? { id: shopId } : null;
  const getServiceByID = (data) => {
    setSelectedService(null);
    setSelectedMaster(null);
    setMasterTimes([]);
    if (!hasId(data?.value)) {
      return;
    }
    servicesService
      .getById(data.value)
      .then((res) => {
        setSelectedService(res.data);
      })
      .catch((error) => {
        console.log(error);
      });
  };
  const getMasterByID = (data) => {
    setSelectedMaster(null);
    setMasterTimes([]);
    if (!hasId(data?.value) || !hasId(selectedService?.id)) {
      return;
    }
    mastersService
      .getById(data.value)
      .then(({ data }) => {
        const service_master = data?.service_masters?.find(
          (item) =>
            item.service_id === selectedService.id &&
            item.shop_id === shop?.id &&
            item.active,
        );
        setSelectedMaster({ ...data, service_master });
        if (!hasId(service_master?.id) || !selectedSlots?.start) {
          return;
        }
        return mastersService
          .getTimes({
            service_master_ids: [service_master.id],
            start_date: moment(selectedSlots?.start).format('YYYY-MM-DD HH:mm'),
          })
          .then(({ data }) => {
            setMasterTimes(
              Object.fromEntries(
                data?.[0]?.times?.map((item) => [item.date, item.times]) || [],
              ),
            );
          });
      })
      .catch((error) => {
        console.log(error);
      });
  };
  const calculate = (data) => {
    return bookingService.calculate({
      ...clientReference(infoData.client),
      payment_id: infoData.payment_id?.value,
      data: data.map((item) => ({
        note: item.note,
        data: item.data,
        service_master_id: item.service_master?.id,
        service_extras: item?.service_extras,
        start_date: item.start_date,
        shop_location_id: item.shop_location_id || infoData.shop_location_id,
      })),
      // start_date: moment(selectedSlots?.start).format('YYYY-MM-DD HH:mm'),
    });
  };
  const onFinish = (values) => {
    const invalidFields = [];
    const serviceErrors = [];
    if (!hasId(selectedService?.id) || !selectedService?.translation?.title) {
      serviceErrors.push('Select a valid service before continuing.');
    }
    if (!hasId(selectedMaster?.service_master?.id)) {
      invalidFields.push({
        name: 'master',
        errors: ['Select a master with this service assigned.'],
      });
    }
    if (!selectedSlots?.start) {
      invalidFields.push({
        name: 'start_date_date',
        errors: ['Select a Calendar time slot before continuing.'],
      });
    }
    const isRegisteredClient =
      infoData?.client?.value?.startsWith('registered:');
    if (
      !hasId(infoData?.client?.value) ||
      (isRegisteredClient && !hasId(infoData?.payment_id?.value))
    ) {
      serviceErrors.push('Select a client and payment method before continuing.');
    }
    if (serviceErrors.length) {
      invalidFields.push({ name: 'service', errors: serviceErrors });
    }
    if (invalidFields.length) {
      form.setFields(invalidFields);
      return;
    }
    let prevServices = [];
    if (calculatedData?.items)
      prevServices = calculatedData?.items?.map((item) => ({
        ...item,
        service_extras: item?.extras?.length
          ? item?.extras?.map((extra) => extra?.id)
          : undefined,
      }));
    const newServiceData = {
      note: values.note,
      data: { address: values[`address[${defaultLang}]`], ...location },
      service_master: selectedMaster?.service_master,
      service_extras: values?.extras?.length
        ? values?.extras?.map((item) => item?.value)
        : undefined,
      start_date: `${values.start_date_date.format('YYYY-MM-DD')} ${values.start_date_time}`,
      shop_location_id: infoData.shop_location_id,
    };
    calculate([...prevServices, newServiceData])
      .then(({ data }) => {
        form.resetFields();
        // A calculation is only a draft, not a persisted calendar appointment.
        setCalculatedData(data);
        setViewContent('addService');
      })
      .catch((error) => {
        console.log(error);
      });
  };

  return (
    <Form layout='vertical' form={form} onFinish={onFinish}>
      {infoData?.client?.value?.startsWith('local:') && (
        <Typography.Paragraph type='secondary' className='agendaally-quiet-payment-note'>
          Local-client bookings remain unpaid until a supported collection is recorded.
        </Typography.Paragraph>
      )}
      <ServiceFormItems
        shop={shop}
        form={form}
        setOpen={setViewContent}
        location={location}
        setLocation={setLocation}
        getMasterByID={getMasterByID}
        getServiceByID={getServiceByID}
        selectedService={selectedService}
        value={value}
        setValue={setValue}
        defaultLang={defaultLang}
        masterTimes={masterTimes}
        shopLocationId={infoData.shop_location_id}
        serviceLookupStatus={navigationScope.status}
      />
    </Form>
  );
};

export default ServiceForm;
