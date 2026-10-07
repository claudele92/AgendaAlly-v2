import React, { useContext, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { Button, Space, Table, Typography } from 'antd';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import { toast } from 'react-toastify';
import { Context } from '../../../context/context';
import CustomModal from '../../../components/modal';
import { addMenu, disableRefetch } from '../../../redux/slices/menu';
import { fetchShopRoles } from '../../../redux/slices/shopRole';
import shopRoleService from '../../../services/seller/shopRole';
import useDidUpdate from '../../../helpers/useDidUpdate';
import { RoleBadge } from '../../../components/staff/badges';
import tableRowClasses from '../../../assets/scss/components/table-row.module.scss';

export default function RolesList() {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { setIsModalVisible } = useContext(Context);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { shopRoles, meta, loading, params } = useSelector(
    (state) => state.shopRole,
    shallowEqual,
  );
  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);

  useEffect(() => {
    dispatch(fetchShopRoles());
    // eslint-disable-next-line
  }, []);

  useDidUpdate(() => {
    if (activeMenu.refetch) {
      dispatch(fetchShopRoles());
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  const goToAdd = () => {
    dispatch(
      addMenu({
        id: 'seller-staff-role-add',
        url: 'seller/staff/roles/add',
        name: t('add.role'),
      }),
    );
    navigate('/seller/staff/roles/add');
  };

  const goToEdit = (row) => {
    dispatch(
      addMenu({
        id: 'seller-staff-role-edit',
        url: `seller/staff/roles/edit/${row.id}`,
        name: t('edit.role'),
      }),
    );
    navigate(`/seller/staff/roles/edit/${row.id}`);
  };

  const handleDelete = () => {
    setLoadingBtn(true);
    shopRoleService
      .delete(id)
      .then(() => {
        toast.success(t('successfully.deleted'));
        dispatch(fetchShopRoles());
        setIsModalVisible(false);
      })
      .finally(() => setLoadingBtn(false));
  };

  const columns = [
    {
      title: t('name'),
      dataIndex: 'name',
      key: 'name',
      render: (name) => <RoleBadge name={name} />,
    },
    {
      title: t('permissions'),
      dataIndex: 'permissions',
      key: 'permissions',
      render: (permissions) => permissions?.length || 0,
    },
    {
      title: t('staff.count'),
      dataIndex: 'staff_count',
      key: 'staff_count',
      render: (count) => count || 0,
    },
    {
      title: t('actions'),
      key: 'actions',
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            onClick={() => goToEdit(row)}
          >
            <EditOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            onClick={() => {
              setId(row.id);
              setIsModalVisible(true);
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ];

  return (
    <>
      <Space className='align-items-center justify-content-between w-100 mb-3'>
        <Typography.Text>{t('shop.roles.description')}</Typography.Text>
        <Button type='primary' icon={<PlusOutlined />} onClick={goToAdd}>
          {t('add.role')}
        </Button>
      </Space>
      <Table
        columns={columns}
        dataSource={shopRoles}
        loading={loading}
        rowKey={(record) => record.id}
        pagination={{
          pageSize: params.perPage,
          total: meta.total,
          current: meta.current_page,
        }}
      />
      <CustomModal
        click={handleDelete}
        text={t('are.you.sure.delete.role')}
        loading={loadingBtn}
        setText={setId}
        // CustomModal's Cancel button unconditionally calls setActive(null)
        // and setVerify(null) even when unused by the caller — pass no-ops
        // so cancelling this modal doesn't throw. Pre-existing behavior in
        // the shared component, not something to fix here.
        setActive={() => {}}
        setVerify={() => {}}
      />
    </>
  );
}
