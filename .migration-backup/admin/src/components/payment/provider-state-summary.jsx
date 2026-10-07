import React from 'react';
import { Tag, Typography } from 'antd';

// Safe server metadata only. Configuration never promises checkout activation.
export default function ProviderStateSummary({ state }) {
  if (!state) return null;
  return (
    <div>
      <Tag>Capability: {state.capability_state}</Tag>
      <Tag>Merchant: {state.configuration_state}</Tag>
      <Tag>Runtime: {state.runtime_state}</Tag>
      <Tag color={state.checkout_available ? 'green' : 'default'}>
        {state.checkout_state}
      </Tag>
      <Typography.Text type='secondary'>
        {state.configuration?.owner_type === 'shop' ? 'Shop merchant' : 'Platform merchant'}
        {state.reasons?.length ? ` — ${state.reasons.map((reason) => reason.replaceAll('_', ' ')).join('; ')}` : ''}
      </Typography.Text>
    </div>
  );
}