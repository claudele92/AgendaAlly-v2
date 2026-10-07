import { Card, Tag } from 'antd';
import React from 'react';

const UiType = () => (
  <Card title='STOREFRONT'>
    <section
      aria-label='AgendaAlly Marketplace is the active storefront'
      style={{
        alignItems: 'flex-start',
        display: 'flex',
        flexDirection: 'column',
        gap: 8,
      }}
    >
      <Tag color='green' style={{ margin: 0 }}>
        Active
      </Tag>
      <h2 style={{ margin: 0 }}>AgendaAlly Marketplace</h2>
      <p style={{ margin: 0 }}>Book local expertise. Shop local businesses.</p>
      <p role='note' style={{ margin: 0 }}>
        The approved AgendaAlly marketplace is the active customer storefront.
      </p>
    </section>
  </Card>
);

export default UiType;
