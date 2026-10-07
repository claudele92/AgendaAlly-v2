import React, { useContext, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { Alert, Button, Space, Table, Typography } from 'antd';
import { DeleteOutlined, PlusOutlined, StopOutlined } from '@ant-design/icons';
import { toast } from 'react-toastify';
import { Context } from 'context/context';
import CustomModal from 'components/modal';
import { disableRefetch } from 'redux/slices/menu';
import countryInviteService from 'services/countryInvite';
import countryRoleService from 'services/countryRole';
import useDidUpdate from 'helpers/useDidUpdate';
import { RoleBadge, StatusBadge } from 'components/staff/badges';
import InviteModal from './invite-modal';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';

// countryId is only ever set for a superadmin (see index.jsx) — a
// restricted country-admin's requests are auto-scoped server-side.
// There is no "edit staff" action here (unlike the seller side): the
// backend has no endpoint to change an existing invite's role, only
// create/list/change-status/delete — see CountryInviteController.
export default function StaffList({ countryId }) {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const { setIsModalVisible } = useContext(Context);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);

  const [invites, setInvites] = useState([]);
  const [meta, setMeta] = useState({});
  const [loading, setLoading] = useState(false);
  const [countryRoles, setCountryRoles] = useState([]);
  const [id, setId] = useState(null);
  const [action, setAction] = useState(null); // 'cancel' | 'remove'
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [inviteModalVisible, setInviteModalVisible] = useState(false);

  const fetchInvites = () => {
    setLoading(true);
    countryInviteService
      .getAll(countryId ? { country_id: countryId } : {})
      .then((res) => {
        setInvites(res.data);
        setMeta(res.meta || {});
      })
      .finally(() => setLoading(false));
  };

  const fetchRoles = () => {
    countryRoleService
      .getAll(countryId ? { country_id: countryId } : {})
      .then((res) => setCountryRoles(res.data));
  };

  useEffect(() => {
    fetchInvites();
    fetchRoles();
    // eslint-disable-next-line
  }, [countryId]);

  useDidUpdate(() => {
    if (activeMenu.refetch) {
      fetchInvites();
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  const confirmText =
    action === 'cancel'
      ? t('are.you.sure.cancel.invite')
      : t('are.you.sure.remove.staff');

  const handleConfirm = () => {
    setLoadingBtn(true);

    const request =
      action === 'cancel'
        ? countryInviteService.changeStatus(
            id,
            'canceled',
            countryId ? { country_id: countryId } : {},
          )
        : countryInviteService.delete({
            'ids[0]': id,
            ...(countryId ? { country_id: countryId } : {}),
          });

    request
      .then(() => {
        toast.success(t('successfully.updated'));
        fetchInvites();
        setIsModalVisible(false);
      })
      .finally(() => setLoadingBtn(false));
  };

  const columns = [
    {
      title: t('name'),
      key: 'name',
      render: (_, row) =>
        `${row.user?.firstname || ''} ${row.user?.lastname || ''}`.trim() ||
        t('n/a'),
    },
    {
      title: t('role'),
      key: 'role',
      render: (_, row) =>
        row.country_role ? <RoleBadge name={row.country_role.name} /> : '—',
    },
    {
      title: t('status'),
      dataIndex: 'status',
      key: 'status',
      render: (status) => <StatusBadge status={status} />,
    },
    {
      title: t('actions'),
      key: 'actions',
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          {row.status === 'new' && (
            <button
              type='button'
              className={tableRowClasses.option}
              title={t('cancel.invite')}
              onClick={() => {
                setId(row.id);
                setAction('cancel');
                setIsModalVisible(true);
              }}
            >
              <StopOutlined />
            </button>
          )}
          {row.status === 'accepted' && (
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
              title={t('remove.staff')}
              onClick={() => {
                setId(row.id);
                setAction('remove');
                setIsModalVisible(true);
              }}
            >
              <DeleteOutlined />
            </button>
          )}
        </div>
      ),
    },
  ];

  const openInvite = () => setInviteModalVisible(true);

  return (
    <>
      <Space className='align-items-center justify-content-between w-100 mb-3'>
        <Typography.Text>{t('staff.description')}</Typography.Text>
        <Button type='primary' icon={<PlusOutlined />} onClick={openInvite}>
          {t('invite.staff')}
        </Button>
      </Space>

      {!countryRoles.length && (
        <Alert
          className='mb-3'
          type='info'
          showIcon
          message={t('no.country.roles.yet')}
          description={t('create.a.role.before.inviting.staff')}
        />
      )}

      <Table
        columns={columns}
        dataSource={invites}
        loading={loading}
        rowKey={(record) => record.id}
        pagination={{
          pageSize: meta.per_page,
          total: meta.total,
          current: meta.current_page,
        }}
      />

      <CustomModal
        click={handleConfirm}
        text={confirmText}
        loading={loadingBtn}
        setText={setId}
        setActive={() => {}}
        setVerify={() => {}}
      />

      {inviteModalVisible && (
        <InviteModal
          countryRoles={countryRoles}
          countryId={countryId}
          handleCancel={() => setInviteModalVisible(false)}
          onInvited={() => fetchInvites()}
        />
      )}
    </>
  );
}
