import React, { useEffect, useRef, useState } from 'react';
import { Alert, Button, Form, Modal, Segmented, Space, Typography } from 'antd';
import { useTranslation } from 'react-i18next';
import messageSubscriberService from '../../services/messageSubscriber';

const widths = [900, 390, 320];

const EmailTemplatePreview = ({
  form,
  type,
  templateKey,
  previewable = true,
  disabled = false,
}) => {
  const { t } = useTranslation();
  const subject = Form.useWatch('subject', form);
  const body = Form.useWatch('body', form);
  const altBody = Form.useWatch('alt_body', form);
  const [open, setOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [preview, setPreview] = useState(null);
  const [width, setWidth] = useState(900);
  const [view, setView] = useState('html');
  const requestSequence = useRef(0);

  useEffect(() => {
    requestSequence.current += 1;
    setPreview(null);
    setError('');
    setOpen(false);
  }, [type, subject, body, altBody, templateKey]);

  const requestPreview = () => {
    const values = form.getFieldsValue(['subject', 'body', 'alt_body']);
    setOpen(true);
    setPreview(null);
    setError('');
    setLoading(true);
    const sequence = ++requestSequence.current;
    messageSubscriberService
      .preview({
        type,
        subject: values.subject || '',
        body: values.body || '',
        alt_body: values.alt_body || '',
      })
      .then((response) => {
        const data = response?.data || response;
        if (
          !data ||
          typeof data.html !== 'string' ||
          typeof data.text !== 'string'
        ) {
          throw new Error(
            t(
              'email.preview.invalid.response',
              'Preview response was not usable.',
            ),
          );
        }
        if (sequence === requestSequence.current) setPreview(data);
      })
      .catch((requestError) => {
        if (sequence === requestSequence.current) {
          setError(
            requestError?.response?.data?.message ||
              requestError?.message ||
              t('email.preview.failed', 'The preview could not be generated.'),
          );
        }
      })
      .finally(() => {
        if (sequence === requestSequence.current) setLoading(false);
      });
  };

  return (
    <>
      <Button
        htmlType='button'
        onClick={requestPreview}
        disabled={disabled || !type || !previewable}
      >
        {t('email.template.preview', 'Preview')}
      </Button>
      <Modal
        title={t('email.template.preview', 'Email preview')}
        visible={open}
        onCancel={() => setOpen(false)}
        footer={null}
        width='min(1000px, calc(100vw - 24px))'
        destroyOnClose
      >
        <Space direction='vertical' size='middle' className='w-100'>
          <Typography.Text type='secondary'>
            {t(
              'email.preview.synthetic',
              'Synthetic preview only. No message is sent and no account state is changed.',
            )}
          </Typography.Text>
          <Space wrap className='w-100 justify-content-between'>
            <Segmented
              value={width}
              options={widths.map((value) => ({
                label: `${value}px`,
                value,
              }))}
              onChange={setWidth}
              aria-label={t('email.preview.width', 'Preview width')}
            />
            <Segmented
              value={view}
              options={[
                { label: t('email.preview.html', 'HTML'), value: 'html' },
                {
                  label: t('email.preview.text', 'Text alternative'),
                  value: 'text',
                },
              ]}
              onChange={setView}
            />
          </Space>
          {error && <Alert type='error' showIcon message={error} />}
          {loading ? (
            <div
              aria-label={t('loading')}
              style={{
                height: 300,
                borderRadius: 6,
                background:
                  'linear-gradient(90deg, var(--skeleton, #f0f2f5) 25%, #fafafa 38%, var(--skeleton, #f0f2f5) 63%)',
                backgroundSize: '400% 100%',
              }}
            />
          ) : preview ? (
            view === 'html' ? (
              <div
                style={{
                  width: '100%',
                  overflow: 'auto',
                  padding: 12,
                  background: 'var(--background, #f4f6f8)',
                  borderRadius: 6,
                }}
              >
                <iframe
                  title={t('email.preview.html', 'Email HTML preview')}
                  sandbox=''
                  srcDoc={preview.html}
                  style={{
                    display: 'block',
                    width: `${width}px`,
                    maxWidth: '100%',
                    height: 520,
                    margin: '0 auto',
                    border: '1px solid #d9d9d9',
                    borderRadius: 4,
                    background: '#fff',
                  }}
                />
              </div>
            ) : (
              <pre
                style={{
                  maxHeight: 520,
                  overflow: 'auto',
                  whiteSpace: 'pre-wrap',
                  overflowWrap: 'anywhere',
                  padding: 18,
                  borderRadius: 6,
                  background: 'var(--background, #f4f6f8)',
                  color: 'var(--text)',
                  fontFamily: 'monospace',
                }}
              >
                {preview.text}
              </pre>
            )
          ) : (
            !error && (
              <Alert
                type='info'
                showIcon
                message={t(
                  'email.preview.pending',
                  'Request a preview to see this wording.',
                )}
              />
            )
          )}
          {preview?.placeholders?.length > 0 && (
            <Typography.Text type='secondary'>
              {t('email.preview.sample.values', 'Sample values')}:{' '}
              {JSON.stringify(preview.sample_values)}
            </Typography.Text>
          )}
        </Space>
      </Modal>
    </>
  );
};

export default EmailTemplatePreview;
