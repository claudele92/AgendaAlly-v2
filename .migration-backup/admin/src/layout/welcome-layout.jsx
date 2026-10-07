import React from 'react';
import { Navigate } from 'react-router-dom';
import { LEGACY_PUBLIC_ROUTE_REDIRECT } from '../configs/admin-startup.mjs';

export const WelcomeLayout = () => (
  <Navigate to={LEGACY_PUBLIC_ROUTE_REDIRECT} replace />
);
