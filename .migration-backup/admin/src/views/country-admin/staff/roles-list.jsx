import React, { useContext, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { Button, Space, Table, Typography } from 'antd';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import { toast } from 'react-toastify';
import { Context } from 'context/context';
import CustomModal from 'components/modal';
import { addMenu, disableRefetch } from 'redux/slices/menu';
import countryRoleService from 'services/countryRole';
import useDidUpdate from 'helpers/useDidUpdate';
import { RoleBadge } from 'components/staff/badges';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';

// countryId is only ever set for a superadmin (picked via the country
// selector on the parent Staff screen) — a restricted country-admin's
// requests are auto-scoped server-side, so it stays undefined for them
// and is simply omitted below.
export default function RolesList({ countryId }) {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { setIsModalVisible } = useContext(Context);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [roles, setRoles] = useState([]);
  const [meta, setMeta] = useState({});
  const [loading, setLoading] = useState(false);
  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);

  const fetchRoles = () => {
    setLoading(true);
    countryRoleService
      .getAll(countryId ? { country_id: countryId } : {})
      .then((res) => {
        setRoles(res.data);
        setMeta(res.meta || {});
      })
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    fetchRoles();
    // eslint-disable-next-line
  }, [countryId]);

  useDidUpdate(() => {
    if (activeMenu.refetch) {
      fetchRoles();
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  const goToAdd = () => {
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

  const goToEdit = (row) => {
    dispatch(
      addMenu({
        id: 'country-staff-role-edit',
        url: `country-admin/staff/roles/edit/${row.id}`,
        name: t('edit.role'),
        data: { country_id: countryId },
      }),
    );
    navigate(`/country-admin/staff/roles/edit/${row.id}`);
  };

  const handleDelete = () => {
    setLoadingBtn(true);
    countryRoleService
      .delete(id, countryId ? { country_id: countryId } : {})
      .then(() => {
        toast.success(t('successfully.deleted'));
        fetchRoles();
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
        <Typography.Text>{t('country.roles.description')}</Typography.Text>
        <Button type='primary' icon={<PlusOutlined />} onClick={goToAdd}>
          {t('add.role')}
        </Button>
      </Space>
      <Table
        columns={columns}
        dataSource={roles}
        loading={loading}
        rowKey={(record) => record.id}
        pagination={{
          pageSize: meta.per_page,
          total: meta.total,
          current: meta.current_page,
        }}
      />
      <CustomModal
        click={handleDelete}
        text={t('are.you.sure.delete.role')}
        loading={loadingBtn}
        setText={setId}
        setActive={() => {}}
        setVerify={() => {}}
      />
    </>
  );
}
