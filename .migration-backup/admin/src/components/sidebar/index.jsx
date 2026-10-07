import { useEffect, useMemo, useRef, useState } from 'react';
import { useLocation } from 'react-router-dom';
import { SearchOutlined } from '@ant-design/icons';
import { Layout, Input } from 'antd';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import Scrollbars from 'react-custom-scrollbars';
import { data } from 'configs/menu-config';
import {
  effectiveNavigationMenuIds,
  findActiveNavigationItem,
  formatVerifiedNavigationScope,
  PRODUCT_DISABLED_MENU_NAMES,
  resolveNavigation,
} from 'configs/navigation.mjs';
import { useNavigationScope } from 'context/navigation-scope';
import { setNavCollapsed } from 'redux/slices/theme';
import SidebarHeader from './header';
import MenuList from './menu-list';
import './navigation.scss';

const { Sider } = Layout;

const Sidebar = () => {
  const { t } = useTranslation();
  const { pathname } = useLocation();
  const dispatch = useDispatch();
  const searchRef = useRef(null);
  const [searchTerm, setSearchTerm] = useState('');
  const [isMobile, setIsMobile] = useState(false);
  const { user } = useSelector((state) => state.auth, shallowEqual);
  const { navCollapsed, parcelMode } = useSelector(
    (state) => state.theme.theme,
    shallowEqual,
  );
  const { products_enabled } = useSelector(
    (state) => state.globalSettings.settings,
    shallowEqual,
  );
  const navigationScope = useNavigationScope();
  const rawScope = navigationScope.status === 'ready' ? navigationScope.scope : null;
  const scope =
    rawScope &&
    rawScope.scope_status !== 'unknown' &&
    String(rawScope.user_id) === String(user?.id) &&
    rawScope.role === user?.role
      ? rawScope
      : null;

  useEffect(() => {
    const media = window.matchMedia('(max-width: 991px)');
    const updateMobile = (event) => {
      setIsMobile(event.matches);
      if (event.matches) dispatch(setNavCollapsed(true));
    };
    updateMobile(media);
    media.addEventListener?.('change', updateMobile);
    return () => media.removeEventListener?.('change', updateMobile);
  }, [dispatch]);

  useEffect(() => {
    const onKeyDown = (event) => {
      if (
        event.key === '/' &&
        !event.defaultPrevented &&
        !event.metaKey &&
        !event.ctrlKey &&
        !event.altKey &&
        !['INPUT', 'TEXTAREA', 'SELECT'].includes(
          document.activeElement?.tagName,
        )
      ) {
        event.preventDefault();
        if (navCollapsed) dispatch(setNavCollapsed(false));
        window.requestAnimationFrame(() => searchRef.current?.focus());
      }
      if (event.key === 'Escape' && isMobile && !navCollapsed) {
        dispatch(setNavCollapsed(true));
        window.requestAnimationFrame(() =>
          document.querySelector('.aa-stage1-mobile-menu')?.focus(),
        );
      }
    };
    window.addEventListener('keydown', onKeyDown);
    return () => window.removeEventListener('keydown', onKeyDown);
  }, [dispatch, isMobile, navCollapsed]);

  useEffect(() => {
    if (isMobile && !navCollapsed) {
      window.requestAnimationFrame(() => searchRef.current?.focus());
    }
  }, [isMobile, navCollapsed]);

  const sourceMenus = useMemo(() => {
    if (!user?.role) return [];
    if (user.role === 'admin' && parcelMode) return data.parcel || [];
    return data[user.role] || [];
  }, [parcelMode, user?.role]);

  const disabledMenuNames = Number(products_enabled)
    ? []
    : PRODUCT_DISABLED_MENU_NAMES;
  const resolvedMenus = useMemo(() => {
    const authorizedMenuIds = effectiveNavigationMenuIds(
      sourceMenus,
      user?.role,
      scope,
      user?.id,
    );
    return resolveNavigation({
      sourceMenus,
      authorizedMenuIds,
      isSuperAdmin:
        (user?.role === 'admin' || user?.role === 'manager') &&
        scope?.is_super_admin === true,
      disabledMenuNames,
      role: user?.role,
    });
  }, [disabledMenuNames, scope, sourceMenus, user?.id, user?.role]);

  const active = findActiveNavigationItem(resolvedMenus.items, pathname);
  const safeHome =
    resolvedMenus.items[0]?.menus?.find((item) => item.name === 'dashboard') ||
    resolvedMenus.items[0]?.menus?.[0];
  const scopeLabel = formatVerifiedNavigationScope(scope);
  const navigationError =
    navigationScope.status === 'error' ||
    navigationScope.status === 'unavailable' ||
    (navigationScope.status === 'ready' && !resolvedMenus.authorizationKnown);

  const filterNavigation = (items, query) => {
    if (!query) return items;
    const needle = query.toLowerCase();
    const filterItems = (source) =>
      source.reduce((filtered, item) => {
        const label = String(t(item?.name) || item?.name || '').toLowerCase();
        if (label.includes(needle)) {
          filtered.push(item);
        } else if (Array.isArray(item?.menus)) {
          const menus = filterItems(item.menus);
          if (menus.length) filtered.push({ ...item, menus });
        } else if (Array.isArray(item?.children)) {
          const children = filterItems(item.children);
          if (children.length) filtered.push({ ...item, children });
        }
        return filtered;
      }, []);
    return filterItems(items);
  };
  const menuList = filterNavigation(resolvedMenus.items, searchTerm.trim());

  const closeMobileNavigation = () => {
    if (isMobile) {
      dispatch(setNavCollapsed(true));
      window.requestAnimationFrame(() =>
        document.querySelector('.aa-stage1-mobile-menu')?.focus(),
      );
    }
  };

  return (
    <>
      {isMobile && !navCollapsed && (
        <button
          type='button'
          className='aa-stage1-nav-scrim'
          aria-label={t('close.navigation', 'Close navigation')}
          onClick={() => {
            dispatch(setNavCollapsed(true));
            window.requestAnimationFrame(() =>
              document.querySelector('.aa-stage1-mobile-menu')?.focus(),
            );
          }}
        />
      )}
      <Sider
        id='business-sidebar'
        className='navbar-nav side-nav aa-stage1-navigation agendaally-stage1-nav'
        width={250}
        collapsed={navCollapsed}
        breakpoint='lg'
        collapsedWidth={isMobile ? 0 : 72}
        onCollapse={(collapsed) => dispatch(setNavCollapsed(collapsed))}
        role='navigation'
        aria-label={t('business.navigation', 'Business navigation')}
        aria-hidden={isMobile && navCollapsed ? true : undefined}
        inert={isMobile && navCollapsed ? '' : undefined}
        style={{ height: '100vh', top: 0 }}
      >
        <SidebarHeader navCollapsed={navCollapsed} />
        <div
          className={`aa-stage1-scope${navCollapsed ? ' is-collapsed' : ''}`}
          aria-live='polite'
        >
          <span className='aa-stage1-scope-label'>{t('working.in', 'WORKING IN')}</span>
          {scopeLabel ? (
            <span className='aa-stage1-scope-value'>{scopeLabel}</span>
          ) : navigationScope.status === 'loading' ? (
            <span className='aa-stage1-scope-message'>
              {t('verifying.scope', 'Verifying account scope…')}
            </span>
          ) : navigationError ? (
            <span className='aa-stage1-scope-message is-error'>
              {t(
                'navigation.scope.unavailable',
                'Scope unavailable. Operational navigation is hidden.',
              )}
            </span>
          ) : (
            <span className='aa-stage1-scope-message'>
              {t('scope.not.provided', 'No additional scope details provided.')}
            </span>
          )}
        </div>
        {!navCollapsed && (
          <label className='aa-stage1-nav-search'>
            <SearchOutlined aria-hidden='true' />
            <Input
              ref={searchRef}
              aria-label={t('search.in.menu', 'Search navigation')}
              placeholder={t('find.a.page', 'Find a page')}
              value={searchTerm}
              onChange={(event) => setSearchTerm(event.target.value)}
              allowClear
            />
            <kbd aria-hidden='true'>/</kbd>
          </label>
        )}
        <Scrollbars
          autoHeight
          autoHeightMax={`calc(100vh - ${navCollapsed ? 140 : 210}px)`}
          autoHeightMin={`calc(100vh - ${navCollapsed ? 140 : 210}px)`}
          autoHide
        >
          <MenuList
            data={menuList}
            active={active || safeHome}
            onNavigate={closeMobileNavigation}
          />
        </Scrollbars>
        {!resolvedMenus.authorizationKnown && !navigationError && (
          <div className='aa-stage1-nav-status' role='status'>
            {t('navigation.access.loading', 'Loading authorized pages…')}
          </div>
        )}
      </Sider>
    </>
  );
};

export default Sidebar;