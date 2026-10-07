import { useDispatch, useSelector, shallowEqual } from 'react-redux';
import { MenuOutlined } from '@ant-design/icons';
import { useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { data } from 'configs/menu-config';
import { navCollapseTrigger } from 'redux/slices/theme';
import { useNavigationScope } from 'context/navigation-scope';
import {
  findActiveNavigationItem,
  formatVerifiedNavigationScope,
} from 'configs/navigation.mjs';
import 'assets/scss/components/header.scss';
import 'components/sidebar/navigation.scss';
import ThemeChanger from './theme-changer';
import NotificationsIndicator from './notifications-indicator';
import LanguageChanger from './language-changer';
import Profile from './profile';

const Header = () => {
  const dispatch = useDispatch();
  const { t } = useTranslation();
  const { pathname } = useLocation();
  const { user } = useSelector((state) => state.auth, shallowEqual);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { navCollapsed, parcelMode } = useSelector(
    (state) => state.theme.theme,
    shallowEqual,
  );
  const navigationScope = useNavigationScope();
  const roleMenus =
    user?.role === 'admin' && parcelMode
      ? data.parcel || []
      : data[user?.role] || [];
  const routeMenu = findActiveNavigationItem(roleMenus, pathname);
  const rawScope =
    navigationScope.status === 'ready' ? navigationScope.scope : null;
  const verifiedScope =
    rawScope &&
    rawScope.scope_status !== 'unknown' &&
    String(rawScope.user_id) === String(user?.id) &&
    rawScope.role === user?.role
      ? formatVerifiedNavigationScope(rawScope)
      : null;

  return (
    <div className='header aa-stage1-header agendaally-stage1-nav'>
      <div className='aa-stage1-header-left'>
        <button
          type='button'
          className='aa-stage1-mobile-menu'
          aria-label={t('toggle.navigation', 'Toggle navigation')}
          aria-expanded={!navCollapsed}
          aria-controls='business-sidebar'
          onClick={() => dispatch(navCollapseTrigger())}
        >
          <MenuOutlined aria-hidden='true' />
        </button>
        <div className='aa-stage1-breadcrumb'>
          <span>{t('workspace', 'Workspace')}</span>
          <span aria-hidden='true'>›</span>
          <strong aria-current='page'>
            {routeMenu?.name || activeMenu?.name
              ? t(routeMenu?.name || activeMenu.name)
              : t('dashboard')}
          </strong>
        </div>
      </div>
      {verifiedScope && (
        <div className='aa-stage1-header-context'>
          <span className='aa-stage1-context-dot' aria-hidden='true' />
          <span>{verifiedScope}</span>
        </div>
      )}
      <div className='wrapper'>
        <ThemeChanger />
        <NotificationsIndicator />
        <LanguageChanger />
        <Profile />
      </div>
    </div>
  );
};

export default Header;
