import clsx from "clsx";
import {
  AGENDAALLY_BRAND_LOGO,
  AGENDAALLY_BRAND_MARK,
} from "@/utils/agendaally-brand-assets";

export interface Stage2BrandProps {
  compact?: boolean;
  dark?: boolean;
  business?: boolean;
  className?: string;
  logo?: string;
  symbol?: string;
}

/**
 * Keep platform branding asset-driven while preserving the existing shell
 * classes and compact/dark/business props.
 */
export const Stage2Brand = ({
  compact = false,
  dark = false,
  business = false,
  className,
  logo = AGENDAALLY_BRAND_LOGO,
  symbol = AGENDAALLY_BRAND_MARK,
}: Stage2BrandProps) => (
  <span
    className={clsx(
      "aa-s2-brand",
      dark && "aa-s2-brand-dark",
      business && "aa-s2-brand-business",
      className
    )}
    role="img"
    aria-label={business ? "AgendaAlly for Business" : "AgendaAlly"}
  >
    <img
      className={compact ? "aa-s2-native-logo aa-s2-logo-mark" : "aa-s2-native-logo aa-s2-logo-wordmark"}
      src={compact ? symbol : logo}
      alt=""
      aria-hidden="true"
    />
    {!compact && (
      <img
        className="aa-s2-native-logo aa-s2-logo-mark aa-s2-responsive-mark"
        src={symbol}
        alt=""
        aria-hidden="true"
      />
    )}
    {!compact && business && (
      <span className="aa-s2-brand-name">
        <small>FOR BUSINESS</small>
      </span>
    )}
  </span>
);

export default Stage2Brand;