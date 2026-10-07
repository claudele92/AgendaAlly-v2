import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate, useParams } from 'react-router-dom';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { Alert, Button, Card, Form, Spin } from 'antd';
import { toast } from 'react-toastify';
import { removeFromMenu } from '../../../redux/slices/menu';
import { fetchStaffInvites } from '../../../redux/slices/staffInvite';
import staffInviteService from '../../../services/seller/staffInvite';
import shopRoleService from '../../../services/seller/shopRole';
import sellerShopLocationService from '../../../services/seller/shop-locations';
import { BranchSelect, RoleSelect } from './role-branch-selects';

// Only shop_role_id and shop_location_ids are editable here - user_id and
// the platform role aren't part of this form (see
// Invitation\InviteUpdateRequest on the backend). Loads its own data
// independently rather than relying on staff-list.jsx's redux state having
// already been populated, same as role-form.jsx does for roles.
export default function StaffEdit() {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { id } = useParams();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);

  const [shopRoles, setShopRoles] = useState([]);
  const [shopLocations, setShopLocations] = useState([]);
  const [roleId, setRoleId] = useState(null);
  const [locationIds, setLocationIds] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  const loadData = () => {
    setLoading(true);
    setLoadError(null);

    Promise.all([
      staffInviteService.getById(id),
      shopRoleService.getAll(),
      sellerShopLocationService.getAll(),
    ])
      .then(([inviteRes, rolesRes, locationsRes]) => {
        setShopRoles(rolesRes.data || []);
        setShopLocations(locationsRes.data || []);
        setRoleId(inviteRes.data?.shop_role?.id ?? null);
        setLocationIds(
          (inviteRes.data?.shop_locations || []).map((l) => l.id),
        );
      })
      .catch((err) =>
        setLoadError(err.response?.data?.message || t('failed.to.load.data')),
      )
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadData();
    // eslint-disable-next-line
  }, [id]);

  const onFinish = () => {
    const nextUrl = 'seller/staff';
    setSubmitting(true);

    staffInviteService
      .update(id, {
        shop_role_id: roleId,
        shop_location_ids: locationIds,
      })
      .then(() => {
        toast.success(t('successfully.updated'));
        dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
        dispatch(fetchStaffInvites());
        navigate(`/${nextUrl}`);
      })
      .finally(() => setSubmitting(false));
  };

  if (loading) {
    return (
      <Card>
        <div className='d-flex justify-content-center p-5'>
          <Spin size='large' />
        </div>
      </Card>
    );
  }

  if (loadError) {
    return (
      <Card title={t('edit.staff')}>
        <Alert
          type='error'
          showIcon
          message={t('failed.to.load.data')}
          description={loadError}
        />
        <Button type='primary' className='mt-3' onClick={loadData}>
          {t('retry')}
        </Button>
      </Card>
    );
  }

  return (
    <Card title={t('edit.staff')}>
      <Form layout='vertical' onFinish={onFinish}>
        <RoleSelect shopRoles={shopRoles} value={roleId} onChange={setRoleId} />
        <BranchSelect
          shopLocations={shopLocations}
          value={locationIds}
          onChange={setLocationIds}
        />
        <Button
          type='primary'
          htmlType='submit'
          loading={submitting}
          className='mt-3'
        >
          {t('submit')}
        </Button>
      </Form>
    </Card>
  );
}
