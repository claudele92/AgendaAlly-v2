import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { useDispatch } from 'react-redux';
import { Alert, Button, Form, Input, Modal, Radio } from 'antd';
import { SearchOutlined } from '@ant-design/icons';
import { toast } from 'react-toastify';
import staffInviteService from '../../../services/seller/staffInvite';
import { addMenu } from '../../../redux/slices/menu';
import { BranchSelect, RoleSelect } from './role-branch-selects';
import CreateAccountForm from './create-account-form';

// Two ways to get someone onto the staff list: link an existing platform
// account (the invite endpoint is keyed by an existing user's id, not
// free-text contact info — see Invitation\SellerRequest on the backend),
// or create a brand-new account for someone who doesn't have one yet
// (posts straight to dashboard/seller/users, the same endpoint the master
// invitation flow already uses for exactly this purpose - see
// CreateAccountForm). Both end up with a real shop_role + branch
// assignment; which one applies just depends on whether the person
// already exists on the platform.
export default function InviteModal({
  shopRoles,
  shopLocations,
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
  const [locationIds, setLocationIds] = useState([]);
  const [submitting, setSubmitting] = useState(false);

  const goCreateRole = () => {
    handleCancel();
    dispatch(
      addMenu({
        id: 'seller-staff-role-add',
        url: 'seller/staff/roles/add',
        name: t('add.role'),
      }),
    );
    navigate('/seller/staff/roles/add');
  };

  const handleSearch = () => {
    if (!query.trim()) {
      return;
    }

    setSearching(true);
    setSearchError(null);
    setResolvedUser(null);

    staffInviteService
      .searchUser(query.trim())
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
    staffInviteService
      .create({
        user_id: resolvedUser.id,
        shop_role_id: roleId,
        ...(locationIds.length ? { shop_location_ids: locationIds } : {}),
      })
      .then(() => {
        toast.success(t('invite.sent'));
        onInvited?.();
        handleCancel();
      })
      .finally(() => setSubmitting(false));
  };

  if (!shopRoles?.length) {
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
          message={t('no.shop.roles.yet')}
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
          shopRoles={shopRoles}
          shopLocations={shopLocations}
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
              <RoleSelect shopRoles={shopRoles} value={roleId} onChange={setRoleId} />
              <BranchSelect
                shopLocations={shopLocations}
                value={locationIds}
                onChange={setLocationIds}
              />
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
