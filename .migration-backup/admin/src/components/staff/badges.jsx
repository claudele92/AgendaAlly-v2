import React from 'react';
import { useTranslation } from 'react-i18next';
import styles from '../../assets/scss/components/staff-theme.module.scss';

// Role-name badge — e.g. "Owner", "Manager", "Receptionist", "Accountant".
// The owner is never a shop_role (they have full access structurally, not
// via a role), so callers pass `isOwner` instead of a role name for them.
export function RoleBadge({ name, isOwner }) {
  const { t } = useTranslation();

  return (
    <span className={styles.roleBadge}>
      {isOwner ? t('owner') : name}
    </span>
  );
}

// Status badge — semantic only (green/amber/red for active/pending/
// cancelled), never decorative. `status` is one of the Invitation::STATUS
// keys: new, accepted, rejected, canceled.
export function StatusBadge({ status }) {
  const { t } = useTranslation();

  if (status === 'accepted') {
    return <span className={styles.statusActive}>{t('active')}</span>;
  }

  if (status === 'new') {
    return <span className={styles.statusPending}>{t('pending')}</span>;
  }

  return <span className={styles.statusCancelled}>{t('cancelled')}</span>;
}
