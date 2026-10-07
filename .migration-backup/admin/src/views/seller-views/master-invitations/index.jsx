import React, { useEffect, useState, useContext } from 'react';
import { useTranslation } from 'react-i18next';
import { shallowEqual, useDispatch, useSelector, batch } from 'react-redux';
import {
  Card,
  Table,
  Tabs,
  Button,
  Space,
  Typography,
  Divider,
  Col,
} from 'antd';
import {
  addMenu,
  disableRefetch,
  setMenuData,
  clearMenu,
} from 'redux/slices/menu';
import RiveResult from 'components/rive-result';
import { EditOutlined, LoginOutlined, PlusOutlined } from '@ant-design/icons';
import { Context } from 'context/context';
import { fetchSellerMasterInvitations } from 'redux/slices/invitations';
import useDidUpdate from 'helpers/useDidUpdate';
import InvitationModal from './invitation-modal';
import formatSortType from 'helpers/formatSortType';
import { useNavigate } from 'react-router-dom';
import SearchInput from 'components/search-input';
import CustomModal from 'components/modal';
import userService from 'services/user';
import { data as menuData } from 'configs/menu-config';
import { setCurrentChat } from 'redux/slices/chat';
import { setUserData, updateUser } from 'redux/slices/auth';
import { fetchRestSettings } from 'redux/slices/globalSettings';
import serviceMasterService from 'services/master/serviceMaster';
import { useQueryParams } from 'helpers/useQueryParams';
import tableRowClasses from '../../../assets/scss/components/table-row.module.scss';
import ColumnImage from '../../../components/column-image';

const { TabPane } = Tabs;

const roles = ['all', 'new', 'accepted', 'canceled', 'rejected'];

function MasterInvitations() {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const queryParams = useQueryParams();

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { invitations, loading, params, meta } = useSelector(
    (state) => state.invitations,
    shallowEqual,
  );
  const currentShopId = useSelector((state) => state.myShop?.myShop?.id);
  const { myShop } = useSelector((state) => state.myShop, shallowEqual);
  const { setIsModalVisible } = useContext(Context);

  const [role, setRole] = useState(activeMenu.data?.role || roles[0]);
  const [invitationDetails, setInvitationDetails] = useState(null);
  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);

  const [columns] = useState([
    {
      title: t('id'),
      dataIndex: 'id',
      is_show: true,
      sorter: (a, b) => a.id - b.id,
    },
    {
      title: t('image'),
      dataIndex: 'img',
      is_show: true,
      render: (img, row) => <ColumnImage size={50} image={img} id={row?.id} />,
    },
    {
      title: t('fullname'),
      dataIndex: 'fullname',
      is_show: true,
    },
    {
      title: t('phone'),
      dataIndex: 'phone',
      is_show: true,
      render: (phone) => <a href={`tel:${phone}`}>{phone}</a>,
    },
    {
      title: t('email'),
      dataIndex: 'email',
      is_show: true,
      render: (email) => <a href={`mailto:${email}`}>{email}</a>,
    },
    {
      title: t('gender'),
      dataIndex: 'gender',
      is_show: true,
    },
    {
      title: t('status'),
      is_show: true,
      dataIndex: 'status',
      key: 'status',
      render: (_, row) => {
        const currentShopInvitationIndex = row.invitations.findIndex(
          (item) => item?.shop_id === currentShopId,
        );
        const status = row?.invitations?.[currentShopInvitationIndex]?.status;
        return (
          <button
            type='button'
            className={tableRowClasses.status}
            onClick={() => {
              setInvitationDetails({
                id: row?.invitations[currentShopInvitationIndex]?.id,
                status,
              });
            }}
          >
            <span className={tableRowClasses[status || 'pending']}>
              {t(status)}
            </span>
            <EditOutlined />
          </button>
        );
      },
    },
    {
      title: t('options'),
      dataIndex: 'options',
      is_show: true,
      render: (_, row) => {
        const invite = row?.invitations?.filter(
          (item) => item?.shop_id === myShop?.id,
        )?.[0];
        return (
          <div className={tableRowClasses.options}>
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
              onClick={(e) => {
                e.stopPropagation();
                goToEdit(row?.uuid, row?.id);
              }}
              disabled={invite?.status !== 'accepted'}
            >
              <EditOutlined />
            </button>
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.clone}`}
              onClick={(e) => {
                e.stopPropagation();
                setId(row?.uuid);
                setIsModalVisible(true);
              }}
              disabled={loadingBtn}
            >
              <LoginOutlined />
            </button>
          </div>
        );
      },
    },
  ]);

  const data = activeMenu.data;

  const paramsData = {
    search: data?.search,
    sort: data?.sort,
    perPage: data?.perPage,
    page: data?.page,
    invite_status: role !== 'all' ? role : null,
  };

  if (!data?.search?.trim()?.length) delete paramsData?.search;

  const handleFilter = (items) => {
    const data = activeMenu.data;
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...data, ...items },
      }),
    );
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

  useEffect(() => {
    if (activeMenu.refetch && !loadingBtn) {
      batch(() => {
        dispatch(fetchSellerMasterInvitations(paramsData));
        dispatch(disableRefetch(activeMenu));
      });
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    if (!loadingBtn) {
      dispatch(fetchSellerMasterInvitations(paramsData));
    }
  }, [activeMenu.data]);

  const goToAdd = () => {
    dispatch(
      addMenu({
        id: 'seller-master-add',
        url: `seller/invitations/masters/add`,
        name: t('add.master'),
      }),
    );
    navigate('add', { state: { paramsData } });
  };

  const goToEdit = (uuid, id) => {
    dispatch(
      addMenu({
        id: 'seller-master-edit',
        url: `seller/invitations/masters/edit/${uuid}`,
        name: t('edit.master'),
      }),
    );
    queryParams.set('master_id', id);
    navigate(`/seller/invitations/masters/edit/${uuid}`, {
      state: { paramsData },
    });
  };

  const loginAsMaster = () => {
    setLoadingBtn(true);
    userService
      .loginAs(id)
      .then((res) => {
        const user = {
          fullName:
            res.data?.user?.firstname ||
            '' + ' ' + res.data?.user?.lastname ||
            '',
          role: 'master',
          urls: menuData.master,
          img: res.data?.user?.img,
          token: res.data?.access_token,
          email: res.data?.user.email,
          id: res.data?.user?.id,
          shop_id: res.data?.user?.shop?.id,
          walletId: res.data?.user?.wallet?.id,
        };

        batch(() => {
          dispatch(clearMenu());
          dispatch(setCurrentChat(null));
          dispatch(setUserData(user));
          dispatch(fetchRestSettings());
          dispatch(disableRefetch(activeMenu));
        });
        serviceMasterService
          .show(user.id)
          .then((res) => {
            dispatch(updateUser(res.data));
          })
          .catch((err) => {
            throw err;
          });
        localStorage.setItem('token', res.data.access_token);
        navigate('/dashboard');
        // dispatch(fetchMyShop({}));
      })
      .finally(() => {
        setLoadingBtn(false);
        setIsModalVisible(false);
      });
  };

  return (
    <>
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
            {t('masters')}
          </Typography.Title>
          <Button
            type='primary'
            icon={<PlusOutlined />}
            onClick={goToAdd}
            style={{ width: '100%' }}
          >
            {t('add.master')}
          </Button>
        </Space>
        <Divider color='var(--divider)' />
        <div
          style={{
            display: 'flex',
            flexWrap: 'wrap',
            rowGap: '6px',
            columnGap: '6px',
          }}
        >
          <Col style={{ minWidth: '253px' }}>
            <SearchInput
              placeholder={t('search')}
              handleChange={(e) => handleFilter({ search: e })}
            />
          </Col>
        </div>
        <Divider color='var(--divider)' />
        <Tabs
          className='mt-3'
          activeKey={role}
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
          locale={{
            emptyText: <RiveResult />,
          }}
          scroll={{ x: true }}
          // rowSelection={rowSelection}
          loading={loading}
          columns={columns?.filter((item) => item.is_show)}
          dataSource={invitations}
          pagination={{
            pageSize: params.perPage,
            page: activeMenu.data?.page || 1,
            total: meta.total,
            defaultCurrent: activeMenu.data?.page,
            current: activeMenu.data?.page,
          }}
          onChange={onChangePagination}
          rowKey={(record) => record.id}
        />
      </Card>
      {invitationDetails && (
        <InvitationModal
          invitationDetails={invitationDetails}
          handleCancel={() => setInvitationDetails(null)}
          paramsData={paramsData}
        />
      )}
      <CustomModal
        text={t('do.you.want.login.as.this.master')}
        click={loginAsMaster}
      />
    </>
  );
}

export default MasterInvitations;
