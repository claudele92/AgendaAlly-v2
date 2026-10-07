import React from 'react';
import { useTranslation } from 'react-i18next';
import { Card, Tabs, Typography } from 'antd';
import RolesList from './roles-list';
import StaffList from './staff-list';

const { TabPane } = Tabs;

export default function Staff() {
  const { t } = useTranslation();

  return (
    <Card>
      <Typography.Title
        level={1}
        style={{
          color: 'var(--text)',
          fontSize: '20px',
          fontWeight: 500,
          padding: 0,
          margin: 0,
        }}
      >
        {t('staff')}
      </Typography.Title>
      <Tabs defaultActiveKey='staff' type='card' className='mt-3'>
        <TabPane tab={t('staff')} key='staff'>
          <StaffList />
        </TabPane>
        <TabPane tab={t('roles')} key='roles'>
          <RolesList />
        </TabPane>
      </Tabs>
    </Card>
  );
}
