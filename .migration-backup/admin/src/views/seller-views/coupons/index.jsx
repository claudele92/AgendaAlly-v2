import React, { useContext, useEffect, useState } from 'react';
import { Button, Divider, Space, Table, Tag, Typography } from 'antd';
import { useNavigate } from 'react-router-dom';
import {
  DeleteOutlined,
  EditOutlined,
  PlusCircleOutlined,
  PlusOutlined,
} from '@ant-design/icons';
import { Context } from 'context/context';
import CustomModal from 'components/modal';
import { toast } from 'react-toastify';
import couponService from 'services/seller/coupon';
import { batch, shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import { useTranslation } from 'react-i18next';
import moment from 'moment';
import DeleteButton from 'components/delete-button';
import FilterColumns from 'components/filter-column';
import useDidUpdate from 'helpers/useDidUpdate';
import { fetchCoupon } from 'redux/slices/sellerCoupons';
import formatSortType from 'helpers/formatSortType';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';
import getFullDateTime from 'helpers/getFullDateTime';
import OutlinedButton from '../../../components/outlined-button';

const SellerCoupon = () => {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();

  const goToEdit = (row) => {
    dispatch(
      addMenu({
        url: `seller/coupons/${row.id}`,
        id: 'coupon_edit',
        name: t('edit.coupon'),
      }),
    );
    navigate(`${row.id}`);
  };

  const goToAdd = () => {
    dispatch(
      addMenu({
        id: 'add.coupon',
        url: `seller/coupons/add`,
        name: t('add.coupon'),
      }),
    );
    navigate(`add`);
  };

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      is_show: true,
    },
    {
      title: t('title'),
      dataIndex: 'title',
      is_show: true,
      render: (_, row) => row.translation?.title,
    },
    {
      title: t('name'),
      dataIndex: 'name',
      is_show: true,
    },
    {
      title: t('type'),
      dataIndex: 'type',
      is_show: true,
      render: (type) => t(type),
    },
    {
      title: t('price'),
      dataIndex: 'price',
      is_show: true,
    },
    {
      title: t('quantity'),
      dataIndex: 'qty',
      is_show: true,
    },
    {
      title: t('expired.at'),
      dataIndex: 'expired_at',
      is_show: true,
      render: (date) => (
        <div className={tableRowClasses.status}>
          <span
            className={`${moment(new Date()).isBefore(date) ? tableRowClasses.published : tableRowClasses.unpublished}`}
          >
            {getFullDateTime(date, false)}
          </span>
        </div>
      ),
    },
    {
      title: t('actions'),
      dataIndex: 'actions',
      is_show: true,
      render: (_, row) => (
        <div className={tableRowClasses.options}>
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
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            onClick={() => {
              setCouponId([row.id]);
              setIsModalVisible(true);
              setText(true);
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ];
  const [columns, setColumns] = useState(initialColumns);

  const { setIsModalVisible } = useContext(Context);
  const [couponId, setCouponId] = useState(null);
  const [text, setText] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { coupons, meta, loading, params } = useSelector(
    (state) => state.sellerCoupons,
    shallowEqual,
  );
  const data = activeMenu.data;
  const paramsData = {
    search: data?.search || undefined,
    sort: data?.sort,
    status: data?.role,
    column: data?.column,
    perPage: data?.perPage,
    page: data?.page,
  };

  function deleteCoupon() {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...couponId.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };
    couponService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setCouponId(null);
        setIsModalVisible(false);
        dispatch(fetchCoupon(paramsData));
        setText(null);
      })
      .finally(() => setLoadingBtn(false));
  }

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
      batch(() => {
        dispatch(fetchCoupon(paramsData));
        dispatch(disableRefetch(activeMenu));
      });
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    dispatch(fetchCoupon(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu.data]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const rowSelection = {
    selectedRowKeys: couponId,
    onChange: (key) => {
      setCouponId(key);
    },
  };

  const allDelete = () => {
    if (couponId === null || couponId.length === 0) {
      toast.warning(t('select.coupon'));
    } else {
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
          {t('coupons')}
        </Typography.Title>
        <Button
          type='primary'
          icon={<PlusOutlined />}
          onClick={goToAdd}
          style={{ width: '100%' }}
        >
          {t('add.coupon')}
        </Button>
      </Space>
      <Divider color='var(--divider)' />
      <Space
        className='w-100 justify-content-end align-items-center'
        style={{ rowGap: '6px', columnGap: '6px', marginBottom: '20px' }}
      >
        <OutlinedButton onClick={allDelete} color='red'>
          {t('delete.selection')}
        </OutlinedButton>
        <FilterColumns columns={columns} setColumns={setColumns} />
      </Space>
      <Table
        scroll={{ x: true }}
        rowSelection={rowSelection}
        rowKey={(record) => record.id}
        dataSource={coupons}
        columns={columns?.filter((item) => item.is_show)}
        pagination={{
          pageSize: params.perPage,
          page: activeMenu.data?.page || 1,
          total: meta.total,
          defaultCurrent: activeMenu.data?.page,
          current: activeMenu.data?.page,
        }}
        loading={loading}
        onChange={onChangePagination}
      />
      <CustomModal
        click={deleteCoupon}
        text={text ? t('delete') : t('all.delete')}
        setText={setCouponId}
        loading={loadingBtn}
      />
    </Card>
  );
};

export default SellerCoupon;
