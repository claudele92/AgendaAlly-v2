import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import {
  Button,
  Alert,
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
import { removeFromMenu, setMenuData } from '../../redux/slices/menu';
import { useTranslation } from 'react-i18next';
import TextEditor from './textEditor';
import moment from 'moment';
import messageSubscriberService from '../../services/messageSubscriber';
import { fetchMessageSubscriber } from '../../redux/slices/messegeSubscriber';
import emailService from '../../services/emailSettings';
import { DebounceSelect } from '../../components/search';
import EmailTemplatePreview from './EmailTemplatePreview';

const options = [
  { label: 'Subscription / Digest (existing workflow)', value: 'subscribe' },
];

const MessageSubciribedAdd = () => {
  const { t } = useTranslation();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const dispatch = useDispatch();
  const [form] = Form.useForm();
  const selectedType = Form.useWatch('type', form);
  const navigate = useNavigate();
  const [loadingBtn, setLoadingBtn] = useState(false);
  const { defaultLang, languages } = useSelector(
    (state) => state.formLang,
    shallowEqual,
  );
  const { subscribers } = useSelector(
    (state) => state.messageSubscriber,
    shallowEqual,
  );

  useEffect(() => {
    return () => {
      const data = form.getFieldsValue(true);
      dispatch(setMenuData({ activeMenu, data }));
    };
  }, []);

  const fetchEmailProvider = () => {
    return emailService.get().then(({ data }) =>
      data.map((item) => ({
        label: item.host,
        value: item.id,
      })),
    );
  };

  const onFinish = (values) => {
    const body = {
      type: values.type, subject: values.subject,
      body: values.body, alt_body: values.alt_body,
    };
    setLoadingBtn(true);
    const nextUrl = 'message/subscriber';
    messageSubscriberService
      .create(body)
      .then(() => {
        toast.success(t('successfully.created'));
        dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
        navigate(`/${nextUrl}`);
        dispatch(fetchMessageSubscriber());
      })
      .finally(() => setLoadingBtn(false));
  };

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

  return (
    <Card
      title='Create Email Template'
      className='h-100'
    >
      <Alert
        type='info'
        showIcon
        style={{ marginBottom: 16 }}
        message='Create presentation for the existing Subscription / Digest workflow'
        description='Creating, editing and previewing library content does not schedule a campaign or send email. Provider linkage is stored by the application; no delivery date is needed. Built-in defaults must be edited in their protected existing records. Existing Subscription campaigns remain separate; Order/Invoice remains application controlled.'
      />
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
              <Input />
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
              <Select
                options={options}
                className='w-100'
              />
            </Form.Item>
          </Col>
          <Col span={24}>
            <TextEditor languages={languages} form={form} lang={defaultLang} accountTemplate />
            {['verify', 'reset'].includes(selectedType) && (
              <Typography.Text type='secondary'>
                {t(
                  'email.template.placeholder.hint',
                  'Supported placeholder: $verify_code',
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

        </Row>
        <div className='flex-grow-1 d-flex flex-column justify-content-end'>
          <div className='pb-5'>
            <div
              className='d-flex flex-wrap align-items-center'
              style={{ gap: 8 }}
            >
              <EmailTemplatePreview
                form={form}
                type={selectedType}
                templateKey='new'
                previewable={selectedType !== 'order'}
              />
              <Button type='primary' htmlType='submit' loading={loadingBtn}>
                {t('save', 'Save template')}
              </Button>
            </div>
          </div>
        </div>
      </Form>
    </Card>
  );
};

export default MessageSubciribedAdd;
