import {
  MenuFoldOutlined,
  MenuUnfoldOutlined,
} from '@ant-design/icons';
import { navCollapseTrigger } from 'redux/slices/theme';
import { useDispatch } from 'react-redux';
import { useTranslation } from 'react-i18next';
import Stage1Brand from 'components/agendaally-stage1/Stage1Brand';

const SidebarHeader = ({ navCollapsed = false }) => {
  const dispatch = useDispatch();
  const { t } = useTranslation();

  const menuTrigger = (event) => {
    event.stopPropagation();
    dispatch(navCollapseTrigger());
    if (window.matchMedia('(max-width: 991px)').matches) {
      window.requestAnimationFrame(() =>
        document.querySelector('.aa-stage1-mobile-menu')?.focus(),
      );
    }
  };
  return (
    <div className='aa-stage1-nav-brand-row'>
      <Stage1Brand
        compact={navCollapsed}
        className='aa-stage1-nav-brand'
      />
      <button
        type='button'
        className='aa-stage1-nav-collapse'
        aria-label={
          navCollapsed
            ? t('expand.navigation', 'Expand navigation')
            : t('collapse.navigation', 'Collapse navigation')
        }
        aria-expanded={!navCollapsed}
        aria-controls='business-sidebar'
        onClick={menuTrigger}
      >
        {navCollapsed ? (
          <MenuUnfoldOutlined aria-hidden='true' />
        ) : (
          <MenuFoldOutlined aria-hidden='true' />
        )}
      </button>
    </div>
  );
};

export default SidebarHeader;
