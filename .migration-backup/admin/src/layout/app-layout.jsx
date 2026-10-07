import { useCallback, useEffect, Suspense } from 'react';
import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { Alert, Layout } from 'antd';
import Sidebar from 'components/sidebar';
import ChatIcons from 'views/chat/chat-icons';
import Footer from 'components/footer';
import languagesService from 'services/languages';
import { setLangugages } from 'redux/slices/formLang';
import { fetchAllShops } from 'redux/slices/allShops';
import {
  fetchRestCurrencies,
  setCurrencySession,
} from 'redux/slices/currency';
import Loading from 'components/loading';
import { fetchMyShop } from 'redux/slices/myShop';
import SubscriptionsDate from 'components/subscriptions-date';
import Header from 'components/header';
import ParcelFloat from '../views/parcel-order/parcel-float';
import request from 'services/request';
import { NavigationScopeProvider, useNavigationScope } from 'context/navigation-scope';
import {
  PRODUCT_DISABLED_MENU_NAMES,
  resolveStage1DashboardDestination,
} from 'configs/navigation.mjs';
import { data as nativeMenus } from 'configs/menu-config';
import { resolveAppLayoutBootstrapPlan } from './app-layout-bootstrap.mjs';
const { Content } = Layout;

const AppLayout = () => {
  const { user } = useSelector((state) => state.auth, shallowEqual);

  const fetchNavigationScope = useCallback(async () => {
    const response = await request.get('dashboard/user/navigation-context');
    if (
      !response?.data ||
      typeof response.data !== 'object' ||
      Array.isArray(response.data)
    ) {
      throw new Error('Navigation scope response is missing its data object.');
    }
    return response.data;
  }, []);

  return (
    <NavigationScopeProvider
      userId={user?.id}
      role={user?.role}
      sessionKey={user?.token}
      fetchScope={fetchNavigationScope}
    >
      <AppLayoutContents user={user} />
    </NavigationScopeProvider>
  );
};

const AppLayoutContents = ({ user }) => {
  const dispatch = useDispatch();
  const location = useLocation();
  const { languages } = useSelector((state) => state.formLang, shallowEqual);
  const { direction, navCollapsed, parcelMode } = useSelector(
    (state) => state.theme.theme,
    shallowEqual,
  );
  const { products_enabled } = useSelector(
    (state) => state.globalSettings.settings,
    shallowEqual,
  );
  const navigationScope = useNavigationScope();
  const bootstrapPlan = resolveAppLayoutBootstrapPlan(user, navigationScope);

  useEffect(() => {
    if (!languages.length) {
      languagesService.getAllActive().then(({ data }) => {
        dispatch(setLangugages(data));
      });
    }
  }, [dispatch, languages.length]);

  useEffect(() => {
    // The reducer preserves a catalog/request for this exact actor session,
    // while clearing it immediately when the account, role, or token changes.
    dispatch(setCurrencySession(user));
    dispatch(fetchRestCurrencies({}));
  }, [dispatch, user?.id, user?.role, user?.token]);

  useEffect(() => {
    const body = {
      page: 1,
      perPage: 1,
      status: 'approved',
    };
    if (bootstrapPlan.fetchAdminShops) {
      dispatch(fetchAllShops(body));
    }
    if (bootstrapPlan.fetchMyShop) {
      dispatch(fetchMyShop());
    }
  }, [
    bootstrapPlan.fetchAdminShops,
    bootstrapPlan.fetchMyShop,
    dispatch,
    user?.id,
    user?.role,
    user?.token,
  ]);

  const getLayoutGutter = () => (navCollapsed ? 72 : 250);

  const getLayoutDirectionGutter = () => {
    if (direction === 'ltr') {
      return { paddingLeft: getLayoutGutter(), minHeight: '100vh' };
    }
    if (direction === 'rtl') {
      return { paddingRight: getLayoutGutter(), minHeight: '100vh' };
    }
    return { paddingLeft: getLayoutGutter() };
  };

  const currentPath = location.pathname.replace(/^\/+|\/+$/g, '');
  let outlet = <Outlet />;
  if (currentPath === 'dashboard') {
    if (navigationScope.status === 'error') {
      outlet = (
        <Alert
          type='error'
          showIcon
          message='Dashboard access could not be verified.'
          description='Refresh the page to retry account-scope verification.'
        />
      );
    } else if (
      navigationScope.status !== 'ready' ||
      navigationScope.sessionMatches !== true
    ) {
      outlet = <Loading />;
    } else {
      const sourceMenus =
        user?.role === 'admin' && parcelMode
          ? nativeMenus.parcel || []
          : nativeMenus[user?.role] || [];
      const disabledMenuNames = Number(products_enabled)
        ? []
        : PRODUCT_DISABLED_MENU_NAMES;
      const dashboardDestination = resolveStage1DashboardDestination({
        sourceMenus,
        role: user?.role,
        context: navigationScope.scope,
        authenticatedUserId: user?.id,
        isSuperAdmin:
          (user?.role === 'admin' || user?.role === 'manager') &&
          navigationScope.scope?.is_super_admin === true,
        disabledMenuNames,
      });

      if (dashboardDestination.dashboardAuthorized) {
        outlet = <Outlet />;
      } else if (dashboardDestination.destinationUrl) {
        outlet = (
          <Navigate
            to={`/${dashboardDestination.destinationUrl}`}
            replace
          />
        );
      } else {
        outlet = (
          <Alert
            type='warning'
            showIcon
            message={
              dashboardDestination.authorizationKnown
                ? 'No authorized workspace pages are available.'
                : 'Authorized workspace access could not be verified.'
            }
          />
        );
      }
    }
  }

  return (
    <Layout className='app-container'>
      <Sidebar />
      <Layout className='app-layout' style={getLayoutDirectionGutter()}>
        <Header />
        {/*<TabMenu />*/}
        <Content
          className='app-main-content'
          style={{ flex: '1 1 auto', minWidth: 0 }}
        >
          <Suspense fallback={<Loading />}>
            <SubscriptionsDate />
            {outlet}
          </Suspense>
          {currentPath === 'seller/calendar' && user?.role === 'seller' && (
            <div className='aa-calendar-help-row'><ChatIcons /></div>
          )}
        </Content>
        <Footer />
      </Layout>
      {user?.role === 'admin' && <ParcelFloat />}
      {(user?.role === 'admin' ||
        (user?.role === 'seller' && currentPath !== 'seller/calendar') ||
        user?.role === 'deliveryman') && <ChatIcons />}
    </Layout>
  );
};

export default AppLayout;
