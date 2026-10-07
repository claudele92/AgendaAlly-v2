import { useSelector } from 'react-redux';
import cls from './Stage1Brand.module.scss';
import {
  AGENDAALLY_BRAND_LOGO,
  AGENDAALLY_BRAND_MARK,
  resolveAgendaAllyBrandAsset,
} from 'helpers/agendaallyBrandAssets';

const Stage1Brand = ({ compact = false, className = '' }) => (
  <BrandImage compact={compact} className={className} />
);

const BrandImage = ({ compact, className }) => {
  const settings = useSelector((state) => state.globalSettings?.settings || {});
  const configuredSrc = compact
    ? settings.admin_favicon || settings.favicon || AGENDAALLY_BRAND_MARK
    : settings.logo || settings.dark_logo || AGENDAALLY_BRAND_LOGO;
  const src =
    resolveAgendaAllyBrandAsset(configuredSrc, compact ? 'mark' : 'logo') ||
    (compact ? AGENDAALLY_BRAND_MARK : AGENDAALLY_BRAND_LOGO);

  return (
    <span
      className={`${cls.brand} ${className}`.trim()}
      role='img'
      aria-label='AgendaAlly'
    >
      <img
        className={compact ? cls.mark : cls.wordmark}
        src={src}
        alt=''
        aria-hidden='true'
      />
    </span>
  );
};

export default Stage1Brand;
