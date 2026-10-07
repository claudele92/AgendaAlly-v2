import React from 'react';
import { useTranslation } from 'react-i18next';
import { Form, Input, Select } from 'antd';
import { locationLabel } from 'views/seller-views/staff/role-branch-selects';

const SERVICE_LOCATION_TYPE = 2;

// Shared by the admin, seller, and master booking-creation forms. A single
// authorized SERVICE location is registered as a hidden form value; multiple
// locations require an explicit choice. No locations keeps the legacy
// location-less flow.
const BookingBranchSelect = ({ shopLocations, disabled }) => {
  const { t } = useTranslation();
  const serviceLocations = (shopLocations || []).filter(
    (location) => location.type === SERVICE_LOCATION_TYPE,
  );

  if (serviceLocations.length === 0) {
    return null;
  }

  if (serviceLocations.length === 1) {
    return (
      <Form.Item name='shop_location_id' hidden>
        <Input />
      </Form.Item>
    );
  }

  return (
    <Form.Item
      name='shop_location_id'
      label={t('choose.a.branch')}
      rules={[{ required: true, message: t('required') }]}
    >
      <Select
        disabled={disabled}
        placeholder={t('choose.a.branch')}
        options={serviceLocations.map((location) => ({
          value: location.id,
          label: locationLabel(location, t),
        }))}
      />
    </Form.Item>
  );
};

export default BookingBranchSelect;
