import React from 'react';
import { Col, Form, Input } from 'antd';
import { useTranslation } from 'react-i18next';

/**
 * Orange Money / MTN Mobile Money credential fields, shared by every
 * payment-gateway-credentials form in the app (seller ShopPayment,
 * platform PlatformPaymentConfig) — field sets mirror each gateway's
 * backend requiredIf() rules exactly (see ShopPayment/StoreRequest and
 * UpdateRequest, PlatformPaymentConfig/StoreRequest and UpdateRequest).
 * Must be rendered inside the consuming form's own <Form> — these are
 * bare <Form.Item>s, not a form of their own.
 *
 * Credential values are never prefilled from a ShopPayment response.
 * On edit, blank credential fields are omitted from the update request and
 * preserve existing configuration; `configured` carries only safe booleans.
 */
export default function GatewayCredentialFields({
  tag,
  isEdit = false,
  configured = {},
  disabled = false,
  allowBlankTargetEnvironment = false,
}) {
  const { t } = useTranslation();

  const credentialRules = (key) => [
    {
      required: !(isEdit && configured[key]),
      message: t('required'),
    },
  ];

  const unchangedHint = (key) =>
    isEdit && configured[key] ? t('leave.blank.to.keep.current.value') : undefined;

  if (tag === 'orange') {
    return (
      <>
        <Col span={12}>
          <Form.Item
            label={t('client.id')}
            name='client_id'
            extra={unchangedHint('client_id')}
            rules={credentialRules('client_id')}
          >
            <Input.Password disabled={disabled} autoComplete='new-password' />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('merchant.key')}
            name='merchant_key'
            extra={unchangedHint('merchant_key')}
            rules={credentialRules('merchant_key')}
          >
            <Input.Password disabled={disabled} autoComplete='new-password' />
          </Form.Item>
        </Col>
      </>
    );
  }

  if (tag === 'mtn') {
    return (
      <>
        <Col span={12}>
          <Form.Item
            label={t('subscription.key')}
            name='subscription_key'
            extra={unchangedHint('subscription_key')}
            rules={credentialRules('subscription_key')}
          >
            <Input.Password disabled={disabled} autoComplete='new-password' />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('api.user')}
            name='api_user'
            extra={unchangedHint('api_user')}
            rules={credentialRules('api_user')}
          >
            <Input.Password disabled={disabled} autoComplete='new-password' />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('api.key')}
            name='api_key'
            extra={unchangedHint('api_key')}
            rules={credentialRules('api_key')}
          >
            <Input.Password disabled={disabled} autoComplete='new-password' />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item
            label={t('target.environment')}
            name='target_environment'
            rules={[
              {
                required: !isEdit || !allowBlankTargetEnvironment,
                message: t('required'),
              },
              {
                pattern: /^[a-z]+$/,
                message: t('must.be.lowercase.letters.only'),
              },
            ]}
          >
            <Input disabled={disabled} />
          </Form.Item>
        </Col>
        <Col span={12}>
          <Form.Item name='base_url' label='Provider HTTPS endpoint' rules={[{ type: 'url', message: t('invalid.url') }]}>
            <Input disabled={disabled} placeholder='Required for a non-sandbox merchant' />
          </Form.Item>
        </Col>
      </>
    );
  }

  return null;
}
