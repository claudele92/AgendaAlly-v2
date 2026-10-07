import React, { useState } from 'react';
import { Button, Col, Form, Modal, Row, Select } from 'antd';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import { disableRefetch } from '../../redux/slices/menu';
import shopService from '../../services/shop';
import { fetchShops } from '../../redux/slices/shop';
import TextArea from 'antd/es/input/TextArea';

const statuses = ['new', 'approved', 'rejected'];

export default function ShopStatusModal({ data, handleCancel, paramsData }) {
  const { t } = useTranslation();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [form] = Form.useForm();
  const dispatch = useDispatch();
  const [loading, setLoading] = useState(false);

  const onFinish = (values) => {
    setLoading(true);
    const params = { ...values };
    shopService
      .statusChange(data.uuid, params)
      .then(() => {
        handleCancel();
        dispatch(fetchShops(paramsData));
        dispatch(disableRefetch(activeMenu));
      })
      .finally(() => setLoading(false));
  };

  return (
    <Modal
      visible={!!data}
      title={t(data.name)}
      onCancel={handleCancel}
      footer={[
        <Button type='primary' onClick={() => form.submit()} loading={loading}>
          {t('save')}
        </Button>,
        <Button type='default' onClick={handleCancel}>
          {t('cancel')}
        </Button>,
      ]}
    >
      <Form
        form={form}
        layout='vertical'
        onFinish={onFinish}
        initialValues={{ status: data.status, status_note: data.status_note }}
      >
        <Row gutter={12}>
          <Col span={24}>
            <Form.Item
              label={t('status')}
              name='status'
              rules={[
                {
                  required: true,
                  message: t('required'),
                },
              ]}
            >
              <Select>
                {statuses.map((item, idx) => (
                  <Select.Option key={item + idx} value={item}>
                    {t(item)}
                  </Select.Option>
                ))}
              </Select>
            </Form.Item>
          </Col>
          <Form.Item
            noStyle
            shouldUpdate={(prevValues, currentValues) =>
              prevValues.status !== currentValues.status
            }
          >
            {({ getFieldValue }) =>
              getFieldValue('status') === 'rejected' ? (
                <Col span={24}>
                  <Form.Item
                    label={t('status.note')}
                    name='status_note'
                    rules={[
                      {
                        required: true,
                        message: t('required'),
                      },
                      {
                        validator(_, value) {
                          if (value && value?.trim()?.length < 2) {
                            return Promise.reject(
                              new Error(t('must.be.at.least.2')),
                            );
                          }
                          return Promise.resolve();
                        },
                      },
                    ]}
                  >
                    <TextArea rows={4} maxLength={100} />
                  </Form.Item>
                </Col>
              ) : null
            }
          </Form.Item>
        </Row>
      </Form>
    </Modal>
  );
}
