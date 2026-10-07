import React, { useEffect, useState } from 'react';
import { EditOutlined, EyeOutlined } from '@ant-design/icons';
import { Button, Card, Divider, Rate, Space, Table, Typography } from 'antd';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { disableRefetch, setMenuData } from '../../../redux/slices/menu';
import useDidUpdate from '../../../helpers/useDidUpdate';
import formatSortType from '../../../helpers/formatSortType';
import { useTranslation } from 'react-i18next';
import { sellerfetchShopReviews } from 'redux/slices/shop-reviews';
import ShopReviewShowModal from './shopReviewShow';
import moment from 'moment';
import FilterColumns from '../../../components/filter-column';
import { getHourFormat } from '../../../helpers/getHourFormat';
import OutlinedButton from '../../../components/outlined-button';
import tableRowClasses from '../../../assets/scss/components/table-row.module.scss';

export default function SellerOrderReviews() {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const hourFormat = getHourFormat();

  const [columns, setColumns] = useState([
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'order',
      sorter: true,
      is_show: true,
    },
    {
      title: t('user'),
      dataIndex: 'user',
      key: 'user',
      is_show: true,
      render: (user) => `${user?.firstname} ${user?.lastname}`,
    },
    {
      title: t('rating'),
      dataIndex: 'rating',
      key: 'rating',
      is_show: true,
      render: (rating) => <Rate disabled defaultValue={rating} />,
    },
    {
      title: t('created.at'),
      dataIndex: 'created_at',
      key: 'created_at',
      is_show: true,
      render: (createdAt) =>
        moment(createdAt).format(`YYYY-MM-DD ${hourFormat}`),
    },
    {
      title: t('options'),
      key: 'options',
      dataIndex: 'options',
      is_show: true,
      render: (_, row) => {
        return (
          <div className={tableRowClasses.options}>
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.details}`}
              disabled={row?.deleted_at}
              onClick={(e) => {
                e.stopPropagation();
                setShow(row.id);
              }}
            >
              <EyeOutlined />
            </button>
          </div>
        );
      },
    },
  ]);

  const [id, setId] = useState(null);
  const [show, setShow] = useState(null);

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { reviews, meta, loading, params } = useSelector(
    (state) => state.shopReviews,
    shallowEqual,
  );

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(sellerfetchShopReviews());
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    const data = activeMenu.data;
    const paramsData = {
      sort: data?.sort,
      column: data?.column,
      perPage: data?.perPage,
      page: data?.page,
      type: 'shop',
    };
    dispatch(sellerfetchShopReviews(paramsData));
  }, [activeMenu.data]);

  function onChangePagination(pagination, filters, sorter) {
    const { pageSize: perPage, current: page } = pagination;
    const { field: column, order } = sorter;
    const sort = formatSortType(order);
    dispatch(
      setMenuData({ activeMenu, data: { perPage, page, column, sort } }),
    );
  }

  const onSelectChange = (newSelectedRowKeys) => {
    setId(newSelectedRowKeys);
  };

  const rowSelection = {
    id,
    onChange: onSelectChange,
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
          {t('shop.reviews')}
        </Typography.Title>
        <FilterColumns columns={columns} setColumns={setColumns} />
      </Space>
      <Divider color='var(--divider)' />
      <Table
        scroll={{ x: true }}
        rowSelection={rowSelection}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={reviews}
        pagination={{
          pageSize: params.perPage,
          page: params.page,
          total: meta.total,
          defaultCurrent: params.page,
        }}
        rowKey={(record) => record.id}
        onChange={onChangePagination}
        loading={loading}
      />
      {show && (
        <ShopReviewShowModal id={show} handleCancel={() => setShow(null)} />
      )}
    </Card>
  );
}
