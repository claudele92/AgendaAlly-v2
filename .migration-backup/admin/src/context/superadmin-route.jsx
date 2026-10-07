import React from 'react';
import { shallowEqual, useSelector } from 'react-redux';
import { Navigate } from 'react-router-dom';

/**
 * Wraps a superadmin-only page's content. A country-scoped admin/manager
 * shares the same 'admin'/'manager' role string as a global superadmin, so
 * nav-hiding alone (menu-config's `superadminOnly` flag) can't stop one from
 * navigating to the URL directly — this is the actual client-side guard,
 * on top of the backend's own self-guard 403 on these endpoints.
 */
export const SuperAdminRoute = ({ children }) => {
  const { user } = useSelector((state) => state.auth, shallowEqual);

  if (!user?.isSuperAdmin) {
    return <Navigate to='/' replace />;
  }
  return children;
};
