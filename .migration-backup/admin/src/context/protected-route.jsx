import React from 'react';
import { shallowEqual, useSelector } from 'react-redux';
import { Navigate } from 'react-router-dom';
import { getAuthRouteDestination } from '../configs/admin-startup.mjs';

export const ProtectedRoute = ({ children }) => {
  const { user } = useSelector((state) => state.auth, shallowEqual);

  if (!user) {
    return <Navigate to={getAuthRouteDestination(user)} replace />;
  }
  return children;
};
