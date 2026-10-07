import React, { useContext, useEffect, useState } from 'react';
import { Card, Col, Divider, Space, Table, Tabs, Typography } from 'antd';
import { useNavigate } from 'react-router-dom';
import { DeleteOutlined, EyeOutlined } from '@ant-design/icons';
import CustomModal from '../../components/modal';
import { Context } from '../../context/context';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch, setMenuData } from '../../redux/slices/menu';
import { toast } from 'react-toastify';
import { useTranslation } from 'react-i18next';
import FilterColumn from '../../components/filter-column';
import { fetchRefund } from '../../redux/slices/refund';
import refundService from '../../services/refund';
import SearchInput from '../../components/search-input';
import useDidUpdate from '../../helpers/useDidUpdate';
import formatSortType from '../../helpers/formatSortType';
import moment from 'moment';
import { getHourFormat } from '../../helpers/getHourFormat';
import OutlinedButton from '../../components/outlined-button';
import tableRowClasses from '../../assets/scss/components/table-row.module.scss';

const { TabPane } = Tabs;
const roles = ['pending', 'accepted', 'canceled'];

const Refunds = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { refund_delete } = useSelector(
    (state) => state.globalSettings.settings,
    shallowEqual,
  );
  const hourFormat = getHourFormat();

  const goToShow = (id) => {
    dispatch(
      addMenu({
        url: `refund/details/${id}`,
        id: 'refund_details',
        name: t('refund.details'),
      }),
    );
    navigate(`/refund/details/${id}`);
  };

  const [columns, setColumns] = useState([
    {
      title: t('id'),
      is_show: true,
      dataIndex: 'id',
      key: 'id',
      sorter: true,
    },
    {
      title: t('order.id'),
      is_show: true,
      dataIndex: 'order.id',
      key: 'order.id',
      render: (_, row) => <div>{row.order.id}</div>,
    },
    {
      title: t('client'),
      is_show: true,
      dataIndex: 'user',
      key: 'user',
      render: (_, row) => (
        <div>
          {row.order.user?.firstname} {row.order.user?.lastname}
        </div>
      ),
    },
    // {
    //   title: t('shop'),
    //   is_show: true,
    //   dataIndex: 'shop',
    //   key: 'shop',
    //   render: (_, row) => <div>{row.order.shop?.translation?.title}</div>,
    // },
    {
      title: t('order.status'),
      is_show: true,
      dataIndex: 'status',
      key: 'status',
      render: (status) => {
        return (
          <button type='button' className={tableRowClasses.status} disabled>
            <span className={tableRowClasses[status || 'new']}>
              {t(status)}
            </span>
          </button>
        );
      },
    },
    {
      title: t('created.at'),
      is_show: true,
      dataIndex: 'created_at',
      key: 'created_at',
      render: (created_at) =>
        moment(created_at).format(`YYYY-MM-DD ${hourFormat}`),
    },
    {
      title: t('options'),
      is_show: true,
      key: 'options',
      render: (_, row) => {
        return (
          <div className={tableRowClasses.options}>
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.details}`}
              disabled={row?.deleted_at}
              onClick={(e) => {
                e.stopPropagation();
                goToShow(row);
              }}
            >
              <EyeOutlined />
            </button>
            {refund_delete === '0' ? null : (
              <button
                type='button'
                className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
                disabled={row?.deleted_at}
                onClick={(e) => {
                  e.stopPropagation();
                  setId([row.id]);
                  setIsModalVisible(true);
                  setText(true);
                }}
              >
                <DeleteOutlined />
              </button>
            )}
          </div>
        );
      },
    },
  ]);

  const { setIsModalVisible } = useContext(Context);
  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [text, setText] = useState(null);

  const [role, setRole] = useState('pending');
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { refund, meta, loading, params } = useSelector(
    (state) => state.refund,
    shallowEqual,
  );
  const immutable = activeMenu.data?.role || role;
  const data = activeMenu.data;
  const paramsData = {
    search: data?.search,
    pageSize: meta.per_page,
    page: data?.page,
    status: immutable || 'published',
  };

  const refundDelete = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };
    refundService
      .delete(params)
      .then(() => {
        dispatch(fetchRefund(paramsData));
        toast.success(t('successfully.deleted'));
      })
      .finally(() => {
        setIsModalVisible(false);
        setLoadingBtn(false);
        setId(null);
      });
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchRefund(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    dispatch(fetchRefund(paramsData));
  }, [activeMenu.data]);

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

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

  const allDelete = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.the.product'));
    } else {
      setIsModalVisible(true);
      setText(false);
    }
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
          {t(`refunds`)}
        </Typography.Title>
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
            resetSearch={!activeMenu.data?.search}
            defaultValue={activeMenu.data?.search}
          />
        </Col>
      </div>
      <Divider color='var(--divider)' />
      <Space className='justify-content-between align-items-start w-100'>
        <Tabs
          className='mt-3'
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
        <Space>
          {refund_delete === '0' ? null : (
            <Space>
              <OutlinedButton color='red' size='' onClick={allDelete}>
                {t('delete.selected')}
              </OutlinedButton>
            </Space>
          )}
          <FilterColumn columns={columns} setColumns={setColumns} />
        </Space>
      </Space>

      <Table
        scroll={{ x: true }}
        rowSelection={rowSelection}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={refund}
        pagination={{
          pageSize: params.perPage,
          page: activeMenu.data?.page || 1,
          total: meta.total,
          defaultCurrent: activeMenu.data?.page,
          current: activeMenu.data?.page,
        }}
        rowKey={(record) => record.id}
        loading={loading}
        onChange={onChangePagination}
      />
      <CustomModal
        click={refundDelete}
        text={text ? t('delete') : t('all.delete')}
        loading={loadingBtn}
        setText={setId}
      />
    </Card>
  );
};

export default Refunds;
