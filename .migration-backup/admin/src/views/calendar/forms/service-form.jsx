import React, { useContext } from 'react';
import {
  Button,
  Col,
  DatePicker,
  Form,
  Input,
  Row,
  Select,
  Tag,
  Typography,
} from 'antd';
import { t } from 'i18next';
import { AsyncSelect } from 'components/async-select';
import { ArrowLeftOutlined } from '@ant-design/icons';
import Map from 'components/map';
import { fetchMasterList, fetchService } from '../helpers';
import AddressForm from 'components/forms/address-form';
import { BookingContext } from '../provider';
import moment from 'moment/moment';
import { getHourFormat } from 'helpers/getHourFormat';
const { Title } = Typography;

const ServiceFormItems = ({
  shop,
  setOpen,
  getServiceByID,
  getMasterByID,
  selectedService,
  form,
  location,
  setLocation,
  value,
  setValue,
  defaultLang,
  masterTimes,
}) => {
  const { calculatedData } = useContext(BookingContext);
  const hourFormat = getHourFormat();
  const selectedDate = Form.useWatch('start_date_date', form);
  const selectedMaster = Form.useWatch('master', form);
  const today = moment(new Date());

  const getTimes = () => {
    const selectedMasterTimes =
      masterTimes[selectedDate?.format('YYYY-MM-DD')] || [];

    if (today.format('YYYY-MM-DD') === selectedDate?.format('YYYY-MM-DD')) {
      return selectedMasterTimes.filter((masterTime) =>
        moment(masterTime, 'HH:mm').isAfter(moment(new Date())),
      );
    }

    return selectedMasterTimes;
  };

  return (
    <Row gutter={10}>
      <Col span={24}>
        <Tag
          icon={<ArrowLeftOutlined />}
          onClick={() => {
            setOpen('addService');
            form.resetFields();
          }}
        />
        <Title className='my-4' level={2}>
          {t('select.service')}
        </Title>
      </Col>
      <Col span={24}>
        <Form.Item
          name='service'
          label={t('select.service')}
          placeholder={t('select.service')}
          rules={[{ required: true, message: t('required') }]}
        >
          <AsyncSelect
            refetch
            disabled={!shop}
            className='w-100'
            onChange={(value) => {
              getServiceByID(value);
              form.setFieldsValue({ extras: [] });
            }}
            fetchOptions={() =>
              fetchService(
                {
                  shop_id: shop.value,
                },
                calculatedData?.items,
              )
            }
          />
        </Form.Item>
      </Col>
      <Col span={24}>
        <Form.Item name='extras' label={t('select.service.extras')}>
          <Select
            disabled={!selectedService?.service_extras?.length}
            options={selectedService?.service_extras?.map((extra) => ({
              label: extra?.translation?.title || t('N/A'),
              value: extra?.id,
              key: extra?.id,
            }))}
            labelInValue
            placeholder={t('select.extra')}
            mode='multiple'
          />
        </Form.Item>
      </Col>
      <Col span={24}>
        <Form.Item
          name='master'
          label={t('select.master')}
          placeholder={t('select.master')}
          rules={[{ required: true, message: t('required') }]}
        >
          <AsyncSelect
            refetch
            className='w-100'
            disabled={!selectedService}
            onChange={(value) => {
              getMasterByID(value);
              form.setFieldsValue({
                start_date_date: undefined,
                start_date_time: undefined,
              });
            }}
            fetchOptions={() =>
              fetchMasterList({
                shop_id: shop?.value,
                service_id: selectedService?.id,
              })
            }
          />
        </Form.Item>
      </Col>
      <Col span={16}>
        <Form.Item
          name='start_date_date'
          label={t('start.date')}
          placeholder={t('select.start.date')}
          rules={[{ required: true, message: t('required') }]}
        >
          <DatePicker
            disabled={!masterTimes || !selectedMaster}
            disabledDate={(date) =>
              new Date(date) <= moment().subtract(1, 'days')
            }
            onChange={() => {
              form.setFieldsValue({
                start_date_time: undefined,
              });
            }}
            className='w-100'
          />
        </Form.Item>
      </Col>
      <Col span={8}>
        <Form.Item
          name='start_date_time'
          label={t('start.time')}
          rules={[{ required: true, message: t('required') }]}
        >
          <Select
            placeholder={t('select.start.time')}
            disabled={!selectedDate}
            options={getTimes()?.map((time) => ({
              label: moment(time, 'HH:mm').format(hourFormat),
              value: time,
            }))}
          />
        </Form.Item>
      </Col>
      <Col span={24}>
        <Form.Item
          name='note'
          label={t('note')}
          rules={[{ required: false, message: t('required') }]}
        >
          <Input.TextArea />
        </Form.Item>
      </Col>
      {Boolean(selectedService?.type === 'offline_out') && (
        <Col span={24}>
          <AddressForm
            value={value}
            setValue={setValue}
            setLocation={setLocation}
          />
          <Map
            location={location}
            setLocation={setLocation}
            setAddress={(value) => {
              form.setFieldsValue({
                [`address[${defaultLang}]`]: value,
              });
            }}
          />
        </Col>
      )}
      <Col span={24} className='mt-4'>
        <Button type='primary' htmlType='submit' className='w-100'>
          {t('submit')}
        </Button>
      </Col>
    </Row>
  );
};

export default ServiceFormItems;
