import React, { useContext, useEffect, useState } from 'react';
import {
  CopyOutlined,
  DeleteOutlined,
  EditOutlined,
  EyeOutlined,
  PlusOutlined,
} from '@ant-design/icons';
import {
  Button,
  Space,
  Table,
  Tabs,
  Tag,
  Switch,
  Typography,
  Divider,
  Col,
} from 'antd';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { Context } from 'context/context';
import CustomModal from 'components/modal';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import shopService from 'services/shop';
import { fetchShops } from 'redux/slices/shop';
import { useTranslation } from 'react-i18next';
import ShopStatusModal from './shop-status-modal';
import SearchInput from 'components/search-input';
import useDidUpdate from 'helpers/useDidUpdate';
import FilterColumns from 'components/filter-column';
import formatSortType from 'helpers/formatSortType';
import { useQueryParams } from 'helpers/useQueryParams';
import ShopDetailsModal from './details-modal';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import ColumnImage from 'components/column-image';
import Card from 'components/card';
import OutlinedButton from 'components/outlined-button';
import cls from './index.module.scss';

const { TabPane } = Tabs;
const roles = ['all', 'new', 'approved', 'rejected'];

const Shops = () => {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const queryParams = useQueryParams();
  const [shopStatus, setShopStatus] = useState(null);
  const { user } = useSelector((state) => state.auth, shallowEqual);
  const [text, setText] = useState(null);
  const [role, setRole] = useState('all');
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const immutable = activeMenu.data?.role || role;
  const { setIsModalVisible } = useContext(Context);
  const [id, setId] = useState(null);
  const [verify, setVerify] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const { shops, meta, loading, params } = useSelector(
    (state) => state.shop,
    shallowEqual,
  );

  const goToEdit = (row) => {
    dispatch(
      addMenu({
        id: 'edit-shop',
        url: `shop/${row.uuid}`,
        name: t('edit.shop'),
      }),
    );
    navigate(`/shop/${row.uuid}`, { state: 'edit' });
  };

  const goToClone = (row) => {
    dispatch(
      addMenu({
        id: 'shop-clone',
        url: `shop-clone/${row.uuid}`,
        name: t('shop.clone'),
      }),
    );
    navigate(`/shop-clone/${row.uuid}`, { state: 'clone' });
  };

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      is_show: true,
      key: 'id',
    },
    {
      title: t('title'),
      dataIndex: 'name',
      is_show: true,
      key: 'title',
    },
    {
      title: t('translations'),
      dataIndex: 'locales',
      key: 'locales',
      is_show: true,
      render: (locales) => (
        <div className={tableRowClasses.locales}>
          {locales?.map((item, index) => (
            <div
              key={item}
              className={`${tableRowClasses.locale} ${1 & index ? tableRowClasses.odd : tableRowClasses.even}`}
            >
              {item}
            </div>
          ))}
        </div>
      ),
    },
    {
      title: t('logo'),
      dataIndex: 'logo_img',
      is_show: true,
      key: 'logo',
      render: (img, row) => <ColumnImage size={50} image={img} id={row?.id} />,
    },
    {
      title: t('background'),
      dataIndex: 'back',
      is_show: true,
      render: (img, row) => <ColumnImage size={50} image={img} id={row?.id} />,
    },
    {
      title: t('seller'),
      dataIndex: 'seller',
      is_show: true,
      key: 'seller',
    },
    {
      title: t('open.time'),
      dataIndex: 'open',
      is_show: true,
      key: 'open.time',
      render: (_, row) =>
        row.open ? (
          <Tag color='blue'> {t('open')} </Tag>
        ) : (
          <Tag color='red'> {t('closed')} </Tag>
        ),
    },
    {
      title: t('tax'),
      is_show: true,
      dataIndex: 'tax',
      key: 'tax',
      render: (tax) => (
        <div style={{ width: 'max-content' }}>{`${tax ?? 0} %`}</div>
      ),
    },
    {
      title: t('verify'),
      dataIndex: 'verify',
      is_show: true,
      render: (active, row) => (
        <Switch
          onChange={() => {
            setIsModalVisible(true);
            setId(row.uuid);
            setVerify(true);
          }}
          checked={active}
        />
      ),
    },
    {
      title: t('status'),
      is_show: true,
      dataIndex: 'status',
      key: 'status',
      render: (status, row) => (
        <button
          type='button'
          className={tableRowClasses.status}
          onClick={() => {
            setShopStatus(row);
          }}
        >
          <span className={tableRowClasses[status || 'pending']}>
            {t(status)}
          </span>
          <EditOutlined />
        </button>
      ),
    },
    {
      title: t('actions'),
      dataIndex: 'actions',
      key: 'actions',
      is_show: true,
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.details}`}
            onClick={(e) => {
              e.stopPropagation();
              queryParams.set('shopId', row?.id);
            }}
          >
            <EyeOutlined />
          </button>
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
            className={`${tableRowClasses.option} ${tableRowClasses.clone}`}
            onClick={(e) => {
              e.stopPropagation();
              goToClone(row);
            }}
          >
            <CopyOutlined />
          </button>
          {user?.role !== 'manager' && (
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
              onClick={() => {
                setId([row?.id]);
                setIsModalVisible(true);
                setText(true);
                setVerify(false);
              }}
            >
              <DeleteOutlined />
            </button>
          )}
        </div>
      ),
    },
  ];
  const [columns, setColumns] = useState(initialColumns);

  const data = activeMenu?.data;
  const paramsData = {
    search: data?.search,
    lang: data?.filter?.equal === 'equal' ? data?.filter?.lang : i18n.language,
    not_lang: data?.filter?.equal === 'not_equal' ? data?.filter?.lang : null,
    status: immutable === 'all' ? undefined : immutable,
    page: data?.page,
    perPage: data?.perPage,
    sort: data?.sort,
    column: data?.column,
  };

  const shopDelete = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };
    shopService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setIsModalVisible(false);
        dispatch(fetchShops(paramsData));
        setText(null);
        setVerify(false);
        setId(null);
      })
      .finally(() => setLoadingBtn(false));
  };

  const handleVerify = () => {
    setLoadingBtn(true);
    shopService
      .setVerify(id)
      .then(() => {
        setIsModalVisible(false);
        toast.success(t('successfully.updated'));
        dispatch(fetchShops(paramsData));
        setVerify(false);
      })
      .finally(() => {
        setLoadingBtn(false);
        setId(null);
      });
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
    if (activeMenu.refetch) {
      dispatch(fetchShops(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    dispatch(fetchShops(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [data]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const goToAdd = () => {
    dispatch(
      addMenu({
        id: 'add-shop',
        url: `shop/add`,
        name: t('add.shop'),
      }),
    );
    navigate(`/shop/add`);
  };

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
      toast.warning(t('select.shop'));
    } else {
      setIsModalVisible(true);
      setText(false);
    }
  };

  return (
    <div className={cls.page}>
      <Card>
        <Space
          className={`${cls.header} align-items-center justify-content-between w-100`}
        >
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
            {t('shops')}
          </Typography.Title>
          <Button type='primary' icon={<PlusOutlined />} onClick={goToAdd}>
            {t('add.shop')}
          </Button>
        </Space>
        <Divider color='var(--divider)' />
        <Space
          className='w-100 justify-content-between align-items-center'
          style={{ rowGap: '6px', columnGap: '6px' }}
        >
          <Col style={{ minWidth: '228px' }}>
            <SearchInput
              placeholder={t('search')}
              handleChange={(e) => handleFilter({ search: e })}
              defaultValue={activeMenu.data?.search}
              resetSearch={!activeMenu.data?.search}
              className='w-100'
            />
          </Col>
        </Space>
        <Divider color='var(--divider)' />
        <Space
          className={`${cls.controls} w-100 justify-content-between align-items-start`}
        >
          <Tabs
            className={cls.statusTabs}
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
          <Space className='gap-10'>
            <OutlinedButton onClick={allDelete} color='red'>
              {t('delete.selection')}
            </OutlinedButton>
            <FilterColumns columns={columns} setColumns={setColumns} />
          </Space>
        </Space>
        <div
          className={cls.tableViewport}
          role='region'
          aria-label={t('shops')}
          tabIndex={0}
        >
          <Table
            scroll={{ x: true }}
            rowSelection={rowSelection}
            columns={columns?.filter((item) => item.is_show)}
            dataSource={shops}
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
        </div>
        {shopStatus && (
          <ShopStatusModal
            data={shopStatus}
            handleCancel={() => setShopStatus(null)}
            paramsData={paramsData}
          />
        )}
        <CustomModal
          click={verify ? handleVerify : shopDelete}
          text={
            verify
              ? t('set.verify.product')
              : text
                ? t('delete')
                : t('all.delete')
          }
          loading={loadingBtn}
          setText={setId}
          setVerify={setVerify}
        />
      </Card>
      <ShopDetailsModal />
    </div>
  );
};

export default Shops;
