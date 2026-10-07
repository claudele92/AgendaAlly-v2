import { Alert } from 'antd';
import Loading from 'components/loading';
import PageLoading from 'components/pageLoading';
import i18n from 'configs/i18next';
import {
  ADMIN_RUNTIME_CONFIG_ERROR,
  ADMIN_RUNTIME_CONFIG_VALID,
} from 'configs/app-global';
import { LEGACY_PUBLIC_ROUTE_REDIRECT } from 'configs/admin-startup.mjs';
import { PathLogout } from 'context/path-logout';
import { ProtectedRoute } from 'context/protected-route';
import AppLayout from 'layout/app-layout';
import Providers from 'providers';
import { Suspense, useEffect, useState } from 'react';
import {
  Route,
  BrowserRouter as Router,
  Routes,
  Navigate,
} from 'react-router-dom';
import { ToastContainer } from 'react-toastify';
import { AllRoutes } from 'routes';
import informationService from 'services/rest/information';
import Login from 'views/login';
import { resolveAgendaAllyBrandAsset } from 'helpers/agendaallyBrandAssets';
import DeliveryDriverInvitation from 'views/delivery-driver-invitation';
import NotFound from 'views/not-found';
import restSettingsService from './services/rest/settings';
import { initializeTranslations } from './configs/i18next-bootstrap.mjs';
import { THEME_CONFIG } from './configs/theme-config';
import {
  fetchRestSettings,
  fetchSettings,
} from './redux/slices/globalSettings';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';

const App = () => {
  const dispatch = useDispatch();
  const [translationsReady, setTranslationsReady] = useState(false);
  const [translationError, setTranslationError] = useState('');
  const { user } = useSelector((state) => state.auth, shallowEqual);

  const fetchAndSetFavicon = () => {
    restSettingsService.getAll().then((res) => {
      console.log('res', res);
      const favicon = res.data.find((item) => item.key === 'admin_favicon');
      if (favicon) {
        const link =
          document.querySelector("link[rel*='icon']") ||
          document.createElement('link');
        link.type = 'image/png';
        link.rel = 'shortcut icon';
        link.href = resolveAgendaAllyBrandAsset(favicon.value, 'mark');
        document.getElementsByTagName('head')[0].appendChild(link);
      }
    });
  };

  const fetchUserSettings = (role) => {
    switch (role) {
      case 'admin':
        dispatch(fetchSettings({}));
        break;
      case 'seller':
        dispatch(fetchRestSettings({ seller: true }));
        break;
      default:
        dispatch(fetchRestSettings({}));
    }
  };

  useEffect(() => {
    if (!ADMIN_RUNTIME_CONFIG_VALID) return;

    initializeTranslations({
      i18n,
      defaultLanguage: THEME_CONFIG.locale,
      fetchTranslations: (lang) => informationService.translations({ lang }),
    })
      .catch((error) => {
        const failureMessage =
          error?.response?.status
            ? `HTTP ${error.response.status}`
            : error?.message || String(error);
        console.error('Unable to initialize application translations:', failureMessage);
        setTranslationError(failureMessage);
      })
      .finally(() => setTranslationsReady(true));
    fetchAndSetFavicon();
    fetchUserSettings(user?.role || '');
  }, []);

  // ParcelFloat

  if (!ADMIN_RUNTIME_CONFIG_VALID) {
    return (
      <Alert
        message={ADMIN_RUNTIME_CONFIG_ERROR}
        showIcon
        type='error'
        role='alert'
      />
    );
  }

  if (!translationsReady) {
    return <PageLoading />;
  }

  return (
    <Providers>
      <Router>
        {translationError && (
          <Alert
            message={translationError}
            showIcon
            type='error'
            role='alert'
          />
        )}
        <Routes>
          <Route
            index
            path='/login'
            element={
              <PathLogout>
                <Login />
              </PathLogout>
            }
          />
          <Route
            path='/delivery-driver-invitation'
            element={<DeliveryDriverInvitation />}
          />
          <Route
            path='/welcome'
            element={<Navigate to={LEGACY_PUBLIC_ROUTE_REDIRECT} replace />}
          />
          <Route
            path='/installation'
            element={<Navigate to={LEGACY_PUBLIC_ROUTE_REDIRECT} replace />}
          />
          <Route
            path=''
            element={
              <ProtectedRoute>
                <AppLayout />
              </ProtectedRoute>
            }
          >
            <Route path='/' element={<Navigate to='dashboard' />} />
            {AllRoutes.map(({ path, component: Component }) => (
              <Route key={path} path={path} element={<Component />} />
            ))}
          </Route>
          <Route
            path='*'
            element={
              <Suspense fallback={<Loading />}>
                <NotFound />
              </Suspense>
            }
          />
        </Routes>
        <ToastContainer
          className='antd-toast'
          position='top-right'
          autoClose={2500}
          hideProgressBar
          closeOnClick
          pauseOnHover
          draggable
        />
      </Router>
    </Providers>
  );
};
export default App;
