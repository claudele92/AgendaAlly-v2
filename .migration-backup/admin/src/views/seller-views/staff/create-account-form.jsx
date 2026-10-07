import React, { useState } from 'react';
import {
  Row,
  Col,
  Form,
  Space,
  Button,
  Input,
  InputNumber,
  DatePicker,
  Select,
} from 'antd';
import { useTranslation } from 'react-i18next';
import MediaUpload from 'components/upload';
import moment from 'moment/moment';
import { toast } from 'react-toastify';
import userService from '../../../services/seller/user';
import { BranchSelect, RoleSelect } from './role-branch-selects';

// Same field set as master-invitations/user-form.jsx (that form's
// create-only path, since a brand-new staff account has no working-hours/
// disabled-times/branch tabs the way an existing master's edit view does),
// but posting a shop_role_id instead of a hardcoded role: 'master' - the
// backend (Seller\UserController::store()) turns any shop_role_id into
// role: shop_manager itself, so this form never needs to set role at all.
export default function CreateAccountForm({
  shopRoles,
  shopLocations,
  handleCancel,
  onInvited,
}) {
  const { t } = useTranslation();
  const [form] = Form.useForm();

  const [image, setImage] = useState([]);
  const [roleId, setRoleId] = useState(null);
  const [locationIds, setLocationIds] = useState([]);
  const [error, setError] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  const onFinish = (values) => {
    if (!roleId) {
      return;
    }

    const body = {
      ...values,
      birthday: moment(values.birthday).format('YYYY-MM-DD'),
      images: image?.[0]?.name ? [image[0].name] : [],
      shop_role_id: roleId,
      ...(locationIds.length ? { shop_location_ids: locationIds } : {}),
    };

    setSubmitting(true);
    setError(null);

    userService
      .create(body)
      .then(() => {
        toast.success(t('invite.sent'));
        onInvited?.();
        handleCancel();
      })
      .catch((err) => setError(err?.response?.data?.params))
      .finally(() => setSubmitting(false));
  };

  return (
    <Form form={form} layout='vertical' onFinish={onFinish}>
      <Row gutter={12}>
        <Col span={24}>
          <Form.Item
            name='avatar'
            label={t('images')}
            rules={[{ required: !image?.length, message: t('required') }]}
          >
            <MediaUpload
              type='users'
              imageList={image}
              setImageList={setImage}
              form={form}
              multiple={false}
              name='logo_img'
            />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('firstname')}
            name='firstname'
            help={error?.firstname?.[0]}
            validateStatus={error?.firstname ? 'error' : 'success'}
            rules={[{ required: true, message: t('required') }]}
          >
            <Input className='w-100' maxLength={20} />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('lastname')}
            name='lastname'
            help={error?.lastname?.[0]}
            validateStatus={error?.lastname ? 'error' : 'success'}
            rules={[{ required: true, message: t('required') }]}
          >
            <Input className='w-100' maxLength={20} />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('phone')}
            name='phone'
            help={error?.phone?.[0]}
            validateStatus={error?.phone ? 'error' : 'success'}
            rules={[{ required: true, message: t('required') }]}
          >
            <InputNumber min={0} className='w-100' />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('birthday')}
            name='birthday'
            rules={[{ required: true, message: t('required') }]}
          >
            <DatePicker
              className='w-100'
              disabledDate={(current) => moment().add(-18, 'years') <= current}
              defaultPickerValue={moment().add(-18, 'years')}
            />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('gender')}
            name='gender'
            rules={[{ required: true, message: t('required') }]}
          >
            <Select picker='dayTime' className='w-100'>
              <Select.Option value='male' key='male'>
                {t('male')}
              </Select.Option>
              <Select.Option value='female' key='female'>
                {t('female')}
              </Select.Option>
            </Select>
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('email')}
            name='email'
            help={error?.email?.[0]}
            validateStatus={error?.email ? 'error' : 'success'}
            rules={[
              { required: true, message: t('required') },
              { type: 'email', message: t('invalid.email') },
            ]}
          >
            <Input type='email' className='w-100' />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('password')}
            name='password'
            help={error?.password?.[0]}
            validateStatus={error?.password ? 'error' : 'success'}
            rules={[{ required: true, message: t('required') }]}
          >
            <Input.Password type='password' className='w-100' placeholder='********' />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('password.confirmation')}
            help={error?.password_confirmation?.[0]}
            validateStatus={error?.password_confirmation ? 'error' : 'success'}
            name='password_confirmation'
            dependencies={['password']}
            hasFeedback
            rules={[
              { required: true, message: t('required') },
              ({ getFieldValue }) => ({
                validator(rule, value) {
                  if (!value || getFieldValue('password') === value) {
                    return Promise.resolve();
                  }
                  return Promise.reject(t('two.passwords.dont.match'));
                },
              }),
            ]}
          >
            <Input.Password type='password' placeholder='********' />
          </Form.Item>
        </Col>
        <Col span={12}>
          <RoleSelect shopRoles={shopRoles} value={roleId} onChange={setRoleId} />
        </Col>
        <Col span={12}>
          <BranchSelect
            shopLocations={shopLocations}
            value={locationIds}
            onChange={setLocationIds}
          />
        </Col>
      </Row>
      <Space wrap>
        <Button type='primary' htmlType='submit' disabled={!roleId} loading={submitting}>
          {t('send.invite')}
        </Button>
      </Space>
    </Form>
  );
}
