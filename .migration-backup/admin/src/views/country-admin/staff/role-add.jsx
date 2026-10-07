import React from 'react';
import { shallowEqual, useSelector } from 'react-redux';
import RoleForm from './role-form';

export default function RoleAdd() {
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  return <RoleForm isEdit={false} countryId={activeMenu.data?.country_id} />;
}
