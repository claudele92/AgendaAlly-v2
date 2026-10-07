import React, { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import { Alert, Button, Card, Col, Form, Input, InputNumber, Row, Switch } from 'antd';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import {
  disableRefetch,
  removeFromMenu,
  setMenuData,
} from '../../redux/slices/menu';
import { useTranslation } from 'react-i18next';
import emailService from '../../services/emailSettings';
import Loading from '../../components/loading';
import { fetchEmailProvider } from 'redux/slices/emailProvider';
import { safeEmailSettingForm, emailSettingPayload, passwordStatusText } from './credential-form.mjs';

const EmailProviderEdit = () => {
  const { t } = useTranslation();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { user } = useSelector((state) => state.auth, shallowEqual);
  const dispatch = useDispatch();
  const [form] = Form.useForm();
  const navigate = useNavigate();
  const { id } = useParams();
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [loading, setLoading] = useState(false);
  const [testEmail, setTestEmail] = useState(user?.email || '');
  const [testingBtn, setTestingBtn] = useState(false);
  const [testResult, setTestResult] = useState(null);
  const [credentialStatus, setCredentialStatus] = useState('missing');
  const [adminTestAvailable, setAdminTestAvailable] = useState(false);

  const handleSendTest = () => {
    if (!adminTestAvailable || credentialStatus !== 'configured') return;
    setTestingBtn(true);
    setTestResult(null);
    emailService
      .sendTest(id, { email: testEmail })
      .then(() => {
        setTestResult({ type: 'success', message: t('test.email.sent.successfully') });
      })
      .catch((err) => {
        setTestResult({
          type: 'error',
          message: err?.response?.data?.message || t('test.email.failed'),
        });
      })
      .finally(() => setTestingBtn(false));
  };

  useEffect(() => {
    return () => {
      const data = safeEmailSettingForm(form.getFieldsValue(true));
      dispatch(setMenuData({ activeMenu, data }));
    };
  }, []);

  const getEmailProvider = (alias) => {
    setLoading(true);
    emailService
      .getById(alias)
      .then((res) => {
        const data = safeEmailSettingForm(res.data);
        setCredentialStatus(data.credential_status || 'missing');
        setAdminTestAvailable(data.admin_test_available === true);
        form.setFieldsValue(data);
        dispatch(setMenuData({ activeMenu, data }));
      })
      .finally(() => {
        dispatch(disableRefetch(activeMenu));
        setLoading(false);
      });
  };

  const onFinish = (values) => {
    const body = emailSettingPayload(values);
    setLoadingBtn(true);
    const nextUrl = 'settings/emailProviders';
    emailService
      .update(id, body)
      .then(() => {
        form.resetFields(['password']);
        toast.success(t('successfully.created'));
        dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
        navigate(`/${nextUrl}`);
        dispatch(fetchEmailProvider({}));
      })
      .finally(() => setLoadingBtn(false));
  };

  useEffect(() => {
    getEmailProvider(id);
  }, [id, activeMenu.refetch]);

  return (
    <Card title={t('edit.email.provider')} className='h-100'>
      {loading ? (
        <Loading />
      ) : (
        <Form
          name='email-provider-add'
          layout='vertical'
          onFinish={onFinish}
          form={form}
          initialValues={{
            smtp_debug: true,
            smtp_auth: true,
            active: true,
            ...safeEmailSettingForm(activeMenu.data),
          }}
          className='d-flex flex-column h-100'
        >
          <Row gutter={12}>
            <Col span={12}>
              <Form.Item
                rules={[
                  {
                    required: true,
                    message: t('required'),
                  },
                  {
                    type: 'email',
                    message: t('invalid.email'),
                  },
                ]}
                label={t('email')}
                name='from_to'
              >
                <Input placeholder='Email' />
              </Form.Item>
            </Col>

            <Col span={12}>
              <Form.Item
                rules={[{
                  validator(_, value) {
                    if (!value || value.trim() === '') {
                      return credentialStatus === 'configured'
                        ? Promise.resolve()
                        : Promise.reject(new Error(t('required')));
                    }
                    return value.length >= 6 ? Promise.resolve()
                      : Promise.reject(new Error(t('min.6.letters')));
                  },
                }]}
                label={t('password')}
                name='password'
                extra={passwordStatusText(credentialStatus)}
                normalize={(value) =>
                  value?.trim() === '' ? value?.trim() : value
                }
              >
                <Input.Password autoComplete='new-password' placeholder={
                  credentialStatus === 'configured' ? 'Configured — leave blank to keep' : 'Enter SMTP password'
                } />
              </Form.Item>
            </Col>

            <Col span={12}>
              <Form.Item
                rules={[
                  {
                    validator(_, value) {
                      if (!value) {
                        return Promise.reject(new Error(t('required')));
                      } else if (value && value?.trim() === '') {
                        return Promise.reject(new Error(t('no.empty.space')));
                      } else if (value && value?.trim().length < 2) {
                        return Promise.reject(
                          new Error(t('must.be.at.least.2')),
                        );
                      }
                      return Promise.resolve();
                    },
                  },
                ]}
                label={t('host')}
                name='host'
              >
                <Input />
              </Form.Item>
            </Col>

            <Col span={12}>
              <Form.Item
                rules={[
                  {
                    validator(_, value) {
                      if (!value) {
                        return Promise.reject(new Error(t('required')));
                      } else if (value && value?.trim() === '') {
                        return Promise.reject(new Error(t('no.empty.space')));
                      } else if (value && value?.trim().length < 2) {
                        return Promise.reject(
                          new Error(t('must.be.at.least.2')),
                        );
                      }
                      return Promise.resolve();
                    },
                  },
                ]}
                label={t('from_site')}
                name='from_site'
              >
                <Input />
              </Form.Item>
            </Col>

            <Col span={12}>
              <Form.Item
                rules={[
                  {
                    required: true,
                    message: t('required'),
                  },
                ]}
                label={t('port')}
                name='port'
              >
                <InputNumber min={0} className='w-100' />
              </Form.Item>
            </Col>

            <Col span={8}>
              <Form.Item
                label={t('active')}
                name='active'
                valuePropName='checked'
              >
                <Switch />
              </Form.Item>
            </Col>

            <Col span={8}>
              <Form.Item
                valuePropName='checked'
                label={t('smtp_debug')}
                name='smtp_debug'
              >
                <Switch />
              </Form.Item>
            </Col>

            <Col span={8}>
              <Form.Item
                valuePropName='checked'
                label={t('smtp_auth')}
                name='smtp_auth'
              >
                <Switch />
              </Form.Item>
            </Col>
          </Row>
          <Row gutter={12} className='mt-3'>
            <Col span={24}>
              <Form.Item label={t('send.test.email')}>
                <Input.Group compact style={{ display: 'flex', maxWidth: 420 }}>
                  <Input
                    value={testEmail}
                    onChange={(e) => setTestEmail(e.target.value)}
                    placeholder={t('email')}
                    style={{ flex: 1 }}
                  />
                  <Button loading={testingBtn} onClick={handleSendTest}
                    disabled={!adminTestAvailable || credentialStatus !== 'configured'}>
                    {t('send.test.email')}
                  </Button>
                </Input.Group>
                <Alert className='mt-2' type='info' showIcon message={
                  adminTestAvailable
                    ? 'Uses saved settings only. Save changes first. Click once; do not automatically retry an unconfirmed result.'
                    : 'Admin SMTP testing is disabled. General email delivery remains off; this preview requires explicit Admin-test permission.'
                } />
              </Form.Item>
              {testResult && (
                <Alert
                  className='mb-3'
                  type={testResult.type}
                  showIcon
                  message={testResult.message}
                  closable
                  onClose={() => setTestResult(null)}
                />
              )}
            </Col>
          </Row>
          <div className='flex-grow-1 d-flex flex-column justify-content-end'>
            <div className='pb-5'>
              <Button type='primary' htmlType='submit' loading={loadingBtn}>
                {t('submit')}
              </Button>
            </div>
          </div>
        </Form>
      )}
    </Card>
  );
};

export default EmailProviderEdit;
