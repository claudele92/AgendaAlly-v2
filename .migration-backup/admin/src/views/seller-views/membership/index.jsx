import { Button, Col, Divider, Space, Table, Typography } from 'antd';
import { useTranslation } from 'react-i18next';
import { shallowEqual, useSelector, useDispatch, batch } from 'react-redux';
import useDidUpdate from 'helpers/useDidUpdate';
import {
  fetchMemberShip,
  fetchSellerMembership,
} from 'redux/slices/membership';
import React, { Fragment, useContext, useEffect, useState } from 'react';
import { addMenu, disableRefetch } from 'redux/slices/menu';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import DeleteButton from 'components/delete-button';
import { Context } from 'context/context';
import { toast } from 'react-toastify';
import membershipService from 'services/seller/membership';
import CustomModal from 'components/modal';
import { useNavigate } from 'react-router-dom';
import useDebounce from 'helpers/useDebounce';
import SearchInput from 'components/search-input';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';

export default function Membership() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { setIsModalVisible } = useContext(Context);
  const { params, memberShipData, loading, meta } = useSelector(
    (state) => state.membership.seller,
    shallowEqual,
  );
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [search, setSearch] = useState('');
  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      is_show: true,
    },
    {
      title: t('name'),
      dataIndex: 'translation',
      key: 'translation',
      is_show: true,
      render: (translation) => translation?.title,
    },
    {
      title: t('price'),
      dataIndex: 'price',
      key: 'price',
      is_show: true,
    },
    { title: t('time'), dataIndex: 'time', key: 'time', is_show: true },
    {
      title: t('actions'),
      key: 'actions',
      is_show: true,
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            onClick={(e) => {
              e.stopPropagation();
              goToEdit(row?.id);
            }}
          >
            <EditOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            onClick={() => {
              setIsModalVisible(true);
              setId([row?.id]);
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ];
  const [columns, setColumns] = useState(initialColumns);
  const debounceSearchValue = useDebounce(search, 200);
  const paramsData = {
    ...params,
    search: debounceSearchValue || undefined,
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      batch(() => {
        dispatch(fetchSellerMembership(paramsData));
        dispatch(disableRefetch(activeMenu));
      });
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    dispatch(fetchSellerMembership(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [debounceSearchValue]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const handleDeleteMembership = () => {
    setLoadingBtn(true);

    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };

    membershipService
      .delete(params)
      .then(() => {
        dispatch(fetchSellerMembership({ paramsData }));
        setIsModalVisible(false);
        setId(null);
        toast.success(t('successfully.updated'));
      })
      .finally(() => setLoadingBtn(false));
  };

  const onChangePagination = (pagination) => {
    const { pageSize, current } = pagination;
    const params = {
      ...paramsData,
      perPage: pageSize,
      page: current,
    };
    batch(() => {
      dispatch(fetchMemberShip(params));
      dispatch(disableRefetch(activeMenu));
    });
  };

  const goToAdd = () => {
    dispatch(
      addMenu({
        id: 'seller.membership.add',
        url: 'seller/membership/add',
        name: 'add.membership',
      }),
    );
    navigate('add');
  };

  const goToEdit = (id) => {
    dispatch(
      addMenu({
        id: 'seller.membership.edit',
        url: `seller/membership/edit/${id}`,
        name: 'edit.membership',
      }),
    );
    navigate(`edit/${id}`, { state: { paramsData } });
  };

  return (
    <Fragment>
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
            {t('memberships')}
          </Typography.Title>
          <Button
            type='primary'
            icon={<PlusOutlined />}
            onClick={goToAdd}
            style={{ width: '100%' }}
          >
            {t('add.membership')}
          </Button>
        </Space>
        <Divider color='var(--divider)' />
        <Space
          className='w-100 align-items-center'
          style={{ rowGap: '6px', columnGap: '6px' }}
        >
          <Col style={{ minWidth: '228px' }}>
            <SearchInput
              handleChange={(e) => setSearch(e)}
              placeholder={t('search')}
              className='w-100'
            />
          </Col>
        </Space>
        <Divider color='var(--divider)' />
        <Table
          scroll={{ x: true }}
          rowKey={(record) => record.id}
          columns={columns?.filter((column) => column?.is_show)}
          dataSource={memberShipData}
          loading={loading}
          pagination={{
            pageSize: meta?.per_page,
            current: meta?.current_page,
            total: meta?.total,
          }}
          onChange={onChangePagination}
        />
      </Card>
      <CustomModal
        click={handleDeleteMembership}
        text={t('are.you.sure.you.want.to.delete.the.selected.products')}
        loading={loadingBtn}
      />
    </Fragment>
  );
}
