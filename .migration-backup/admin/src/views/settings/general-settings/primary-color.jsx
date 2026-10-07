import { Button, Card, Col, Form, Input, Modal, Radio, Row } from 'antd';
import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { useSelector } from 'react-redux';
import { useDispatch } from 'react-redux';
import { shallowEqual } from 'react-redux';
import { toast } from 'react-toastify';
import { setMenuData } from '../../../redux/slices/menu';
import settingService from '../../../services/settings';
import { fetchSettings as getSettings } from '../../../redux/slices/globalSettings';
import { QrCodeCard } from 'components/qr-code-card';
import '../../../assets/scss/components/radio-card.scss';
import { ExclamationCircleFilled } from '@ant-design/icons';
import { SketchPicker } from 'react-color';

const { confirm } = Modal;
const qrCodeTypes = [
  {
    title: 'View 1',
    value: 'w1',
    img: '/img/qr-code-type1.png',
  },
  {
    title: 'View 2',
    value: 'w2',
    img: '/img/qr-code-type2.png',
  },
  {
    title: 'View 3',
    value: 'w3',
    img: '/img/qr-code-type3.png',
  },
];

const defaultPrimaryColors = {
  primary_color: '#BB9B6A',
  primary_button_font_color: '#ffffff',
};

const PrimaryColor = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const [form] = Form.useForm();

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);

  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleResetColors = () => {
    form.setFieldsValue(defaultPrimaryColors);
  };

  const updateSettings = (data) => {
    setIsSubmitting(true);
    settingService
      .update(data)
      .then(() => {
        toast.success(t('successfully.updated'));
        dispatch(getSettings());
      })
      .finally(() => {
        setIsSubmitting(false);
      });
  };

  const onFinish = (values) => {
    updateSettings(values);
  };

  return (
    <Form
      layout='vertical'
      form={form}
      name='global-settings'
      onFinish={onFinish}
      initialValues={{
        primary_color:
          activeMenu.data?.primary_color || defaultPrimaryColors.primary_color,
        primary_button_font_color:
          activeMenu.data?.primary_button_font_color ||
          defaultPrimaryColors.primary_button_font_color,
      }}
    >
      <Card
        title={t('primary.color')}
        extra={
          <Button type='button' onClick={handleResetColors}>
            {t('reset')}
          </Button>
        }
      >
        <Row gutter={12}>
          <Col span={12}>
            <Form.Item
              label={t('primary.color')}
              name='primary_color'
              rules={[{ required: true, message: t('required') }]}
            >
              <SketchPicker
                color={Form.useWatch('primary_color', form)}
                disableAlpha
                onChangeComplete={(color) =>
                  form.setFieldsValue({ primary_color: color.hex })
                }
              />
              <div
                style={{
                  width: '30px',
                  height: '30px',
                  marginTop: '10px',
                  border: '1px solid #ddd',
                  borderRadius: '8px',
                  background: Form.useWatch('primary_color', form),
                }}
              />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item
              label={t('primary.button.font.color')}
              name='primary_button_font_color'
              rules={[{ required: true, message: t('required') }]}
            >
              <SketchPicker
                color={Form.useWatch('primary_button_font_color', form)}
                disableAlpha
                onChangeComplete={(color) =>
                  form.setFieldsValue({ primary_button_font_color: color.hex })
                }
              />
              <div
                style={{
                  width: '30px',
                  height: '30px',
                  marginTop: '10px',
                  border: '1px solid #ddd',
                  borderRadius: '8px',
                  background: Form.useWatch('primary_button_font_color', form),
                }}
              />
            </Form.Item>
          </Col>
        </Row>
        <Button
          type='primary'
          onClick={() => form.submit()}
          loading={isSubmitting}
        >
          {t('save')}
        </Button>
      </Card>
    </Form>
  );
};

export default PrimaryColor;
