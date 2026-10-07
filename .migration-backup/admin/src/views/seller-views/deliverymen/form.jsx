import React, { useState } from 'react';
import { Tabs } from 'antd';
import { useTranslation } from 'react-i18next';
import UserForm from './user-form';
import DeliverymanForm from './deliveryman-form';
import { useParams } from 'react-router-dom';

const { TabPane } = Tabs;

const FormDeliveryman = () => {
  const { t } = useTranslation();
  const { uuid } = useParams();

  return (
    <>
      {uuid === undefined ? (
        <UserForm />
      ) : (
        <Tabs tabPosition='left' size='small'>
          <TabPane key='user' tab={t('user')}>
            <UserForm />
          </TabPane>
          <TabPane key='deliveryman' tab={t('deliveryman')}>
            <DeliverymanForm />
          </TabPane>
        </Tabs>
      )}
    </>
  );
};

export default FormDeliveryman;
