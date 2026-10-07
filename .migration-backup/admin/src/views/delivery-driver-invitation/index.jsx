import React, { useEffect, useMemo, useState } from 'react';
import {
  Alert,
  Button,
  Form,
  Input,
  Modal,
  Space,
  Typography,
  notification,
} from 'antd';
import {
  ArrowRightOutlined,
  LockOutlined,
  MailOutlined,
  SafetyCertificateOutlined,
  UserOutlined,
} from '@ant-design/icons';
import { useDispatch, useSelector } from 'react-redux';
import { data as roleUrls } from 'configs/menu-config';
import { api_url, RECAPTCHA_DISABLED_LOCAL } from 'configs/app-global';
import Recaptcha from 'components/recaptcha';
import Stage1Brand from 'components/agendaally-stage1/Stage1Brand';
import authService from 'services/auth';
import deliveryDriverInvitations from 'services/delivery-driver-invitations';
import userService from 'services/user';
import { setUserData } from 'redux/slices/auth';
import cls from '../seller-views/deliverymen/delivery-driver.module.scss';
import '../../styles/agendaally-stage1-tokens.scss';

const { Text } = Typography;

function extractPreview(response) {
  const first = response?.data;
  const value =
    first && typeof first === 'object' && !Array.isArray(first)
      ? first
      : response;
  if (value?.data && typeof value.data === 'object' && value.data.email) {
    return value.data;
  }
  return value;
}

function normalizeEmail(email) {
  return typeof email === 'string' ? email.trim().toLowerCase() : '';
}

function getResponsePayload(response) {
  const outer = response?.data || response;
  return outer?.data && typeof outer.data === 'object' ? outer.data : outer;
}

function extractVerificationHash(value) {
  const input = value.trim();
  if (!input) return '';
  const isNativeVerificationHash = (hash) => /^[a-f0-9]{64}$/i.test(hash);
  if (isNativeVerificationHash(input)) return input;

  try {
    const parsed = new URL(input, window.location.origin);
    const configuredApi = new URL(api_url, window.location.origin);
    if (parsed.origin !== configuredApi.origin) return '';
    const prefix = configuredApi.pathname.endsWith('/')
      ? configuredApi.pathname
      : `${configuredApi.pathname}/`;
    if (!parsed.pathname.startsWith(prefix)) return '';
    const route = parsed.pathname.slice(prefix.length).split('/').filter(Boolean);
    let hash = '';
    if (route.length === 3 && route[0] === 'auth' && route[1] === 'verify') {
      hash = route[2];
    } else if (
      route.length === 4 &&
      route[0] === 'email' &&
      route[1] === 'verify'
    ) {
      hash = route[3];
    }
    return isNativeVerificationHash(hash) ? hash : '';
  } catch {
    return '';
  }
}

function DeliveryDriverInvitation() {
  const dispatch = useDispatch();
  const { user } = useSelector((state) => state.auth);
  const [invitationToken, setInvitationToken] = useState('');
  const [invitation, setInvitation] = useState(null);
  const [previewLoading, setPreviewLoading] = useState(true);
  const [previewError, setPreviewError] = useState('');
  const [mode, setMode] = useState('signin');
  const [formLoading, setFormLoading] = useState(false);
  const [actionLoading, setActionLoading] = useState(false);
  const [recaptcha, setRecaptcha] = useState(null);
  const [registrationSent, setRegistrationSent] = useState(false);
  const [verificationLink, setVerificationLink] = useState('');
  const [verificationStatus, setVerificationStatus] = useState('');
  const [actionError, setActionError] = useState('');
  const [completion, setCompletion] = useState(null);
  const [registrationNotice, setRegistrationNotice] = useState('');
  const [signInForm] = Form.useForm();
  const [registerForm] = Form.useForm();

  const invitationEmail = useMemo(
    () => invitation?.email || '',
    [invitation?.email],
  );
  const storedAccessToken =
    typeof window !== 'undefined'
      ? window.localStorage.getItem('token')
      : null;
  const hasAuthenticatedSession =
    Boolean(user) &&
    Boolean(storedAccessToken) &&
    (!user?.token || user.token === storedAccessToken);
  const canRespondToInvitation =
    hasAuthenticatedSession &&
    Boolean(normalizeEmail(invitationEmail)) &&
    normalizeEmail(user?.email) === normalizeEmail(invitationEmail);

  useEffect(() => {
    const fragment = new URLSearchParams(window.location.hash.slice(1));
    const tokenFromFragment = fragment.get('token') || '';
    setInvitationToken(tokenFromFragment);

    let mounted = true;
    if (!tokenFromFragment) {
      setPreviewError(
        'This invitation link is incomplete or no longer available. Ask the shop to send a new invitation.',
      );
      setPreviewLoading(false);
    } else {
      deliveryDriverInvitations
        .preview(tokenFromFragment)
        .then((response) => {
          if (!mounted) return;
          const payload = extractPreview(response);
          if (!payload?.email) {
            setPreviewError(
              'This invitation could not be confirmed. Ask the shop to send a new invitation.',
            );
          } else {
            setInvitation(payload);
            signInForm.setFieldsValue({ email: payload.email });
            registerForm.setFieldsValue({ email: payload.email });
          }
        })
        .catch((requestError) => {
          if (!mounted) return;
          setPreviewError(
            requestError?.response?.data?.message ||
              'This invitation could not be confirmed. It may have expired or been withdrawn. Ask the shop to send a new invitation.',
          );
        })
        .finally(() => {
          if (mounted) setPreviewLoading(false);
        });
    }
    return () => {
      mounted = false;
    };
  }, [registerForm, signInForm]);

  const handleCaptcha = (value) => setRecaptcha(value);

  const handleSignIn = async (values) => {
    if (formLoading) return;
    if (!RECAPTCHA_DISABLED_LOCAL && !recaptcha) {
      setActionError('Complete the configured verification before signing in.');
      return;
    }
    setFormLoading(true);
    setActionError('');
    const credentials = { email: invitationEmail, password: values.password };
    if (!RECAPTCHA_DISABLED_LOCAL) {
      credentials['g-recaptcha-response'] = recaptcha;
    }
    try {
      const response = await authService.login(credentials);
      const account = response?.data;
      if (!account?.access_token || !account?.user) {
        throw new Error('The sign-in response was incomplete.');
      }
      if (
        normalizeEmail(account.user.email) !== normalizeEmail(invitationEmail)
      ) {
        setActionError(
          'This account email does not match the invitation. No session was changed. Sign in with the invited email address.',
        );
        return;
      }
      localStorage.setItem('token', account.access_token);
      dispatch(
        setUserData({
          fullName: `${account.user.firstname || ''} ${account.user.lastname || ''}`,
          role: account.user.role,
          urls: roleUrls[account.user.role],
          img: account.user.img,
          token: account.access_token,
          email: account.user.email,
          id: account.user.id,
          shop_id: account.user?.shop?.id,
          isSuperAdmin: !!account.user.is_super_admin,
          countryAdmin: account.user.country_admin || null,
        }),
      );
      signInForm.resetFields(['password']);
      setRecaptcha(null);
      notification.success({
        message: 'Signed in to your AgendaAlly account',
      });
    } catch (requestError) {
      setActionError(
        requestError?.response?.data?.message ||
          requestError?.message ||
          'Sign-in failed. Check your email and password, then try again.',
      );
    } finally {
      setFormLoading(false);
    }
  };

  const handleRegister = async (values) => {
    if (formLoading) return;
    if (!RECAPTCHA_DISABLED_LOCAL && !recaptcha) {
      setActionError('Complete the configured verification before registering.');
      return;
    }
    setFormLoading(true);
    setActionError('');
    const payload = {
      token: invitationToken,
      firstname: values.firstname.trim(),
      lastname: values.lastname?.trim() || '',
      password: values.password,
      password_confirmation: values.password_confirmation,
    };
    if (!RECAPTCHA_DISABLED_LOCAL) {
      payload['g-recaptcha-response'] = recaptcha;
    }
    try {
      const response = await deliveryDriverInvitations.register(payload);
      const registration = getResponsePayload(response);
      const verificationRequired =
        registration?.verification_required ??
        response?.verification_required ??
        response?.data?.verification_required;
      setRegistrationSent(true);
      setRegistrationNotice(
        verificationRequired === true
          ? `The server says email verification is required before accepting. Registration does not confirm that a verification message was delivered.`
          : verificationRequired === false
            ? `The server did not require email verification in its response. This page cannot confirm message delivery; sign in and let the server validate the account before accepting.`
            : `The response did not specify verification_required. This page cannot confirm message delivery. Email verification is separate from the shop invitation.`,
      );
      setVerificationStatus(
        `Registration completed for ${invitationEmail}. The shop invitation token does not verify your email.`,
      );
      setRecaptcha(null);
      registerForm.resetFields(['password', 'password_confirmation']);
    } catch (requestError) {
      setActionError(
        requestError?.response?.data?.message ||
          'Registration could not be completed. Review the invitation and try again.',
      );
    } finally {
      setFormLoading(false);
    }
  };

  const handleVerification = async () => {
    const hash = extractVerificationHash(verificationLink);
    if (!hash) {
      setVerificationStatus(
        'Paste the verification link from your AgendaAlly email, or its verification hash. Invitation links are not email-verification links.',
      );
      return;
    }
    setActionLoading(true);
    setActionError('');
    setVerificationStatus('');
    try {
      await deliveryDriverInvitations.verifyEmail(hash);
      setVerificationStatus(
        `Email verification completed. Sign in with ${invitationEmail} to review and accept the shop invitation.`,
      );
    } catch (requestError) {
      setVerificationStatus(
        requestError?.response?.data?.message ||
          'Email verification did not complete. Use the native verification link for the invited address, if available, or request a new verification email.',
      );
    } finally {
      setActionLoading(false);
    }
  };

  const resendEmailVerification = async () => {
    if (!invitationEmail || actionLoading) return;
    setActionLoading(true);
    setActionError('');
    try {
      await deliveryDriverInvitations.resendVerification(invitationEmail);
      setVerificationStatus(
        `A verification request was submitted for ${invitationEmail}. Check that inbox and its spam folder. This page cannot confirm delivery.`,
      );
    } catch (requestError) {
      setVerificationStatus(
        requestError?.response?.data?.message ||
          'A verification email could not be requested. Try again later.',
      );
    } finally {
      setActionLoading(false);
    }
  };

  const respondToInvitation = async (decision) => {
    if (!canRespondToInvitation || !invitationToken || actionLoading) return;
    setActionLoading(true);
    setActionError('');
    setCompletion(null);
    try {
      if (decision === 'accept') {
        const response = await deliveryDriverInvitations.accept(invitationToken);
        const payload = getResponsePayload(response);
        const row = payload?.data || payload?.invitation || payload;
        const membershipState =
          row?.membership?.state ||
          payload?.membership?.state ||
          row?.state ||
          payload?.state ||
          '';

        let refreshedRole = '';
        let profileRefreshMessage =
          'The latest account role was not returned. Sign in again before opening other AgendaAlly areas.';
        try {
          const profileResponse = await userService.profileShow();
          const profile = getResponsePayload(profileResponse);
          const actualRole = profile?.user?.role || profile?.role;
          if (typeof actualRole === 'string' && actualRole) {
            refreshedRole = actualRole;
            dispatch(
              setUserData({
                ...user,
                role: actualRole,
                urls: roleUrls[actualRole] || user?.urls,
                ...(profile?.user?.firstname || profile?.firstname
                  ? {
                      fullName: `${profile?.user?.firstname || profile?.firstname} ${profile?.user?.lastname || profile?.lastname || ''}`.trim(),
                    }
                  : {}),
                ...(profile?.user?.email || profile?.email
                  ? { email: profile?.user?.email || profile?.email }
                  : {}),
              }),
            );
            profileRefreshMessage = `AgendaAlly refreshed your account role from your profile: ${actualRole}.`;
          }
        } catch {
          profileRefreshMessage =
            'The shop acceptance was recorded, but the account role could not be refreshed here. Sign in again before opening other AgendaAlly areas.';
        }

        setCompletion({
          kind: 'accepted',
          message: `You accepted the relationship with ${invitation.shop_name || invitation.title || 'this shop'}.`,
          membershipState,
          refreshedRole,
          profileRefreshMessage,
        });
      } else {
        await deliveryDriverInvitations.decline(invitationToken);
        setCompletion({
          kind: 'declined',
          message: `You declined the invitation from ${invitation.shop_name || invitation.title || 'this shop'}.`,
        });
      }
      setInvitationToken('');
      window.history.replaceState(
        window.history.state,
        '',
        `${window.location.pathname}${window.location.search}`,
      );
    } catch (requestError) {
      const serverMessage = requestError?.response?.data?.message;
      setActionError(
        serverMessage ||
          (requestError?.response?.status === 409
            ? 'This invitation cannot be accepted in its current state. Contact the shop and ask them to review it.'
            : 'Your choice could not be recorded. Confirm that you are signed in with the invited driver account and try again.'),
      );
    } finally {
      setActionLoading(false);
    }
  };

  const confirmDecline = () => {
    Modal.confirm({
      title: 'Decline this shop invitation?',
      content:
        'The shop will not be added to your driver relationships. You can ask them to invite you again if you change your mind.',
      okText: 'Decline invitation',
      okButtonProps: { danger: true },
      cancelText: 'Keep invitation',
      onOk: () => respondToInvitation('decline'),
    });
  };

  return (
    <div className={cls.publicPage}>
      <header className={cls.publicHeader}>
        <Stage1Brand />
      </header>
      <main className={cls.publicMain}>
        <aside className={cls.publicAside}>
          <div>
            <div className={cls.publicKicker}>A shop invitation</div>
            <h1 className={cls.publicTitle}>
              Work with a shop.
              <br />
              On your terms.
            </h1>
            <p className={cls.publicDescription}>
              Your AgendaAlly account and password belong to you. Review the
              invitation, verify your own email, then decide whether to accept.
            </p>
          </div>
          <div className={cls.inviteFrom}>
            {previewLoading ? (
              'Checking this invitation…'
            ) : invitation ? (
              <>
                Invitation for <strong>{invitation.email}</strong>
                <br />
                From <strong>{invitation.shop_name || invitation.title || 'a shop'}</strong>
                {invitation.expires_at && (
                  <>
                    <br />
                    Expires{' '}
                    {new Date(invitation.expires_at).toLocaleDateString()}
                  </>
                )}
              </>
            ) : (
              'Invitation details are unavailable.'
            )}
          </div>
        </aside>

        <section className={cls.publicForm} aria-label='Driver invitation actions'>
          {previewLoading ? (
            <div className={cls.statusBox} role='status'>
              Checking invitation details…
            </div>
          ) : previewError ? (
            <Alert
              showIcon
              type='warning'
              message='Invitation unavailable'
              description={previewError}
            />
          ) : completion ? (
            <div className={cls.statusBox} role='status'>
              <h2 className={cls.publicFormTitle}>
                {completion.kind === 'accepted'
                  ? 'Shop relationship accepted'
                  : 'Invitation declined'}
              </h2>
              <p>{completion.message}</p>
              {completion.kind === 'accepted' && (
                <>
                  <p>
                    Shop eligibility:{' '}
                    {invitation.eligible === true
                      ? 'eligible'
                      : invitation.eligible === false
                        ? 'not eligible'
                        : 'not returned by the server'}
                  </p>
                  <p>
                    Membership state:{' '}
                    {completion.membershipState ||
                      'The acceptance response did not include a membership state.'}
                  </p>
                  <p>{completion.profileRefreshMessage}</p>
                  {completion.refreshedRole && (
                    <p>
                      Refreshed account role: {completion.refreshedRole}. This role
                      came from the native profile response.
                    </p>
                  )}
                </>
              )}
            </div>
          ) : (
            <>
              {actionError && (
                <Alert
                  showIcon
                  type='error'
                  style={{ marginBottom: 16 }}
                  message={actionError}
                />
              )}
              {invitation.state && invitation.state !== 'pending' ? (
                <Alert
                  showIcon
                  type='info'
                  message={`Invitation state: ${invitation.state}`}
                  description='This invitation is not awaiting a driver decision. Contact the shop that sent it if you believe the status is incorrect.'
                />
              ) : !canRespondToInvitation ? (
                <>
                  <h2 className={cls.publicFormTitle}>
                    {mode === 'signin' ? 'Sign in to continue' : 'Create your account'}
                  </h2>
                  <p className={cls.publicFormCopy}>
                    {mode === 'signin'
                      ? 'Use the invited email and your existing AgendaAlly password.'
                      : 'Set your own password. Account creation and email verification are separate from accepting this invitation.'}
                  </p>
                  <div className={cls.publicTabs} role='tablist' aria-label='Account access'>
                    <button
                      type='button'
                      role='tab'
                      aria-selected={mode === 'signin'}
                      className={`${cls.tabButton} ${mode === 'signin' ? cls.tabActive : ''}`}
                      onClick={() => {
                        setMode('signin');
                        setActionError('');
                      }}
                    >
                      Sign in
                    </button>
                    <button
                      type='button'
                      role='tab'
                      aria-selected={mode === 'register'}
                      className={`${cls.tabButton} ${mode === 'register' ? cls.tabActive : ''}`}
                      onClick={() => {
                        setMode('register');
                        setActionError('');
                      }}
                    >
                      Register
                    </button>
                  </div>

                  {mode === 'signin' ? (
                    <Form
                      form={signInForm}
                      layout='vertical'
                      onFinish={handleSignIn}
                      requiredMark={false}
                    >
                      <Form.Item label='Invited email'>
                        <Input
                          prefix={<MailOutlined />}
                          value={invitationEmail}
                          readOnly
                          autoComplete='username'
                        />
                      </Form.Item>
                      <Form.Item
                        name='password'
                        label='Your password'
                        rules={[{ required: true, message: 'Enter your password.' }]}
                      >
                        <Input.Password
                          prefix={<LockOutlined />}
                          autoComplete='current-password'
                          disabled={formLoading}
                        />
                      </Form.Item>
                      {!RECAPTCHA_DISABLED_LOCAL && (
                        <div style={{ marginBottom: 18 }}>
                          <div className={cls.publicKicker} style={{ marginBottom: 8 }}>
                            Configured verification
                          </div>
                          <Recaptcha onChange={handleCaptcha} />
                        </div>
                      )}
                      <Button
                        type='primary'
                        htmlType='submit'
                        loading={formLoading}
                        disabled={!RECAPTCHA_DISABLED_LOCAL && !recaptcha}
                        block
                      >
                        Sign in
                        <ArrowRightOutlined />
                      </Button>
                    </Form>
                  ) : registrationSent ? (
                    <>
                      <div className={cls.statusBox} role='status'>
                        Registration completed. {registrationNotice} The shop
                        invitation has not been accepted.
                      </div>
                      <div className={cls.actionDivider} />
                      <h3 className={cls.publicFormTitle} style={{ fontSize: 16 }}>
                        Verify your email
                      </h3>
                      <p className={cls.publicFormCopy}>
                        Email verification is independent of this invitation. If a
                        verification message was issued, paste its link or hash
                        here. A successful request on this page does not confirm
                        message delivery.
                      </p>
                      <Input.TextArea
                        value={verificationLink}
                        onChange={(event) => setVerificationLink(event.target.value)}
                        placeholder='Paste the link from your email, or its verification hash'
                        autoSize={{ minRows: 2, maxRows: 4 }}
                        aria-label='Email verification link or hash'
                      />
                      <Space wrap style={{ marginTop: 10 }}>
                        <Button
                          type='primary'
                          loading={actionLoading}
                          onClick={handleVerification}
                        >
                          Verify email
                        </Button>
                        <Button
                          loading={actionLoading}
                          onClick={resendEmailVerification}
                        >
                          Request verification email
                        </Button>
                        <Button
                          type='link'
                          onClick={() => {
                            setMode('signin');
                            setVerificationStatus('');
                          }}
                        >
                          Back to sign in
                        </Button>
                      </Space>
                      {verificationStatus && (
                        <div className={cls.statusBox} style={{ marginTop: 14 }} role='status'>
                          {verificationStatus}
                        </div>
                      )}
                    </>
                  ) : (
                    <>
                      <Alert
                        showIcon
                        type='info'
                        style={{ marginBottom: 16 }}
                        message='Use the invited email for your driver account'
                        description='Your email must match the invitation. You will verify the account using a separate email before accepting.'
                      />
                      <Form
                        form={registerForm}
                        layout='vertical'
                        onFinish={handleRegister}
                        requiredMark={false}
                      >
                        <Form.Item label='Invited email'>
                          <Input
                            prefix={<MailOutlined />}
                            value={invitationEmail}
                            readOnly
                            autoComplete='email'
                          />
                        </Form.Item>
                        <Form.Item
                          name='firstname'
                          label='First name'
                          rules={[
                            { required: true, message: 'Enter your first name.' },
                            { max: 100, message: 'Name is too long.' },
                          ]}
                        >
                          <Input prefix={<UserOutlined />} autoComplete='given-name' />
                        </Form.Item>
                        <Form.Item
                          name='lastname'
                          label='Last name (optional)'
                          rules={[{ max: 100, message: 'Name is too long.' }]}
                        >
                          <Input autoComplete='family-name' />
                        </Form.Item>
                        <Form.Item
                          name='password'
                          label='Create a password'
                          rules={[
                            { required: true, message: 'Create your password.' },
                            { min: 8, message: 'Use at least 8 characters.' },
                          ]}
                        >
                          <Input.Password
                            prefix={<LockOutlined />}
                            autoComplete='new-password'
                            disabled={formLoading}
                          />
                        </Form.Item>
                        <Form.Item
                          name='password_confirmation'
                          label='Confirm password'
                          dependencies={['password']}
                          rules={[
                            { required: true, message: 'Confirm your password.' },
                            ({ getFieldValue }) => ({
                              validator(_, value) {
                                return !value || getFieldValue('password') === value
                                  ? Promise.resolve()
                                  : Promise.reject(new Error('Passwords do not match.'));
                              },
                            }),
                          ]}
                        >
                          <Input.Password
                            prefix={<LockOutlined />}
                            autoComplete='new-password'
                            disabled={formLoading}
                          />
                        </Form.Item>
                        {!RECAPTCHA_DISABLED_LOCAL && (
                          <div style={{ marginBottom: 18 }}>
                            <div className={cls.publicKicker} style={{ marginBottom: 8 }}>
                              Configured verification
                            </div>
                            <Recaptcha onChange={handleCaptcha} />
                          </div>
                        )}
                        <Button
                          type='primary'
                          htmlType='submit'
                          loading={formLoading}
                          disabled={!RECAPTCHA_DISABLED_LOCAL && !recaptcha}
                          block
                        >
                          Create my account
                          <ArrowRightOutlined />
                        </Button>
                      </Form>
                    </>
                  )}
                </>
              ) : (
                <>
                  <h2 className={cls.publicFormTitle}>Review your invitation</h2>
                  <p className={cls.publicFormCopy}>
                    Signed in as <strong>{user.email || user.fullName}</strong>.
                    Accepting adds this shop relationship only; it does not make
                    you shop staff or share your credentials.
                  </p>
                  <p className={cls.publicFormCopy}>
                    Assignment eligibility is checked after verified acceptance
                    for this shop. A pending invitation alone does not grant
                    delivery access.
                  </p>
                  <div className={cls.statusBox}>
                    <strong>{invitation.shop_name || invitation.title || 'Shop'}</strong>
                    <br />
                    Invitation addressed to {invitationEmail}
                    <br />
                    Current invitation state: {invitation.state || 'available'}
                  </div>
                  <Space direction='vertical' style={{ width: '100%', marginTop: 20 }}>
                    <Button
                      type='primary'
                      block
                      loading={actionLoading}
                      disabled={!invitationToken || Boolean(completion)}
                      onClick={() => respondToInvitation('accept')}
                    >
                      <SafetyCertificateOutlined />
                      Accept shop relationship
                    </Button>
                    <Button
                      block
                      danger
                      loading={actionLoading}
                      disabled={!invitationToken || Boolean(completion)}
                      onClick={confirmDecline}
                    >
                      Decline invitation
                    </Button>
                  </Space>
                  <Text
                    type='secondary'
                    style={{ display: 'block', marginTop: 16, fontSize: 12 }}
                  >
                    No membership is added until you choose Accept. This invitation
                    cannot change another shop relationship or your account password.
                  </Text>
                </>
              )}

              {registrationSent && verificationStatus && mode === 'signin' && (
                <div className={cls.statusBox} style={{ marginTop: 14 }} role='status'>
                  {verificationStatus} {registrationNotice}
                </div>
              )}
            </>
          )}
        </section>
      </main>
    </div>
  );
}

export default DeliveryDriverInvitation;