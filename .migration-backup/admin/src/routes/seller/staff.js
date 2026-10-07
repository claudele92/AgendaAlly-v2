import { lazy } from 'react';

const SellerStaffRoutes = [
  {
    path: 'seller/staff',
    component: lazy(() => import('views/seller-views/staff')),
  },
  {
    path: 'seller/staff/roles/add',
    component: lazy(() => import('views/seller-views/staff/role-add')),
  },
  {
    path: 'seller/staff/roles/edit/:id',
    component: lazy(() => import('views/seller-views/staff/role-edit')),
  },
  {
    path: 'seller/staff/edit/:id',
    component: lazy(() => import('views/seller-views/staff/staff-edit')),
  },
];

export default SellerStaffRoutes;
