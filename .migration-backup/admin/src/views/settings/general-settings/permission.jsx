import { Card, Col, Form, InputNumber, Row, Switch } from 'antd';
import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useSelector } from 'react-redux';
import { useDispatch } from 'react-redux';
import { shallowEqual } from 'react-redux';
import { toast } from 'react-toastify';
import settingService from 'services/settings';
import { fetchSettings as getSettings } from 'redux/slices/globalSettings';
import { CheckOutlined, CloseOutlined } from '@ant-design/icons';
import useDemo from 'helpers/useDemo';
import useDebounce from 'helpers/useDebounce';
import useDidUpdate from 'helpers/useDidUpdate';
import { setRefetch } from '../../../redux/slices/menu';

const Permission = () => {
  const { t } = useTranslation();
  const { isDemo } = useDemo();
  const [form] = Form.useForm();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const dispatch = useDispatch();
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [inputValue, setInputValue] = useState({
    orderRefreshTimeout: activeMenu.data?.order_refresh_timeout,
  });

  function updateSettings(data) {
    setLoadingBtn(true);
    settingService
      .update(data)
      .then(() => {
        toast.success(t('successfully.updated'));
        dispatch(setRefetch(activeMenu));
      })
      .finally(() => setLoadingBtn(false));
  }

  const debounceOrderRefreshTimeout = useDebounce(
    inputValue?.orderRefreshTimeout,
    1000,
  );
  useDidUpdate(() => {
    if (
      (debounceOrderRefreshTimeout === 0 ||
        debounceOrderRefreshTimeout >= 30) &&
      debounceOrderRefreshTimeout !== activeMenu.data?.order_refresh_timeout
    ) {
      updateSettings({ order_refresh_timeout: debounceOrderRefreshTimeout });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [debounceOrderRefreshTimeout]);

  return (
    <Form
      layout='vertical'
      form={form}
      name='global-settings'
      initialValues={{
        ...activeMenu.data,
        active_parcel: Number(activeMenu.data?.active_parcel),
      }}
    >
      <Card title={t('permission')}>
        <Row gutter={24}>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('system.refund')}</b>
                <p className='text-overflow'>
                  {t(
                    'You.decide.whether.the.project.has.a.refund.system.or.not',
                  )}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='system_refund' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => updateSettings({ system_refund: e })}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('order.auto.approved')}</b>
                <p className='text-overflow'>
                  {t(
                    'When.you.create.the.status.of.the.order.you.choose.the.approved.status',
                  )}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='order_auto_approved' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => updateSettings({ order_auto_approved: e })}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('parcel.order.auto.approved')}</b>
                <p className='text-overflow'>
                  {t(
                    'When.you.create.parcel.order.it.creates.with.status.approved',
                  )}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item
                  name='parcel_order_auto_approved'
                  valuePropName='checked'
                >
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) =>
                      updateSettings({ parcel_order_auto_approved: e })
                    }
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('move.next.bookings')}</b>
                <p className='text-overflow'>
                  {t(
                    'you.choose.whether.to.move.on.to.the.next.one.if.you.have.multiple.bookings.at.the.same.time',
                  )}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item
                  name='can_move_the_reservation_time'
                  valuePropName='checked'
                >
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) =>
                      updateSettings({ can_move_the_reservation_time: e })
                    }
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('refund.delete')}</b>
                <p className='text-overflow'>
                  {t(
                    'You.decide.whether.to.show.the.refund.system.disable.button',
                  )}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='refund_delete' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => updateSettings({ refund_delete: e })}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('order.auto.deliveryman')}</b>
                <p className='text-overflow'>
                  {t(
                    'You.choose.the.deliveryman.yourself.when.you.create.the.order',
                  )}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item
                  name='order_auto_delivery_man'
                  valuePropName='checked'
                >
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) =>
                      updateSettings({ order_auto_delivery_man: e })
                    }
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('blog.active')}</b>
                <p className='text-overflow'>
                  {t('Automatically.activate.blogs')}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='blog_active' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => updateSettings({ blog_active: e })}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('prompt.email.modal')}</b>
                <p className='text-overflow'>
                  {t('Send.sms.to.email.subscribers')}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='prompt_email_modal' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => updateSettings({ prompt_email_modal: e })}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('referral.active')}</b>
                <p className='text-overflow'>
                  {t('You.choose.whether.the.referral.will.work.or.not')}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='referral_active' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => updateSettings({ referral_active: e })}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('aws.active')}</b>
                <p className='text-overflow'>
                  {t('You.choose.whether.the.aws.will.work.or.not')}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='aws' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    disabled={isDemo}
                    onChange={(e) => updateSettings({ aws: e })}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('by.subscription')}</b>
                <p className='text-overflow'>
                  {t('You.choose.whether.the.by.subscription.will.work.or.not')}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='by_subscription' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    disabled={isDemo}
                    onChange={(e) => updateSettings({ by_subscription: e })}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('group.order')}</b>
                <p className='text-overflow'>
                  {t('You.choose.whether.enable.group.order.or.not')}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='group_order' valuePropName='checked'>
                  <Switch
                    loading={loadingBtn}
                    disabled={isDemo}
                    onChange={(e) => updateSettings({ group_order: e })}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('is_demo')}</b>
                <p className='text-overflow'>
                  {t('You.choose.whether.enable.is.demo.or.not')}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='is_demo' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => updateSettings({ is_demo: e })}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('activate.parcel.mode')}</b>
                <p className='text-overflow'>
                  {t('You.choose.whether.enable.parcel.or.not')}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='active_parcel' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => {
                      updateSettings({ active_parcel: e });
                    }}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('auto.approve.products')}</b>
                <p className='text-overflow'>
                  {t('You.choose.whether.auto.approve.products.or.not')}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='product_auto_approve' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => {
                      updateSettings({ product_auto_approve: e });
                    }}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('auto.approve.categories')}</b>
                <p className='text-overflow'>
                  {t('You.choose.whether.auto.approve.categories.or.not')}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='category_auto_approve' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => {
                      updateSettings({ category_auto_approve: e });
                    }}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('require.phone.for.create.order')}</b>
                <p className='text-overflow'>
                  {t(
                    'You.choose.whether.require.phone.number.or.not.for.create.order',
                  )}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item
                  name='before_order_phone_required'
                  valuePropName='checked'
                >
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => {
                      updateSettings({ before_order_phone_required: e });
                    }}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          {/*<Col span={24}>*/}
          {/*  <Row gutter={24}>*/}
          {/*    <Col span={21}>*/}
          {/*      <b>{t('subscription')}</b>*/}
          {/*      <p className='text-overflow'>*/}
          {/*        {t('You.choose.whether.enable.or.disable.subscription')}*/}
          {/*      </p>*/}
          {/*    </Col>*/}
          {/*    <Col span={3} className='mt-3'>*/}
          {/*      <Form.Item name='subscription' valuePropName='checked'>*/}
          {/*        <Switch*/}
          {/*          checkedChildren={<CheckOutlined />}*/}
          {/*          unCheckedChildren={<CloseOutlined />}*/}
          {/*          loading={loadingBtn}*/}
          {/*          onChange={(e) => {*/}
          {/*            updateSettings({ subscription: e });*/}
          {/*          }}*/}
          {/*        />*/}
          {/*      </Form.Item>*/}
          {/*    </Col>*/}
          {/*  </Row>*/}
          {/*</Col>*/}
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('products.enabled')}</b>
                <p className='text-overflow'>
                  {t('You.choose.whether.enable.or.disable.products')}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='products_enabled' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => {
                      updateSettings({ products_enabled: e });
                    }}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('shop.reviews.enabled')}</b>
                <p className='text-overflow'>
                  {t('You.choose.whether.enable.or.disable.reviews')}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='shop_reviews_enabled' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => {
                      updateSettings({ shop_reviews_enabled: e });
                    }}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('product.reviews.enabled')}</b>
                <p className='text-overflow'>
                  {t('You.choose.whether.enable.or.disable.reviews')}
                </p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item
                  name='product_reviews_enabled'
                  valuePropName='checked'
                >
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => {
                      updateSettings({ product_reviews_enabled: e });
                    }}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('ai.features')}</b>
                <p>{t('you.can.enable.or.disable.ai.features')}</p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item name='ai_active' valuePropName='checked'>
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => {
                      updateSettings({ ai_active: e });
                    }}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          <Col span={24}>
            <Row gutter={24}>
              <Col span={21}>
                <b>{t('enable.auto.refresh.order')}</b>
                <p>{t('you.can.enable.or.disable.auto.refresh.order')}</p>
              </Col>
              <Col span={3} className='mt-3'>
                <Form.Item
                  name='enable_order_refresh_timeout'
                  valuePropName='checked'
                >
                  <Switch
                    checkedChildren={<CheckOutlined />}
                    unCheckedChildren={<CloseOutlined />}
                    loading={loadingBtn}
                    onChange={(e) => {
                      setInputValue((prev) => {
                        return { ...prev, orderRefreshTimeout: e ? 30 : 0 };
                      });
                    }}
                  />
                </Form.Item>
              </Col>
            </Row>
          </Col>
          {Number(activeMenu.data?.order_refresh_timeout) > 0 && (
            <Col span={24}>
              <Row gutter={24}>
                <Col span={17}>
                  <b>{t('order.refresh.timeout')}</b>
                  <p>{t('duration.of.order.refresh.timeout.min.val.30')}</p>
                </Col>
                <Col span={6} className='mt-3'>
                  <Form.Item
                    rules={[
                      {
                        type: 'number',
                        min: 30,
                        message: t('minimum.is.30'),
                      },
                    ]}
                    name='order_refresh_timeout'
                    style={{
                      justifyContent: 'end',
                    }}
                  >
                    <InputNumber
                      min={30}
                      className='w-100'
                      onChange={(e) =>
                        setInputValue((prev) => {
                          return { ...prev, orderRefreshTimeout: e };
                        })
                      }
                      placeholder={t('order.refresh.timeout')}
                      disabled={loadingBtn}
                    />
                  </Form.Item>
                </Col>
              </Row>
            </Col>
          )}
        </Row>
      </Card>
    </Form>
  );
};

export default Permission;
