import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { useDispatch } from 'react-redux';
import { Alert, Button, Form, Input, Modal, Radio, Select } from 'antd';
import { SearchOutlined } from '@ant-design/icons';
import { toast } from 'react-toastify';
import countryInviteService from 'services/countryInvite';
import { addMenu } from 'redux/slices/menu';
import CreateAccountForm from './create-account-form';

// Mirrors seller-views/staff/invite-modal.jsx's two modes: resolve an
// exact email/phone to an existing user (then pick a role), or create a
// brand-new account for someone who doesn't have one yet — country
// invites have no branch/location concept, so there's no second select
// in the search path, unlike the seller side.
export default function InviteModal({
  countryRoles,
  countryId,
  handleCancel,
  onInvited,
}) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const dispatch = useDispatch();

  const [mode, setMode] = useState('search');
  const [query, setQuery] = useState('');
  const [searching, setSearching] = useState(false);
  const [searchError, setSearchError] = useState(null);
  const [resolvedUser, setResolvedUser] = useState(null);
  const [roleId, setRoleId] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  const goCreateRole = () => {
    handleCancel();
    dispatch(
      addMenu({
        id: 'country-staff-role-add',
        url: 'country-admin/staff/roles/add',
        name: t('add.role'),
        data: { country_id: countryId },
      }),
    );
    navigate('/country-admin/staff/roles/add');
  };

  const handleSearch = () => {
    if (!query.trim()) {
      return;
    }

    setSearching(true);
    setSearchError(null);
    setResolvedUser(null);

    countryInviteService
      .searchUser(query.trim(), countryId ? { country_id: countryId } : {})
      .then((res) => setResolvedUser(res.data))
      .catch(() =>
        setSearchError(t('no.user.found.must.already.have.an.account')),
      )
      .finally(() => setSearching(false));
  };

  const handleInvite = () => {
    if (!resolvedUser || !roleId) {
      return;
    }

    setSubmitting(true);
    countryInviteService
      .create({
        user_id: resolvedUser.id,
        country_role_id: roleId,
        ...(countryId ? { country_id: countryId } : {}),
      })
      .then(() => {
        toast.success(t('invite.sent'));
        onInvited?.();
        handleCancel();
      })
      .finally(() => setSubmitting(false));
  };

  if (!countryRoles?.length) {
    return (
      <Modal
        title={t('invite.staff')}
        visible
        onCancel={handleCancel}
        footer={null}
      >
        <Alert
          type='info'
          showIcon
          message={t('no.country.roles.yet')}
          description={t('create.a.role.before.inviting.staff')}
        />
        <Button type='primary' className='mt-3' onClick={goCreateRole}>
          {t('create.role')}
        </Button>
      </Modal>
    );
  }

  return (
    <Modal
      title={t('invite.staff')}
      visible
      onCancel={handleCancel}
      footer={null}
    >
      <Radio.Group
        className='mb-3'
        optionType='button'
        buttonStyle='solid'
        value={mode}
        onChange={(e) => setMode(e.target.value)}
        options={[
          { label: t('invite.existing.user'), value: 'search' },
          { label: t('create.new.staff.account'), value: 'create' },
        ]}
      />

      {mode === 'create' ? (
        <CreateAccountForm
          countryRoles={countryRoles}
          handleCancel={handleCancel}
          onInvited={onInvited}
        />
      ) : (
        <Form layout='vertical'>
          <Form.Item label={t('email.or.phone')}>
            <Input.Group compact style={{ display: 'flex' }}>
              <Input
                value={query}
                placeholder={t('enter.email.or.phone')}
                onChange={(e) => {
                  setQuery(e.target.value);
                  setResolvedUser(null);
                  setSearchError(null);
                }}
                onPressEnter={handleSearch}
                style={{ flex: 1 }}
              />
              <Button
                icon={<SearchOutlined />}
                onClick={handleSearch}
                loading={searching}
              >
                {t('search')}
              </Button>
            </Input.Group>
          </Form.Item>

          {searchError && (
            <Alert className='mb-3' type='error' showIcon message={searchError} />
          )}

          {resolvedUser && (
            <>
              <Alert
                className='mb-3'
                type='success'
                showIcon
                message={resolvedUser.name}
                description={[resolvedUser.email, resolvedUser.phone]
                  .filter(Boolean)
                  .join(' · ')}
              />
              <Form.Item label={t('role')}>
                <Select
                  allowClear
                  placeholder={t('select.role')}
                  value={roleId}
                  onChange={setRoleId}
                  options={countryRoles.map((role) => ({
                    value: role.id,
                    label: role.name,
                  }))}
                />
              </Form.Item>
              <Button
                type='primary'
                disabled={!roleId}
                loading={submitting}
                onClick={handleInvite}
              >
                {t('send.invite')}
              </Button>
            </>
          )}
        </Form>
      )}
    </Modal>
  );
}
