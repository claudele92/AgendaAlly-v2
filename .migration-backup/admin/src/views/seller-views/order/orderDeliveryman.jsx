import React, { useCallback, useEffect, useRef, useState } from 'react';
import { Alert, Button, Col, Form, Modal, Row } from 'antd';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import orderService from '../../../services/seller/order';
import { setRefetch } from '../../../redux/slices/menu';
import { useTranslation } from 'react-i18next';
import { DebounceSelect } from 'components/search';
import sellerDeliverymenService from 'services/seller/user';

export default function OrderDeliveryman({ orderDetails: data, handleCancel }) {
  const { t } = useTranslation();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [form] = Form.useForm();
  const dispatch = useDispatch();
  const [loading, setLoading] = useState(false);
  const [eligibilityLookupError, setEligibilityLookupError] = useState(false);
  const driverFieldChanged = useRef(false);

  const fetchDeliverymen = useCallback((search) => {
    const paramsData = {
      perPage: 10,
      page: 1,
      search,
    };
    return sellerDeliverymenService.getDeliverymans(paramsData).then((res) =>
      (res.data || []).flatMap((delivery) => {
        const id = Number(delivery?.id);
        if (!Number.isSafeInteger(id) || id <= 0) return [];

        const label =
          [delivery.firstname, delivery.lastname].filter(Boolean).join(' ') ||
          `#${id}`;
        return [{ label, value: id }];
      }),
    );
  }, []);

  useEffect(() => {
    form.resetFields(['deliveryman']);
    driverFieldChanged.current = false;
    setEligibilityLookupError(false);

    const existingDriver = data?.deliveryman;
    const search = [
      existingDriver?.firstname,
      existingDriver?.lastname,
    ]
      .filter(Boolean)
      .join(' ')
      .trim();

    if (!existingDriver?.id || !search) return undefined;

    let active = true;
    fetchDeliverymen(search)
      .then((options) => {
        if (!active) return;
        const existingId = Number(existingDriver.id);
        const eligibleOption = options.find(
          (option) => option.value === existingId,
        );
        if (
          eligibleOption &&
          !driverFieldChanged.current &&
          !form.getFieldValue('deliveryman')
        ) {
          form.setFieldsValue({ deliveryman: eligibleOption });
        }
      })
      .catch(() => {
        if (active) setEligibilityLookupError(true);
      });

    return () => {
      active = false;
    };
  }, [
    data?.id,
    data?.deliveryman?.id,
    data?.deliveryman?.firstname,
    data?.deliveryman?.lastname,
    fetchDeliverymen,
    form,
  ]);

  const onFinish = (values) => {
    const deliverymanId = Number(values?.deliveryman?.value);
    if (!Number.isSafeInteger(deliverymanId) || deliverymanId <= 0) {
      form.setFields([
        {
          name: 'deliveryman',
          errors: [t('required')],
        },
      ]);
      return;
    }

    const params = { deliveryman_id: values.deliveryman.value };
    params.deliveryman_id = deliverymanId;
    setLoading(true);
    orderService
      .updateDelivery(data.id, params)
      .then(() => {
        handleCancel();
        dispatch(setRefetch(activeMenu));
      })
      .finally(() => setLoading(false));
  };

  return (
    <Modal
      visible={!!data}
      title={data.title}
      onCancel={handleCancel}
      footer={[
        <Button
          key='save'
          type='primary'
          onClick={() => form.submit()}
          loading={loading}
        >
          {t('save')}
        </Button>,
        <Button key='cancel' type='default' onClick={handleCancel}>
          {t('cancel')}
        </Button>,
      ]}
    >
      {eligibilityLookupError && (
        <Alert
          type='warning'
          showIcon
          style={{ marginBottom: 16 }}
          message='The current driver could not be checked for this shop'
          description='The previous assignment was left unselected. Choose an eligible driver before saving.'
        />
      )}
      <Form form={form} layout='vertical' onFinish={onFinish}>
        <Row gutter={12}>
          <Col span={24}>
            <Form.Item
              label={t('deliveryman')}
              name='deliveryman'
              rules={[
                {
                  required: true,
                  message: t('required'),
                },
                {
                  validator: (_, value) => {
                    const id = Number(value?.value);
                    return Number.isSafeInteger(id) && id > 0
                      ? Promise.resolve()
                      : Promise.reject(new Error(t('required')));
                  },
                },
              ]}
            >
              <DebounceSelect
                fetchOptions={fetchDeliverymen}
                onChange={() => {
                  driverFieldChanged.current = true;
                  setEligibilityLookupError(false);
                }}
              />
            </Form.Item>
          </Col>
        </Row>
      </Form>
    </Modal>
  );
}