import { lazy } from 'react';

const SellerServiceExtraRoutes = [
  {
    path: 'seller/service-extra',
    component: lazy(() => import('views/seller-views/service-extra')),
  },
  {
    path: 'seller/service-extra/add',
    component: lazy(() => import('views/seller-views/service-extra/create')),
  },
  {
    path: 'seller/service-extra/:id',
    component: lazy(() => import('views/seller-views/service-extra/edit')),
  },
];

export default SellerServiceExtraRoutes;
