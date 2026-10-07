import React, { useEffect, useState, useContext } from 'react';
import {
  CopyOutlined,
  DeleteOutlined,
  EditOutlined,
  ExpandOutlined,
  EyeOutlined,
  LoginOutlined,
  PlusOutlined,
} from '@ant-design/icons';
import { Button, Col, Divider, Space, Table, Tabs, Typography } from 'antd';
import { useNavigate } from 'react-router-dom';
import { batch, shallowEqual, useDispatch, useSelector } from 'react-redux';
import { data as menuData } from 'configs/menu-config';
import { fetchUsers } from 'redux/slices/user';
import formatSortType from 'helpers/formatSortType';
import {
  addMenu,
  clearMenu,
  disableRefetch,
  setMenuData,
} from 'redux/slices/menu';
import useDidUpdate from 'helpers/useDidUpdate';
import UserShowModal from './userShowModal';
import { useTranslation } from 'react-i18next';
import UserRoleModal from './userRoleModal';
import SearchInput from 'components/search-input';
import FilterColumns from 'components/filter-column';
import { Context } from 'context/context';
import { toast } from 'react-toastify';
import deliveryService from 'services/delivery';
import CustomModal from 'components/modal';
import userService from 'services/user';
import { setUserData, updateUser } from 'redux/slices/auth';
import { setCurrentChat } from 'redux/slices/chat';
import { fetchRestSettings } from 'redux/slices/globalSettings';
import { fetchMyShop } from 'redux/slices/myShop';
import hideEmail from 'components/hideEmail';
import serviceMasterService from 'services/master/serviceMaster';
import Card from 'components/card';
import OutlinedButton from 'components/outlined-button';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';

const { TabPane } = Tabs;
const ReactAppIsDemo = import.meta.env.VITE_IS_DEMO;

const modalType = {
  SELLER_LOGIN: 'seller_login',
  MASTER_LOGIN: 'master_login',
  DELIVERYMAN_LOGIN: 'deliveryman_login',
  MODERATOR_LOGIN: 'moderator_login',
  MANAGER_LOGIN: 'manager_login',
  DELETE: 'delete',
};

export default function Admin() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const [id, setId] = useState(null);
  const [text, setText] = useState(null);
  const { setIsModalVisible } = useContext(Context);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { users, loading, meta, params } = useSelector(
    (state) => state.user,
    shallowEqual,
  );
  const [uuid, setUuid] = useState(null);
  const [userRole, setUserRole] = useState(null);
  const [roles, setRoles] = useState([]);
  const [role, setRole] = useState('admin');
  const [modalTypeState, setModalTypeState] = useState(modalType.DELETE);
  const immutable = activeMenu.data?.role || role;
  const { user } = useSelector((state) => state.auth, shallowEqual);

  const data = activeMenu.data;
  const roleData = { role: immutable };

  const paramsData = {
    sort: data?.sort,
    column: data?.column,
    ...roleData,
    perPage: data?.perPage,
    page: data?.page,
    search: data?.search,
  };

  const goToEdit = (row) => {
    dispatch(
      addMenu({
        url: `user/${row.uuid}`,
        id: 'user_edit',
        name: 'User edit',
      }),
    );
    navigate(`/user/${row.uuid}`);
  };

  const goToClone = (row) => {
    dispatch(
      addMenu({
        url: `user-clone/${row.uuid}`,
        id: 'user-clone',
        name: 'user.clone',
      }),
    );
    navigate(`/user-clone/${row.uuid}`);
  };

  const goToDetail = (row) => {
    dispatch(
      addMenu({
        url: `/users/user/${row.uuid}`,
        id: 'user_info',
        name: t('user.info'),
      }),
    );
    navigate(`/users/user/${row.uuid}`, { state: { user_id: row.id } });
  };

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      sorter: true,
      is_show: true,
    },
    {
      title: t('firstname'),
      dataIndex: 'firstname',
      is_show: true,
    },
    {
      title: t('lastname'),
      dataIndex: 'lastname',
      is_show: true,
    },
    {
      title: t('email'),
      dataIndex: 'email',
      is_show: true,
      render: (email) => <div>{ReactAppIsDemo ? hideEmail(email) : email}</div>,
    },
    {
      title: t('role'),
      dataIndex: 'role',
      is_show: true,
    },
    {
      title: t('options'),
      dataIndex: 'options',
      is_show: true,
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.location}`}
            onClick={(e) => {
              e.stopPropagation();
              setUuid(row?.uuid);
            }}
          >
            <ExpandOutlined />
          </button>
          {(user?.role !== 'manager' || row?.role !== 'admin') && (
            <>
              <button
                type='button'
                className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
                onClick={(e) => {
                  e.stopPropagation();
                  goToEdit(row);
                }}
              >
                <EditOutlined />
              </button>
              <button
                type='button'
                className={`${tableRowClasses.option} ${tableRowClasses.details}`}
                onClick={(e) => {
                  e.stopPropagation();
                  goToDetail(row);
                }}
              >
                <EyeOutlined />
              </button>
            </>
          )}
          {row?.role !== 'admin' && (
            <>
              <button
                type='button'
                className={`${tableRowClasses.option} ${tableRowClasses.clone}`}
                onClick={(e) => {
                  e.stopPropagation();
                  goToClone(row);
                }}
              >
                <CopyOutlined />
              </button>
              <button
                type='button'
                className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
                onClick={() => {
                  setModalTypeState(modalType.DELETE);
                  setId([row.id]);
                  setIsModalVisible(true);
                  setText(true);
                }}
              >
                <DeleteOutlined />
              </button>
            </>
          )}
          {row?.role !== 'admin' && row?.role !== 'user' && (
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.download}`}
              onClick={() => {
                const typeState =
                  row?.role === 'seller'
                    ? modalType.SELLER_LOGIN
                    : row?.role === 'master'
                      ? modalType.MASTER_LOGIN
                      : row?.role === 'deliveryman'
                        ? modalType.DELIVERYMAN_LOGIN
                        : row?.role === 'moderator'
                          ? modalType.MODERATOR_LOGIN
                          : row?.role === 'manager'
                            ? modalType.MANAGER_LOGIN
                            : modalType.DELETE;
                setModalTypeState(typeState);
                setIsModalVisible(true);
                setId(row.uuid);
              }}
            >
              <LoginOutlined />
            </button>
          )}
        </div>
      ),
    },
  ];

  const [columns, setColumns] = useState(initialColumns);

  const goToAdduser = (e) => {
    dispatch(
      addMenu({
        id: 'user-add-role',
        url: `user/add/${e}`,
        name: t(`add.${e}`),
      }),
    );
    navigate(`/user/add/${e}`);
  };

  function onChangePagination(pagination, filter, sorter) {
    const { pageSize: perPage, current: page } = pagination;
    const { field: column, order } = sorter;
    const sort = formatSortType(order);
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...activeMenu.data, perPage, page, column, sort },
      }),
    );
  }

  const userDelete = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };

    deliveryService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        dispatch(fetchUsers(paramsData));
        setIsModalVisible(false);
        setText([]);
      })
      .finally(() => {
        setId(null);
        setLoadingBtn(false);
      });
  };

  const handleClear = (user) => {
    if (!user) return;
    batch(() => {
      dispatch(clearMenu());
      dispatch(setCurrentChat(null));
      dispatch(setUserData(user));
      dispatch(fetchRestSettings({}));
      dispatch(disableRefetch(activeMenu));
    });
  };

  const loginAsSeller = () => {
    setLoadingBtn(true);
    userService
      .loginAs(id)
      .then((res) => {
        const user = {
          fullName: res.data.user.firstname + ' ' + res.data.user.lastname,
          role: 'seller',
          urls: menuData.seller,
          img: res.data.user.img,
          token: res.data.access_token,
          email: res.data.user.email,
          id: res.data.user.id,
          shop_id: res.data.user?.shop?.id,
          walletId: res.data?.user?.wallet?.id,
        };

        handleClear(user);
        localStorage.setItem('token', res.data.access_token);
        dispatch(fetchMyShop({}));
        navigate('/dashboard');
      })
      .finally(() => setIsModalVisible(false));
  };

  const loginAsModerator = () => {
    setLoadingBtn(true);
    userService
      .loginAs(id)
      .then((res) => {
        const user = {
          fullName: res.data.user.firstname + ' ' + res.data.user.lastname,
          role: 'moderator',
          urls: menuData.moderator,
          img: res.data.user.img,
          token: res.data.access_token,
          email: res.data.user.email,
          id: res.data.user.id,
          shop_id: res.data.user?.shop?.id,
          walletId: res.data?.user?.wallet?.id,
        };

        handleClear(user);
        localStorage.setItem('token', res.data.access_token);
        navigate('/dashboard');
      })
      .finally(() => setIsModalVisible(false));
  };

  const loginAsManager = () => {
    setLoadingBtn(true);
    userService
      .loginAs(id)
      .then((res) => {
        const user = {
          fullName: res.data.user.firstname + ' ' + res.data.user.lastname,
          role: 'manager',
          urls: menuData.manager,
          img: res.data.user.img,
          token: res.data.access_token,
          email: res.data.user.email,
          id: res.data.user.id,
          shop_id: res.data.user?.shop?.id,
          walletId: res.data?.user?.wallet?.id,
        };

        handleClear(user);
        localStorage.setItem('token', res.data.access_token);
        navigate('/dashboard');
      })
      .finally(() => setIsModalVisible(false));
  };

  const loginAsDeliveryman = () => {
    setLoadingBtn(true);
    userService
      .loginAs(id)
      .then((res) => {
        const user = {
          fullName: `${res.data?.user?.firstname || ''} ${
            res.data?.user?.lastname || ''
          }`,
          role: 'deliveryman',
          urls: menuData.deliveryman,
          img: res.data?.user?.img,
          token: res.data?.access_token,
          email: res.data?.user.email,
          id: res.data?.user?.id,
          shop_id: res.data?.user?.shop?.id,
          walletId: res.data?.user?.wallet?.id,
        };

        handleClear(user);
        localStorage.setItem('token', res.data.access_token);
        navigate('/dashboard');
      })
      .finally(() => {
        setIsModalVisible(false);
        setLoadingBtn(false);
      });
  };

  const loginAsMaster = () => {
    setLoadingBtn(true);
    userService
      .loginAs(id)
      .then((res) => {
        const user = {
          fullName: `${res.data?.user?.firstname || ''} ${res.data?.user?.lastname || ''}`,
          role: 'master',
          urls: menuData.master,
          img: res.data?.user?.img,
          token: res.data?.access_token,
          email: res.data?.user.email,
          id: res.data?.user?.id,
          shop_id: res.data?.user?.shop?.id,
          walletId: res.data?.user?.wallet?.id,
        };

        handleClear(user);
        serviceMasterService
          .show(user.id)
          .then((res) => {
            dispatch(updateUser(res.data));
            dispatch(disableRefetch(activeMenu));
          })
          .catch((err) => {
            throw err;
          });
        localStorage.setItem('token', res.data.access_token);
        navigate('/dashboard');
      })
      .finally(() => {
        setLoadingBtn(false);
        setIsModalVisible(false);
      });
  };

  const fetchAllRoles = () => {
    userService.getRoles().then(({ data }) => {
      setRoles(data?.map((role) => role?.name));
    });
  };

  useEffect(() => {
    if (activeMenu.refetch && !loadingBtn) {
      batch(() => {
        dispatch(fetchUsers(paramsData));
        dispatch(disableRefetch(activeMenu));
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    if (!loadingBtn) {
      dispatch(fetchUsers(paramsData));
    }
  }, [activeMenu.data]);

  useEffect(() => {
    if (!loadingBtn) {
      fetchAllRoles();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const handleFilter = (items) => {
    const data = activeMenu.data;
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...data, ...items },
      }),
    );
  };

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

  const allDelete = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.user'));
    } else {
      setModalTypeState(modalType.DELETE);
      setIsModalVisible(true);
      setText(false);
    }
  };

  return (
    <Card>
      <Space className='align-items-center justify-content-between w-100'>
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
          {t('users')}
        </Typography.Title>
        {immutable !== 'admin' && immutable !== 'seller' && (
          <Button
            type='primary'
            icon={<PlusOutlined />}
            onClick={() => goToAdduser(immutable)}
            style={{ width: '100%' }}
          >
            {t(`add.${immutable}`)}
          </Button>
        )}
      </Space>
      <Divider color='var(--divider)' />
      <Space
        className='w-100 justify-content-end align-items-center'
        style={{ rowGap: '6px', columnGap: '6px' }}
      >
        <Col style={{ minWidth: '170px' }}>
          <SearchInput
            placeholder={t('search')}
            className='w-100'
            handleChange={(e) => {
              handleFilter({ search: e });
            }}
            defaultValue={activeMenu.data?.search}
            resetSearch={!activeMenu.data?.search}
          />
        </Col>
        <OutlinedButton onClick={allDelete} color='red'>
          {t('delete.selection')}
        </OutlinedButton>
        <FilterColumns columns={columns} setColumns={setColumns} />
      </Space>
      <Divider color='var(--divider)' />
      <Tabs
        scroll={{ x: true }}
        activeKey={immutable}
        onChange={(key) => {
          handleFilter({ role: key, page: 1 });
          setRole(key);
        }}
        type='card'
      >
        {roles.map((item) => (
          <TabPane tab={t(item)} key={item} />
        ))}
      </Tabs>
      <Table
        scroll={{ x: true }}
        rowSelection={rowSelection}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={users}
        loading={loading}
        pagination={{
          pageSize: params.perPage,
          page: activeMenu.data?.page || 1,
          total: meta.total,
          defaultCurrent: activeMenu.data?.page,
          current: activeMenu.data?.page,
        }}
        rowKey={(record) => record.id}
        onChange={onChangePagination}
      />
      <CustomModal
        click={
          modalTypeState === modalType.SELLER_LOGIN
            ? loginAsSeller
            : modalTypeState === modalType.MASTER_LOGIN
              ? loginAsMaster
              : modalTypeState === modalType.DELIVERYMAN_LOGIN
                ? loginAsDeliveryman
                : modalTypeState === modalType.MODERATOR_LOGIN
                  ? loginAsModerator
                  : modalTypeState === modalType.MANAGER_LOGIN
                    ? loginAsManager
                    : userDelete
        }
        text={
          modalTypeState === modalType.SELLER_LOGIN
            ? t('do.you.want.login.as.this.seller')
            : modalTypeState === modalType.MASTER_LOGIN
              ? t('do.you.want.login.as.this.master')
              : modalTypeState === modalType.DELIVERYMAN_LOGIN
                ? t('do.you.want.login.as.this.deliveryman')
                : modalTypeState === modalType.MODERATOR_LOGIN
                  ? t('do.you.want.login.as.this.moderator')
                  : modalTypeState === modalType.MANAGER_LOGIN
                    ? t('do.you.want.login.as.this.manager')
                    : text
                      ? t('delete')
                      : t('all.delete')
        }
        loading={loadingBtn}
        setText={setId}
      />
      {uuid && <UserShowModal uuid={uuid} handleCancel={() => setUuid(null)} />}
      {userRole && (
        <UserRoleModal data={userRole} handleCancel={() => setUserRole(null)} />
      )}
    </Card>
  );
}
