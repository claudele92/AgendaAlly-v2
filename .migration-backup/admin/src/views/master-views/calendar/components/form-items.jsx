import React, { useContext, useState } from 'react';
import {
  Alert,
  Button,
  Card,
  Checkbox,
  Form,
  Input,
  Radio,
  Select,
  Switch,
  Typography,
} from 'antd';
import { t } from 'i18next';
import bookingService from 'services/master/booking';
import { BookingContext } from '../provider';
const { Title } = Typography;

const FormItems = ({ item }) => {
  const [form] = Form.useForm();
  const [submitError, setSubmitError] = useState('');

  const { service_id, setIsAddForm, calculatedData, setCalculatedData } =
    useContext(BookingContext);

  const renderFormItem = (data) => {
    switch (data.answer_type) {
      case 'single_answer':
        return (
          <Radio.Group>
            {data.answer?.map((item) => (
              <Radio value={item}>{item}</Radio>
            ))}
          </Radio.Group>
        );
      case 'short_answer':
        return <Input />;
      case 'long_answer':
        return <Input.TextArea />;
      case 'multiple_choice':
        return (
          <Checkbox.Group>
            {data.answer?.map((item) => (
              <Checkbox value={item}>{item}</Checkbox>
            ))}
          </Checkbox.Group>
        );
      case 'drop_down':
        return (
          <Select>
            {data.answer?.map((item) => (
              <Select.Option value={item}>{item}</Select.Option>
            ))}
          </Select>
        );
      case 'yes_or_no':
        return <Switch />;
      default:
        return '';
    }
  };

  const perevFormItems = calculatedData?.items?.flatMap(
    (item) => item?.data?.form || [],
  ) || [];

  const onFinish = (values) => {
    if (service_id === null || service_id === undefined || service_id === '') {
      setSubmitError('Select a saved booking before adding a form.');
      return;
    }
    if (!calculatedData?.items?.[0]?.data || !Array.isArray(item?.data)) {
      setSubmitError('The selected booking or form data is unavailable.');
      return;
    }
    setSubmitError('');
    bookingService
      .update(service_id, {
        data: {
          ...calculatedData.items[0].data,
          form: [
            ...perevFormItems,
            {
              ...item,
              data: item.data?.map((item) => {
                const userAnswer = values[item.answer_type];
                return {
                  ...item,
                  user_answer: userAnswer
                    ? typeof userAnswer === 'object'
                      ? userAnswer
                      : [userAnswer]
                    : undefined,
                };
              }),
            },
          ],
        },
      })
      .then((res) => {
        form.resetFields();
        setIsAddForm(false);
        setCalculatedData((prev) => ({
          ...prev,
          items:
            prev.items?.map((item) => ({ ...item, data: res.data.data })) || [],
        }));
      })
      .catch((error) => {
        setSubmitError(
          error?.response?.data?.message ||
            error?.message ||
            'Unable to save the form.',
        );
      });
  };
  if (!item) return null;
  if (!Array.isArray(item.data)) {
    return (
      <Alert
        className='w-100'
        type='error'
        showIcon
        message='The selected form does not contain valid questions.'
      />
    );
  }

  return (
    <Card className='w-100'>
      <Title>{item?.translation?.title}</Title>
      <Form layout='vertical' form={form} onFinish={onFinish}>
        {submitError && (
          <Alert className='mb-3' type='error' showIcon message={submitError} />
        )}
        {item.data.map((item) => {
          return (
            <>
              {item.answer_type === 'description_text' ? (
                <Alert message={item.question} type='info' />
              ) : (
                <Form.Item
                  label={item.question}
                  name={item.answer_type}
                  rules={[{ required: Boolean(item.required) }]}
                >
                  {renderFormItem(item)}
                </Form.Item>
              )}
            </>
          );
        })}
        <Button className='mt-4' type='primary' htmlType='submit'>
          {t('complete')}
        </Button>
      </Form>
    </Card>
  );
};

export default FormItems;
