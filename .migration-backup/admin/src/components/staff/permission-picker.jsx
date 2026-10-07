import React, { useMemo } from 'react';
import { useTranslation } from 'react-i18next';
import { Checkbox, Collapse } from 'antd';
import styles from '../../assets/scss/components/staff-theme.module.scss';

const { Panel } = Collapse;

// Groups a flat permission catalog ([{id,key,group,label}]) into a
// collapsible, per-domain checkbox picker with a "select all in this
// group" toggle — used by both the seller shop_roles screen and the
// country-admin country_roles screen (same shape, different catalog).
export default function PermissionPicker({ catalog, value, onChange }) {
  const { t } = useTranslation();
  const selected = value || [];

  const groups = useMemo(() => {
    const byGroup = {};

    (catalog || []).forEach((permission) => {
      const key = permission.group || 'other';
      if (!byGroup[key]) {
        byGroup[key] = [];
      }
      byGroup[key].push(permission);
    });

    return byGroup;
  }, [catalog]);

  const toggleOne = (id, checked) => {
    if (checked) {
      onChange([...selected, id]);
    } else {
      onChange(selected.filter((existingId) => existingId !== id));
    }
  };

  const toggleGroup = (groupPermissions, checked) => {
    const groupIds = groupPermissions.map((permission) => permission.id);

    if (checked) {
      onChange([...new Set([...selected, ...groupIds])]);
    } else {
      onChange(selected.filter((id) => !groupIds.includes(id)));
    }
  };

  return (
    <Collapse defaultActiveKey={Object.keys(groups)}>
      {Object.entries(groups).map(([group, permissions]) => {
        const groupIds = permissions.map((permission) => permission.id);
        const selectedInGroup = groupIds.filter((id) =>
          selected.includes(id),
        );
        const allSelected =
          groupIds.length > 0 && selectedInGroup.length === groupIds.length;
        const partiallySelected =
          selectedInGroup.length > 0 && !allSelected;

        return (
          <Panel
            key={group}
            header={
              <div className={styles.groupHeader}>
                <span>
                  {t(group)}
                  <span className={styles.groupCount}>
                    {selectedInGroup.length}/{groupIds.length}
                  </span>
                </span>
                <Checkbox
                  checked={allSelected}
                  indeterminate={partiallySelected}
                  onClick={(e) => e.stopPropagation()}
                  onChange={(e) => toggleGroup(permissions, e.target.checked)}
                >
                  {t('select.all')}
                </Checkbox>
              </div>
            }
          >
            <div className='d-flex flex-column'>
              {permissions.map((permission) => (
                <Checkbox
                  key={permission.id}
                  checked={selected.includes(permission.id)}
                  onChange={(e) =>
                    toggleOne(permission.id, e.target.checked)
                  }
                  style={{ marginLeft: 0, marginBottom: 8 }}
                >
                  {permission.label}
                </Checkbox>
              ))}
            </div>
          </Panel>
        );
      })}
    </Collapse>
  );
}
