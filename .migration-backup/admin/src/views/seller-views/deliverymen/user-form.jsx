import React from 'react';
import { Alert, Descriptions } from 'antd';
import { useSelector, shallowEqual } from 'react-redux';
import { useTranslation } from 'react-i18next';

const displayValue = (value) =>
  value === null || value === undefined || value === '' ? '--' : value;

const UserForm = () => {
  const { t } = useTranslation();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const driver = activeMenu.data || {};

  return (
    <>
      <Alert
        type='info'
        showIcon
        message='Account details are read-only'
        description='A driver account is shared across shops. Vendors cannot change identity, contact, verification, or password details here. Vehicle and delivery settings remain available in the Deliveryman tab.'
      />
      <Descriptions
        bordered
        size='small'
        column={{ xs: 1, sm: 2 }}
        style={{ marginTop: 16 }}
      >
        <Descriptions.Item label={t('firstname')}>
          {displayValue(driver.firstname)}
        </Descriptions.Item>
        <Descriptions.Item label={t('lastname')}>
          {displayValue(driver.lastname)}
        </Descriptions.Item>
        <Descriptions.Item label={t('phone')}>
          {displayValue(driver.phone)}
        </Descriptions.Item>
        <Descriptions.Item label={t('email')}>
          {displayValue(driver.email)}
        </Descriptions.Item>
        <Descriptions.Item label={t('birthday')}>
          {displayValue(driver.birthday)}
        </Descriptions.Item>
        <Descriptions.Item label={t('gender')}>
          {displayValue(driver.gender)}
        </Descriptions.Item>
      </Descriptions>
    </>
  );
};

export default UserForm;
