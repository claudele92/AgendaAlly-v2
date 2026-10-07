import React, { useContext, useEffect, useState } from 'react';
import { Button, Card, Space, Table, Tag } from 'antd';
import { DeleteOutlined, PlusCircleOutlined } from '@ant-design/icons';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { Context } from 'context/context';
import CustomModal from 'components/modal';
import countryAdminService from 'services/countryAdmin';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch } from 'redux/slices/menu';
import { useTranslation } from 'react-i18next';
import DeleteButton from 'components/delete-button';
import { SuperAdminRoute } from 'context/superadmin-route';

const CountryAdmins = () => {
  const { t } = useTranslation();
  const { setIsModalVisible } = useContext(Context);
  const [id, setId] = useState(null);
  const [loading, setLoading] = useState(false);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [rows, setRows] = useState([]);
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);

  const fetchCountryAdmins = () => {
    setLoading(true);
    countryAdminService
      .paginate()
      .then(({ data }) => setRows(data))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    fetchCountryAdmins();
    dispatch(disableRefetch(activeMenu));
    // eslint-disable-next-line
  }, [activeMenu.refetch]);

  const goToAdd = () => {
    dispatch(
      addMenu({
        id: 'country-admin-add',
        url: 'country-admins/add',
        name: t('add.country.admin'),
      }),
    );
    navigate('/country-admins/add');
  };

  const revokeCountryAdmin = () => {
    setLoadingBtn(true);
    countryAdminService
      .delete(id)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setIsModalVisible(false);
        fetchCountryAdmins();
      })
      .finally(() => {
        setId(null);
        setLoadingBtn(false);
      });
  };

  const columns = [
    {
      title: t('user'),
      dataIndex: 'user',
      key: 'user',
      render: (user) =>
        user ? `${user.firstname || ''} ${user.lastname || ''}`.trim() : '—',
    },
    {
      title: t('email'),
      dataIndex: 'user',
      key: 'email',
      render: (user) => user?.email || '—',
    },
    {
      title: t('country'),
      dataIndex: 'country',
      key: 'country',
      render: (country) => country?.name,
    },
    {
      title: t('manager.role.granted'),
      dataIndex: 'manager_role_granted',
      key: 'manager_role_granted',
      render: (granted) =>
        granted ? (
          <Tag color='blue'>{t('yes')}</Tag>
        ) : (
          <Tag>{t('no')}</Tag>
        ),
    },
    {
      title: t('options'),
      key: 'options',
      dataIndex: 'options',
      render: (_, row) => (
        <DeleteButton
          icon={<DeleteOutlined />}
          onClick={() => {
            setId(row.id);
            setIsModalVisible(true);
          }}
        >
          {t('revoke')}
        </DeleteButton>
      ),
    },
  ];

  return (
    <SuperAdminRoute>
      <Card
        title={t('country.admins')}
        extra={
          <Space>
            <Button
              icon={<PlusCircleOutlined />}
              type='primary'
              onClick={goToAdd}
            >
              {t('add.country.admin')}
            </Button>
          </Space>
        }
      >
        <Table
          scroll={{ x: true }}
          columns={columns}
          dataSource={rows}
          rowKey={(record) => record.id}
          loading={loading}
          pagination={false}
        />
        <CustomModal
          click={revokeCountryAdmin}
          text={t('are.you.sure.you.want.to.revoke.this.country.admin?')}
          loading={loadingBtn}
          setText={setId}
          // CustomModal's Cancel button unconditionally calls setActive(null)
          // and setVerify(null) even when unused by the caller — pass no-ops
          // so cancelling this modal doesn't throw. Pre-existing behavior in
          // the shared component, not something to fix here.
          setActive={() => {}}
          setVerify={() => {}}
        />
      </Card>
    </SuperAdminRoute>
  );
};

export default CountryAdmins;
