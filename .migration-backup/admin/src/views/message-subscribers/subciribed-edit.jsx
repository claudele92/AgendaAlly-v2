import React, { useEffect, useRef, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import {
  Button,
  Card,
  Col,
  DatePicker,
  Form,
  Input,
  Row,
  Select,
  Typography,
} from 'antd';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import {
  disableRefetch,
  removeFromMenu,
  setMenuData,
} from '../../redux/slices/menu';
import { useTranslation } from 'react-i18next';
import TextEditor from './textEditor';
import moment from 'moment';
import messageSubscriberService from '../../services/messageSubscriber';
import Loading from '../../components/loading';
import { fetchMessageSubscriber } from '../../redux/slices/messegeSubscriber';
import emailService from '../../services/emailSettings';
import { DebounceSelect } from '../../components/search';
import EmailTemplatePreview from './EmailTemplatePreview';

const options = [
  { label: 'Order (financial/deferred)', value: 'order' },
   { label: 'Subscription / Digest', value: 'subscribe' },
  { label: 'Email Verification', value: 'verify' },
  { label: 'Password Reset', value: 'reset' },
];

const MessageSubciribedAdd = () => {
  const { t } = useTranslation();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const dispatch = useDispatch();
  const [form] = Form.useForm();
  const currentSubject = Form.useWatch('subject', form);
  const currentType = Form.useWatch('type', form);
  const navigate = useNavigate();
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [loading, setLoading] = useState(true);
  const [template, setTemplate] = useState(null);
  const fetchSequence = useRef(0);
  const { id } = useParams();
  const { defaultLang, languages } = useSelector(
    (state) => state.formLang,
    shallowEqual,
  );

  useEffect(() => {
    return () => {
      const values = form.getFieldsValue(true);
      const send_to = JSON.stringify(values.send_to);
      const data = { ...values, send_to };
      dispatch(setMenuData({ activeMenu, data }));
    };
  }, []);

  const fetchSubscriber = (id) => {
    const sequence = ++fetchSequence.current;
    setLoading(true);
    messageSubscriberService
      .getById(id)
      .then((res) => {
        if (sequence !== fetchSequence.current) return;
        const record = res.data;
        setTemplate(record);
        const data = {
          ...record,
          send_to: moment(record.send_to, 'YYYY-MM-DD HH:mm:ss'),
          has_date: true,
          email_setting_id: record.email_setting
            ? {
                label: record.email_setting.host,
                value: record.email_setting.id,
              }
            : record.email_setting_id,
        };
        form.setFieldsValue(data);
      })
      .finally(() => {
        if (sequence === fetchSequence.current) {
          setLoading(false);
          dispatch(disableRefetch(activeMenu));
        }
      });
  };

  const onFinish = (values) => {
    const systemTemplate =
      Boolean(template?.system_template) ||
      Number(template?.status) === 2 ||
      ['verify', 'reset'].includes(template?.type);
    const body = systemTemplate
      ? {
          subject: values.subject,
          body: values.body,
          alt_body: values.alt_body,
          type: template.type,
          email_setting_id: template.email_setting_id,
          send_to: template.send_to,
        }
      : {
          ...values,
          send_to: moment(values.send_to).format('YYYY-MM-DD HH:mm:ss'),
          email_setting_id:
            values.email_setting_id?.value || values.email_setting_id,
        };
    setLoadingBtn(true);
    const nextUrl = 'message/subscriber';
    messageSubscriberService
      .update(id, body)
      .then(() => {
        toast.success(t('successfully.created'));
        dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
        navigate(`/${nextUrl}`);
        dispatch(fetchMessageSubscriber());
      })
      .finally(() => setLoadingBtn(false));
  };

  useEffect(() => {
    setTemplate(null);
    form.resetFields();
    fetchSubscriber(id);
  }, [activeMenu.refetch, id]);

  const getInitialValues = () => {
    const data = activeMenu.data;
    if (!data?.send_to) {
      return data;
    }
    const start = data.send_to;
    return {
      ...data,
      send_to: moment(start, 'YYYY-MM-DD'),
    };
  };

  const fetchEmailProvider = () => {
    return emailService.get().then(({ data }) =>
      data.map((item) => ({
        label: item.host,
        value: item.id,
      })),
    );
  };

  return (
    <>
      {!loading ? (
        <Card
          title={
            currentSubject || t('email.template.edit', 'Edit email template')
          }
          className='h-100'
        >
          <Form
            name='subscriber-add'
            layout='vertical'
            onFinish={onFinish}
            form={form}
            initialValues={{
              ...activeMenu.data,
              ...getInitialValues(),
            }}
            className='d-flex flex-column h-100'
          >
            <Row gutter={12}>
              <Col xs={24} md={12}>
                <Form.Item
                  label={t('subject')}
                  name='subject'
                  rules={[
                    {
                      required: true,
                      message: t('required'),
                    },
                  ]}
                >
                  <Input disabled={template?.editable === false} />
                </Form.Item>
              </Col>
              <Col xs={24} md={12}>
                <Form.Item
                  label={t('type')}
                  name='type'
                  rules={[
                    {
                      required: true,
                      message: t('required'),
                    },
                  ]}
                >
                  <Select disabled options={options} className='w-100' />
                </Form.Item>
              </Col>
              {!(
                Boolean(template?.system_template) ||
                Number(template?.status) === 2 ||
                ['verify', 'reset'].includes(template?.type)
              ) && (
                <Col xs={24} md={12}>
                  <Form.Item
                    label={t('email.setting.id')}
                    name='email_setting_id'
                    rules={[{ required: true, message: t('required') }]}
                  >
                    <DebounceSelect
                      fetchOptions={fetchEmailProvider}
                      className='w-100'
                      placeholder={t('email.setting.id')}
                    />
                  </Form.Item>
                </Col>
              )}

              <Col span={24}>
                <TextEditor
                  languages={languages}
                  form={form}
                  lang={defaultLang}
                  accountTemplate={Boolean(template?.system_template) || Number(template?.status) === 2 || ['verify', 'reset'].includes(currentType)}
                />
                {['verify', 'reset'].includes(currentType) && (
                  <Typography.Text type='secondary'>
                    {t(
                      'email.template.placeholder.hint',
                      'Use $verify_code in both bodies. Subject is also the heading. Delivery, expiry and account/action URLs remain application controlled.',
                    )}
                  </Typography.Text>
                )}
              </Col>
              <Col xs={24} md={12}>
                <Form.Item
                  label={t('alt.body')}
                  name='alt_body'
                  rules={[
                    {
                      required: true,
                      message: t('required'),
                    },
                  ]}
                >
                  <Input.TextArea autoSize={{ minRows: 3, maxRows: 12 }} />
                </Form.Item>
              </Col>

              {!(
                Boolean(template?.system_template) ||
                Number(template?.status) === 2 ||
                ['verify', 'reset'].includes(template?.type)
              ) && (
                <Col xs={24} md={6}>
                  <Form.Item
                    label={t('send.to')}
                    name='send_to'
                    rules={[
                      {
                        required: true,
                        message: t('required'),
                      },
                    ]}
                  >
                    <DatePicker
                      showTime
                      className='w-100'
                      disabledDate={(current) =>
                        moment().add(-1, 'days') >= current
                      }
                    />
                  </Form.Item>
                </Col>
              )}
            </Row>
            <div className='flex-grow-1 d-flex flex-column justify-content-end'>
              <div className='pb-5'>
                <div
                  className='d-flex flex-wrap align-items-center'
                  style={{ gap: 8 }}
                >
                  <EmailTemplatePreview
                    form={form}
                    type={currentType}
                    templateKey={id}
                    previewable={template?.previewable}
                  />
                   <Button type='primary' htmlType='submit' loading={loadingBtn} disabled={template?.editable === false}>
                    {t('save', 'Save wording')}
                  </Button>
                </div>
              </div>
            </div>
          </Form>
        </Card>
      ) : (
        <Loading />
      )}
    </>
  );
};

export default MessageSubciribedAdd;
