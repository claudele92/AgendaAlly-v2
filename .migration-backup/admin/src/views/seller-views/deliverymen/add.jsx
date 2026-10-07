import React, { useState } from 'react';
import {
  Alert,
  Button,
  Card,
  Form,
  Input,
  Skeleton,
  Space,
  Typography,
  notification,
} from 'antd';
import {
  ArrowLeftOutlined,
  CopyOutlined,
  MailOutlined,
  SafetyCertificateOutlined,
} from '@ant-design/icons';
import { useSelector, shallowEqual } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { useNavigationScope } from 'context/navigation-scope';
import deliveryDriverInvitations from 'services/delivery-driver-invitations';
import { getDeliveryDriverPermissions } from './delivery-driver-access.mjs';
import {
  invitationResponsePayload,
  normalizeDriverInvitationLink,
} from './delivery-driver-link.mjs';
import cls from './delivery-driver.module.scss';

const { Title, Text } = Typography;

function SellerDeliverymanInvite() {
  const navigate = useNavigate();
  const { user } = useSelector((state) => state.auth, shallowEqual);
  const navigationScope = useNavigationScope();
  const { canInvite } = getDeliveryDriverPermissions(user, navigationScope);
  const [form] = Form.useForm();
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState(null);
  const [error, setError] = useState('');

  const createInvitation = async (values) => {
    if (!canInvite || loading) return;
    setLoading(true);
    setError('');
    setResult(null);
    try {
      const response = await deliveryDriverInvitations.create({
        email: values.email.trim(),
        ...(values.firstname?.trim()
          ? { firstname: values.firstname.trim() }
          : {}),
        ...(values.lastname?.trim()
          ? { lastname: values.lastname.trim() }
          : {}),
      });
      const payload = invitationResponsePayload(response);
      const row = payload?.invitation || payload;
      setResult({
        row,
        link: normalizeDriverInvitationLink(
          payload?.invitation_link || row?.invitation_link || '',
        ),
        notificationStatus:
          payload?.notification_status ||
          row?.notification_status ||
          'not provided',
      });
      form.resetFields();
    } catch (requestError) {
      setError(
        requestError?.response?.data?.message ||
          'The invitation could not be created. Check the address and try again.',
      );
    } finally {
      setLoading(false);
    }
  };

  const copyInvitation = async () => {
    if (!result?.link) return;
    try {
      await navigator.clipboard.writeText(result.link);
      notification.success({
        message: 'Invitation link copied',
        description:
          'Share this link securely with the intended driver. It is not shown again in the roster.',
      });
    } catch {
      notification.warning({
        message: 'Copy was not available',
        description: 'Select and copy the invitation link from the field.',
      });
    }
  };

  return (
    <div className={cls.page}>
      <Card className={cls.inviteCard}>
        <Button
          type='link'
          icon={<ArrowLeftOutlined />}
          style={{ paddingLeft: 0, marginBottom: 14 }}
          onClick={() => navigate('/seller/invitations/deliverymen')}
        >
          Back to drivers
        </Button>
        <div className={cls.eyebrow}>Secure shop invitation</div>
        <Title level={1} className={cls.title}>
          Invite a delivery driver
        </Title>
        <p className={cls.inviteHint}>
          Send an invitation to the driver’s own email address. They set or use
          their own password, verify the same email with AgendaAlly, and then
          explicitly accept this shop relationship. Creating an invitation does
          not create an account or grant access.
        </p>

        {navigationScope?.status !== 'ready' ? (
          <Skeleton active paragraph={{ rows: 4 }} />
        ) : !canInvite ? (
          <Alert
            showIcon
            type='warning'
            message='You cannot send shop invitations'
            description='This session does not include the staff.invite permission for the current shop.'
          />
        ) : (
          <>
            {error && (
              <Alert
                showIcon
                type='error'
                className={cls.notice}
                message={error}
              />
            )}
            {result ? (
              <div className={cls.inviteResult} role='status'>
                <div className={cls.resultLabel}>Invitation created</div>
                <Text>
                  {result.row?.email ||
                    'The invited email is not available in the response.'}
                </Text>
                <div style={{ marginTop: 9 }}>
                  <Text type='secondary'>
                    Notification status: {result.notificationStatus}. This status
                    does not confirm that a message reached the recipient.
                  </Text>
                </div>
                {result.link ? (
                  <Space
                    direction='vertical'
                    style={{ width: '100%', marginTop: 16 }}
                    size={8}
                  >
                    <Input.TextArea
                      readOnly
                      value={result.link}
                      autoSize={{ minRows: 2, maxRows: 4 }}
                      aria-label='New invitation link'
                    />
                    <Button
                      type='primary'
                      icon={<CopyOutlined />}
                      onClick={copyInvitation}
                    >
                      Copy secure invitation link
                    </Button>
                    <Text type='secondary'>
                      This one-time link is displayed only from this creation
                      response. Keep it private and send it only to the invited
                      driver.
                    </Text>
                  </Space>
                ) : (
                  <Alert
                    style={{ marginTop: 14 }}
                    showIcon
                    type='warning'
                    message='The invitation was created, but no link was returned.'
                    description='The API response did not include a shareable link. Do not use a historical roster record as a substitute. Ask an authorized administrator to review the invitation.'
                  />
                )}
                <Button
                  style={{ marginTop: 18 }}
                  onClick={() => navigate('/seller/invitations/deliverymen')}
                >
                  Return to driver roster
                </Button>
              </div>
            ) : (
              <Form
                form={form}
                layout='vertical'
                requiredMark={false}
                onFinish={createInvitation}
                autoComplete='off'
              >
                <Form.Item
                  name='email'
                  label='Driver email address'
                  rules={[
                    { required: true, message: 'Enter the driver’s email address.' },
                    { type: 'email', message: 'Enter a valid email address.' },
                    { max: 254, message: 'Email address is too long.' },
                  ]}
                >
                  <Input
                    prefix={<MailOutlined />}
                    type='email'
                    autoComplete='email'
                    placeholder='driver@example.com'
                    disabled={loading}
                  />
                </Form.Item>
                <Space size={12} style={{ width: '100%' }} align='start'>
                  <Form.Item
                    name='firstname'
                    label='First name (optional)'
                    style={{ flex: 1, minWidth: 0 }}
                    rules={[{ max: 100, message: 'Name is too long.' }]}
                  >
                    <Input autoComplete='off' disabled={loading} />
                  </Form.Item>
                  <Form.Item
                    name='lastname'
                    label='Last name (optional)'
                    style={{ flex: 1, minWidth: 0 }}
                    rules={[{ max: 100, message: 'Name is too long.' }]}
                  >
                    <Input autoComplete='off' disabled={loading} />
                  </Form.Item>
                </Space>
                <Alert
                  showIcon
                  type='info'
                  icon={<SafetyCertificateOutlined />}
                  style={{ marginBottom: 18 }}
                  message='Account security stays with the driver'
                  description='No password, phone number, contact verification, or shop ownership is requested here.'
                />
                <Button
                  type='primary'
                  htmlType='submit'
                  loading={loading}
                  disabled={!canInvite}
                >
                  Create invitation
                </Button>
              </Form>
            )}
          </>
        )}
      </Card>
    </div>
  );
}

export default SellerDeliverymanInvite;