import React from 'react';
import { Alert, Card, Descriptions, Divider, Tag, Typography } from 'antd';

const BALANCE_FIELDS = [
  ['gross', 'Gross'],
  ['original_gross', 'Original gross'],
  ['quoted_gross', 'Quoted gross'],
  ['commission', 'Commission'],
  ['original_commission', 'Original commission'],
  ['quoted_commission', 'Quoted commission'],
  ['platform_principal', 'Platform principal'],
  ['vendor_principal', 'Vendor principal'],
  ['vendor_payable', 'Vendor payable'],
  ['commission_receivable', 'Commission receivable'],
  ['refunded', 'Refunded'],
  ['funding_returned', 'Funding returned'],
  ['commission_satisfied', 'Commission satisfied'],
  ['vendor_entitlement', 'Vendor entitlement'],
];

function formatExactMoney(units, scale) {
  if (typeof units !== 'string' || !/^-?\d+$/.test(units)) return 'Unavailable';

  const decimalPlaces = Number(scale);
  if (!Number.isInteger(decimalPlaces) || decimalPlaces < 0 || decimalPlaces > 100) {
    return `${units} units`;
  }

  const negative = units.startsWith('-');
  const digits = (negative ? units.slice(1) : units).padStart(decimalPlaces + 1, '0');
  const sign = negative ? '-' : '';
  if (decimalPlaces === 0) return `${sign}${digits}`;

  const splitAt = digits.length - decimalPlaces;
  return `${sign}${digits.slice(0, splitAt)}.${digits.slice(splitAt)}`;
}

function displayValue(value) {
  return value === undefined || value === null || value === '' ? 'Not reported' : String(value);
}

export default function CanonicalFinancePanel({ canonicalFinance }) {
  if (!canonicalFinance || typeof canonicalFinance !== 'object') {
    return (
      <Alert
        type='info'
        showIcon
        message='Canonical finance evidence unavailable'
        description='Evidence may be legacy, not retained, or unavailable to this view. No balances are inferred.'
        className='mt-3'
      />
    );
  }

  const {
    allocation_id,
    shop_id,
    state,
    currency_code,
    money_scale,
    balances = {},
    contributions = [],
    payment_verified,
    external_settlement_status,
    refund_status,
    payout_status,
    commission_receivable_state,
  } = canonicalFinance;

  return (
    <Card size='small' title='Canonical finance' className='mt-3'>
      <Typography.Paragraph type='secondary' className='mb-3'>
        Retained allocation evidence in {displayValue(currency_code)}. Amounts are exact native units formatted at scale{' '}
        {displayValue(money_scale)}; currencies are not aggregated. Application payment verification does not verify external settlement.
      </Typography.Paragraph>
      <Descriptions bordered size='small' column={2}>
        <Descriptions.Item label='Allocation'>{displayValue(allocation_id)}</Descriptions.Item>
        <Descriptions.Item label='Shop'>{displayValue(shop_id)}</Descriptions.Item>
        <Descriptions.Item label='Allocation state' span={2}>
          <Tag>{displayValue(state)}</Tag>
        </Descriptions.Item>
        <Descriptions.Item label='Payment verification'>
          <Tag color={payment_verified === true ? 'green' : 'default'}>
            {payment_verified === true ? 'Verified in application' : 'Not verified'}
          </Tag>
        </Descriptions.Item>
        <Descriptions.Item label='External settlement'>
          <Tag color='default'>{displayValue(external_settlement_status)}</Tag>
        </Descriptions.Item>
        <Descriptions.Item label='Refund status'>
          <Tag>{displayValue(refund_status)}</Tag>
        </Descriptions.Item>
        <Descriptions.Item label='Payout status'>
          <Tag>{displayValue(payout_status)}</Tag>
        </Descriptions.Item>
        <Descriptions.Item label='Commission receivable state' span={2}>
          <Tag>{displayValue(commission_receivable_state)}</Tag>
        </Descriptions.Item>
      </Descriptions>

      <Divider orientation='left' plain>
        Native allocation balances
      </Divider>
      <Descriptions bordered size='small' column={2}>
        {BALANCE_FIELDS.map(([key, label]) => (
          <Descriptions.Item label={label} key={key}>
            {formatExactMoney(balances?.[key], money_scale)} {displayValue(currency_code)}
          </Descriptions.Item>
        ))}
      </Descriptions>

      <Divider orientation='left' plain>
        Payment contributions
      </Divider>
      {Array.isArray(contributions) && contributions.length > 0 ? (
        contributions.map((contribution, index) => (
          <Descriptions
            bordered
            size='small'
            column={2}
            key={`${contribution?.provider || 'provider'}-${index}`}
            className='mb-2'
          >
            <Descriptions.Item label='Provider'>{displayValue(contribution?.provider)}</Descriptions.Item>
            <Descriptions.Item label='Collection mode'>
              {displayValue(contribution?.collection_mode)}
            </Descriptions.Item>
            <Descriptions.Item label='Custody'>
              <Tag>{displayValue(contribution?.custody)}</Tag>
            </Descriptions.Item>
            <Descriptions.Item label='Contribution state'>
              <Tag>{displayValue(contribution?.state)}</Tag>
            </Descriptions.Item>
            <Descriptions.Item label='Provider reference' span={2}>
              {displayValue(contribution?.provider_reference)}
            </Descriptions.Item>
          </Descriptions>
        ))
      ) : (
        <Typography.Text type='secondary'>No retained allocation contributions.</Typography.Text>
      )}
    </Card>
  );
}