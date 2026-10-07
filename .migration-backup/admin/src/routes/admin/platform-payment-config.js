// ** React Imports
import { lazy } from 'react';

const PlatformPaymentConfigRoutes = [
  {
    path: 'platform-payment-configs',
    component: lazy(() => import('views/platform-payment-configs')),
  },
  {
    path: 'platform-payment-configs/add',
    component: lazy(() =>
      import('views/platform-payment-configs/platform-payment-config-add'),
    ),
  },
  {
    path: 'platform-payment-configs/:id',
    component: lazy(() =>
      import('views/platform-payment-configs/platform-payment-config-edit'),
    ),
  },
];

export default PlatformPaymentConfigRoutes;
