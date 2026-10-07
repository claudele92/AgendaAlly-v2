import { useEffect, useState } from 'react';
import { data } from 'configs/menu-config';
import {
  ArrowRightOutlined,
  LockOutlined,
  SafetyCertificateOutlined,
  ShopOutlined,
  UserOutlined,
} from '@ant-design/icons';
import { Alert, Button, Form, Input, notification, Typography } from 'antd';
import authService from 'services/auth';
import { getRequestErrorMessage } from 'services/request-error.mjs';
import { useDispatch, useSelector } from 'react-redux';
import { setUserData } from 'redux/slices/auth';
import { fetchRestSettings, fetchSettings } from 'redux/slices/globalSettings';
import { useTranslation } from 'react-i18next';
import { RECAPTCHA_DISABLED_LOCAL } from 'configs/app-global';
import Recaptcha from 'components/recaptcha';
import { setMenu } from 'redux/slices/menu';
import Stage1Brand from 'components/agendaally-stage1/Stage1Brand';
import businessWorkspaceImage from '../../assets/images/auth/business-workspace.jpg';
import '../../styles/agendaally-stage1-tokens.scss';
import cls from './login.module.scss';

const { Title } = Typography;

const Login = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const { user } = useSelector((state) => state.auth);

  const [loading, setLoading] = useState(false);
  const [loginError, setLoginError] = useState('');
  const [recaptcha, setRecaptcha] = useState(null);

  const handleRecaptchaChange = (value) => {
    setRecaptcha(value);
  };

  const fetchUserSettings = (role) => {
    switch (role) {
      case 'admin':
        dispatch(fetchSettings({}));
        break;
      case 'seller':
        dispatch(fetchRestSettings({ seller: true }));
        break;
      default:
        dispatch(fetchRestSettings({}));
    }
  };

  const handleLogin = (values) => {
    if (loading) return;
    setLoginError('');

    if (!RECAPTCHA_DISABLED_LOCAL && !recaptcha) {
      notification.error({
        message: 'Complete the configured CAPTCHA before logging in.',
      });
      return;
    }

    const body = {
      password: values.password,
    };
    if (!RECAPTCHA_DISABLED_LOCAL) {
      body['g-recaptcha-response'] = recaptcha;
    }
    if (values.email.includes('@')) {
      body.email = values.email;
    } else {
      body.phone = values.email.replace(/[^0-9]/g, '');
    }
    setLoading(true);
    authService
      .login(body)
      .then((res) => {
        const user = {
          fullName: `${res?.data?.user?.firstname || ''} ${res?.data?.user?.lastname || ''}`,
          role: res.data.user.role,
          urls: data[res.data.user.role],
          img: res.data.user.img,
          token: res.data.access_token,
          email: res.data.user.email,
          id: res.data.user.id,
          shop_id: res.data.user?.shop?.id,
          isSuperAdmin: !!res.data.user.is_super_admin,
          countryAdmin: res.data.user.country_admin || null,
        };
        if (user.role === 'waiter') {
          dispatch(
            setMenu({
              name: t('my.orders'),
              url: 'waiter/orders',
              id: 'my_orders',
              refetch: true,
            }),
          );
        }
        if (user?.role === 'user') {
          notification.error({
            message: t('ERROR_101'),
          });
          return;
        }
        localStorage.setItem('token', res?.data?.access_token);
        dispatch(setUserData(user));
        fetchUserSettings(user?.role);
      })
      .catch((error) => setLoginError(getRequestErrorMessage(error, t)))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    fetchUserSettings(user?.role || '');
    return () => {};
    // eslint-disable-next-line
  }, []);

  return (
    <div className={`${cls.loginContainer} agendaally-stage1-auth`}>
      <header className={cls.topbar}>
        <Stage1Brand />
        <span className={cls.workspaceLabel}>Business workspace</span>
      </header>

      <main className={cls.main}>
        <section
          className={cls.formPanel}
          aria-labelledby='business-login-title'
        >
          <div className={cls.formContent}>
            <div className={cls.mobileNote}>
              <ShopOutlined aria-hidden='true' />
              <span>One practical workspace for your day-to-day business.</span>
            </div>
            <div className={cls.eyebrow}>AgendaAlly for business</div>
            <Title level={1} id='business-login-title' className={cls.heading}>
              Good to have you back.
            </Title>
            <p className={cls.subheading}>
              Sign in to manage your work, team and customers.
            </p>

            {loginError && <Alert type='error' showIcon message={loginError} className='mb-3' />}
            <Form
              name='login-form'
              layout='vertical'
              onFinish={handleLogin}
              className={cls.form}
              requiredMark={false}
            >
              <Form.Item
                name='email'
                label='Email or phone number'
                rules={[
                  {
                    required: true,
                    message: t('please.enter.your.email'),
                  },
                ]}
              >
                <Input
                  prefix={<UserOutlined aria-hidden='true' />}
                  placeholder='name@yourbusiness.com or phone number'
                  autoComplete='username'
                  disabled={loading}
                />
              </Form.Item>
              <Form.Item
                name='password'
                label={t('password')}
                rules={[
                  {
                    required: true,
                    message: t('please.enter.your.password'),
                  },
                ]}
              >
                <Input.Password
                  prefix={<LockOutlined aria-hidden='true' />}
                  placeholder={t('password')}
                  autoComplete='current-password'
                  disabled={loading}
                />
              </Form.Item>
              <div className={cls.protectedNote}>
                <span>Protected workspace</span>
              </div>

              <div className={cls.captchaArea}>
                <div className={cls.captchaLabel}>
                  <SafetyCertificateOutlined aria-hidden='true' />
                  <span>Configured verification</span>
                </div>
                <Recaptcha onChange={handleRecaptchaChange} />
              </div>
              {!RECAPTCHA_DISABLED_LOCAL && !recaptcha && (
                <div className={cls.captchaHint} role='status'>
                  Complete the configured verification before signing in.
                </div>
              )}

              <Form.Item className={cls.submitRow}>
                <Button
                  type='primary'
                  htmlType='submit'
                  className={cls.submitButton}
                  loading={loading}
                  disabled={!RECAPTCHA_DISABLED_LOCAL && !Boolean(recaptcha)}
                >
                  {t('login')}
                  {!loading && <ArrowRightOutlined aria-hidden='true' />}
                </Button>
              </Form.Item>
            </Form>

            <p className={cls.accessFootnote}>
              Your landing workspace follows your assigned account permissions.
            </p>
            <p className={cls.helpText}>
              Having trouble? Contact your account administrator.
            </p>
          </div>
        </section>

        <aside
          className={cls.artwork}
          aria-label='An independent business workspace'
          style={{ backgroundImage: `url(${businessWorkspaceImage})` }}
        >
          <div className={cls.artworkBadge}>
            <ShopOutlined aria-hidden='true' />
            <span>A clearer day at work</span>
          </div>
          <div className={cls.artworkCopy}>
            <h2>
              Your work.
              <br />
              In good order.
            </h2>
            <p>
              Keep appointments, customers and day-to-day business in one calm,
              capable workspace.
            </p>
          </div>
          <div className={cls.artworkFoot}>
            Made for the people behind great service.
          </div>
        </aside>
      </main>
    </div>
  );
};

export default Login;
