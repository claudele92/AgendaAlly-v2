import React from 'react';
import { shallowEqual, useSelector } from 'react-redux';
import RoleForm from './role-form';

export default function RoleEdit() {
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  return <RoleForm isEdit countryId={activeMenu.data?.country_id} />;
}
