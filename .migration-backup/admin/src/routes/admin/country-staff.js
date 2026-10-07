// ** React Imports
import { lazy } from 'react';

const CountryStaffRoutes = [
  {
    path: 'country-admin/staff',
    component: lazy(() => import('views/country-admin/staff')),
  },
  {
    path: 'country-admin/staff/roles/add',
    component: lazy(() => import('views/country-admin/staff/role-add')),
  },
  {
    path: 'country-admin/staff/roles/edit/:id',
    component: lazy(() => import('views/country-admin/staff/role-edit')),
  },
];

export default CountryStaffRoutes;
