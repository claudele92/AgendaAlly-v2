import React from 'react';
import { useTranslation } from 'react-i18next';
import { Form, Select } from 'antd';

// Shared by invite-modal.jsx (new invite) and staff-edit.jsx (editing an
// existing invite's role/branch) so both stay in sync rather than drifting
// apart as two copies of the same Select.

export function locationLabel(location, t) {
  const typeLabel = location.type === 2 ? t('service') : t('product');
  const place = [location.country?.translation?.title, location.city?.translation?.title]
    .filter(Boolean)
    .join(', ');
  return place ? `${typeLabel} — ${place}` : `${typeLabel} #${location.id}`;
}

export function RoleSelect({ shopRoles, value, onChange }) {
  const { t } = useTranslation();

  return (
    <Form.Item label={t('role')}>
      <Select
        allowClear
        placeholder={t('select.role')}
        value={value}
        onChange={onChange}
        options={shopRoles.map((role) => ({
          value: role.id,
          label: role.name,
        }))}
      />
    </Form.Item>
  );
}

// A single invitation can hold more than one branch — e.g. both the
// PRODUCT and SERVICE ShopLocation for the same city, structurally
// separate rows even though they represent "the same place" — so this is
// a multi-select; value/onChange are arrays of shop_location ids.
export function BranchSelect({ shopLocations, value, onChange }) {
  const { t } = useTranslation();

  if (!shopLocations?.length) {
    return null;
  }

  return (
    <Form.Item label={t('assign.to.branch')}>
      <Select
        mode='multiple'
        allowClear
        placeholder={t('assign.to.branch.optional')}
        value={value}
        onChange={onChange}
        options={shopLocations.map((location) => ({
          value: location.id,
          label: locationLabel(location, t),
        }))}
      />
    </Form.Item>
  );
}
