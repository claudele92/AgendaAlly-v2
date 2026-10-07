import { Navigate } from 'react-router-dom';
import { useSelector } from 'react-redux/es/hooks/useSelector';
import { shallowEqual } from 'react-redux';
import { getAuthenticatedDestination } from '../configs/admin-startup.mjs';

export const PathLogout = ({ children }) => {
  const { user } = useSelector((state) => state.auth, shallowEqual);
  const menuActive = useSelector((list) => list.menu.activeMenu, shallowEqual);

  if (user) {
    return <Navigate to={getAuthenticatedDestination(menuActive)} replace />;
  }

  return children;
};
