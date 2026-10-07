// ** React Imports
import { lazy } from 'react';

const CountryAdminRoutes = [
  {
    path: 'country-admins',
    component: lazy(() => import('views/country-admins')),
  },
  {
    path: 'country-admins/add',
    component: lazy(() => import('views/country-admins/country-admin-add')),
  },
];

export default CountryAdminRoutes;
