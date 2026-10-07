import React from 'react';
import { Navigate } from 'react-router-dom';
import { LEGACY_PUBLIC_ROUTE_REDIRECT } from '../../configs/admin-startup.mjs';

export default function Welcome() {
  return <Navigate to={LEGACY_PUBLIC_ROUTE_REDIRECT} replace />;
}
