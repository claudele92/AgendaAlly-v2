import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Alert, Card, Tabs, Typography } from 'antd';
import { shallowEqual, useSelector } from 'react-redux';
import { InfiniteSelect } from 'components/infinite-select';
import countryService from 'services/deliveryzone/country';
import RolesList from './roles-list';
import StaffList from './staff-list';

const { TabPane } = Tabs;

// A country-admin (or their accepted staff) is auto-scoped server-side —
// see CountryContext::restrictedCountryId() — so they never see or need a
// country picker here at all. Only a global superadmin, who isn't tied to
// any single country, has to name one before country-roles/country-invites
// endpoints will resolve anything (see CountryBaseController).
export default function CountryStaff() {
  const { t } = useTranslation();
  const { user } = useSelector((state) => state.auth, shallowEqual);
  const [country, setCountry] = useState(null);

  const isSuperAdmin = !!user?.isSuperAdmin;
  const countryId = country?.value;

  const fetchCountry = ({ search, page }) =>
    countryService
      .get({ search: !!search?.length ? search : undefined, page })
      .then((res) =>
        res.data.map((item) => ({
          label: item?.translation?.title,
          value: item.id,
        })),
      );

  return (
    <Card>
      <Typography.Title
        level={1}
        style={{
          color: 'var(--text)',
          fontSize: '20px',
          fontWeight: 500,
          padding: 0,
          margin: 0,
        }}
      >
        {t('staff')}
      </Typography.Title>

      {isSuperAdmin && (
        <InfiniteSelect
          className='mt-3 w-100'
          style={{ maxWidth: 320 }}
          placeholder={t('select.country')}
          value={country}
          onChange={setCountry}
          fetchOptions={fetchCountry}
        />
      )}

      {isSuperAdmin && !countryId ? (
        <Alert
          className='mt-3'
          type='info'
          showIcon
          message={t('select.a.country.to.manage.its.staff')}
        />
      ) : (
        <Tabs defaultActiveKey='staff' type='card' className='mt-3'>
          <TabPane tab={t('staff')} key='staff'>
            <StaffList countryId={countryId} />
          </TabPane>
          <TabPane tab={t('roles')} key='roles'>
            <RolesList countryId={countryId} />
          </TabPane>
        </Tabs>
      )}
    </Card>
  );
}
