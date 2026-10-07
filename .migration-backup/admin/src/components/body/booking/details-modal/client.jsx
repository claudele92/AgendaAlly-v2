import { Descriptions } from 'antd';
import { useTranslation } from 'react-i18next';
import React from 'react';

const ClientDescriptions = ({ data, userExtraData }) => {
  const { t } = useTranslation();
  const userPhone = userExtraData?.phone || data?.phone;

  return (
    <Descriptions className='aa-booking-description' bordered title={t('client')}>
      <Descriptions.Item label={t('id')} span={3}>
        {data?.id}
      </Descriptions.Item>
      <Descriptions.Item label={t('fullname')} span={3}>
        {userExtraData?.firstname +
          (userExtraData?.lastname && ` ${userExtraData?.lastname}`) ||
          data?.full_name ||
          'N/A'}
      </Descriptions.Item>
      <Descriptions.Item label={t('phone')} span={3}>
        {userPhone ? (
          <a href={`tel:${userPhone.startsWith('+') ? '' : '+'}${userPhone}`}>
            {userPhone.startsWith('+') ? '' : '+'}
            {userPhone}
          </a>
        ) : (
          'N/A'
        )}
      </Descriptions.Item>
      <Descriptions.Item label={t('email')} span={3}>
        {data?.email ? (
          <a href={`mailto:${data?.email}`}>{data?.email}</a>
        ) : (
          'N/A'
        )}
      </Descriptions.Item>
      {userExtraData?.zipcode && (
        <Descriptions.Item label={t('zipcode')} span={3}>
          {userExtraData?.zipcode}
        </Descriptions.Item>
      )}
      {userExtraData?.street_house_number && (
        <Descriptions.Item label={t('street.house.number')} span={3}>
          {userExtraData?.street_house_number}
        </Descriptions.Item>
      )}
      {userExtraData?.address && (
        <Descriptions.Item label={t('address')} span={3}>
          <a
            href={`https://www.google.com/maps/search/?api=1&query=${userExtraData?.lat},${userExtraData?.long}`}
            target='_blank'
            rel="noreferrer"
          >
            {userExtraData?.address}
          </a>
        </Descriptions.Item>
      )}
      {userExtraData?.address && (
        <Descriptions.Item label={t('additional.details')} span={3}>
          {userExtraData?.additional_details}
        </Descriptions.Item>
      )}
    </Descriptions>
  );
};

export default ClientDescriptions;
