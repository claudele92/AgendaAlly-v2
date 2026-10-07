import React, { useContext, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { Alert, Button, Space, Table, Typography } from 'antd';
import {
  DeleteOutlined,
  EditOutlined,
  PlusOutlined,
  StopOutlined,
} from '@ant-design/icons';
import { toast } from 'react-toastify';
import { Context } from '../../../context/context';
import CustomModal from '../../../components/modal';
import { addMenu, disableRefetch } from '../../../redux/slices/menu';
import { fetchStaffInvites } from '../../../redux/slices/staffInvite';
import { fetchShopRoles } from '../../../redux/slices/shopRole';
import { fetchSellerShopLocations } from '../../../redux/slices/shop-locations';
import staffInviteService from '../../../services/seller/staffInvite';
import useDidUpdate from '../../../helpers/useDidUpdate';
import { RoleBadge, StatusBadge } from '../../../components/staff/badges';
import InviteModal from './invite-modal';
import tableRowClasses from '../../../assets/scss/components/table-row.module.scss';

export default function StaffList() {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { setIsModalVisible } = useContext(Context);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { staffInvites, meta, loading, params } = useSelector(
    (state) => state.staffInvite,
    shallowEqual,
  );
  const { shopRoles } = useSelector((state) => state.shopRole, shallowEqual);
  const { locations: shopLocations } = useSelector(
    (state) => state.shopLocations,
    shallowEqual,
  );

  const [id, setId] = useState(null);
  const [action, setAction] = useState(null); // 'cancel' | 'remove'
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [inviteModalVisible, setInviteModalVisible] = useState(false);

  useEffect(() => {
    dispatch(fetchStaffInvites());
    dispatch(fetchShopRoles());
    dispatch(fetchSellerShopLocations());
    // eslint-disable-next-line
  }, []);

  useDidUpdate(() => {
    if (activeMenu.refetch) {
      dispatch(fetchStaffInvites());
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  const confirmText =
    action === 'cancel'
      ? t('are.you.sure.cancel.invite')
      : t('are.you.sure.remove.staff');

  const goToEdit = (row) => {
    dispatch(
      addMenu({
        id: 'seller-staff-edit',
        url: `seller/staff/edit/${row.id}`,
        name: t('edit.staff'),
      }),
    );
    navigate(`/seller/staff/edit/${row.id}`);
  };

  const handleConfirm = () => {
    setLoadingBtn(true);

    const request =
      action === 'cancel'
        ? staffInviteService.changeStatus(id, 'canceled')
        : staffInviteService.delete({ 'ids[0]': id });

    request
      .then(() => {
        toast.success(t('successfully.updated'));
        dispatch(fetchStaffInvites());
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
        row.shop_role ? <RoleBadge name={row.shop_role.name} /> : '—',
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
          {(row.status === 'new' || row.status === 'accepted') && (
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
              title={t('edit.staff')}
              onClick={() => goToEdit(row)}
            >
              <EditOutlined />
            </button>
          )}
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

      {!shopRoles.length && (
        <Alert
          className='mb-3'
          type='info'
          showIcon
          message={t('no.shop.roles.yet')}
          description={t('create.a.role.before.inviting.staff')}
        />
      )}

      <Table
        columns={columns}
        dataSource={staffInvites}
        loading={loading}
        rowKey={(record) => record.id}
        pagination={{
          pageSize: params.perPage,
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
          shopRoles={shopRoles}
          shopLocations={shopLocations}
          handleCancel={() => setInviteModalVisible(false)}
          onInvited={() => dispatch(fetchStaffInvites())}
        />
      )}
    </>
  );
}
