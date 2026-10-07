import React, { useEffect, useState } from 'react';
import { Table, Space, Form, Typography, Divider, Col } from 'antd';
import { fetchSellerAdverts } from 'redux/slices/advert';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import SearchInput from 'components/search-input';
import { disableRefetch } from 'redux/slices/menu';
import useDidUpdate from 'helpers/useDidUpdate';
import numberToPrice from 'helpers/numberToPrice';
import advertService from 'services/seller/advert';
import { toast } from 'react-toastify';
import { EyeFilled } from '@ant-design/icons';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import AdDetail from './ad-detail';
import AssignProduct from './assign-product';
import Card from 'components/card';

export default function Advert() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const [form] = Form.useForm();
  const [id, setId] = useState(null);
  const [isAddProduct, setIsAddProduct] = useState(null);

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const {
    advertList,
    loading: listLoading,
    meta,
  } = useSelector((state) => state.advert, shallowEqual);

  const [search, setSearch] = useState('');
  const [loading, setLoading] = useState(false);

  const handlePurchase = ({ product_ids, ads_package_id }) => {
    setLoading(true);
    advertService
      .create({ ads_package_id, product_ids })
      .then(() => {
        toast.success(t('assigned.successfully'));
        form.resetFields();
      })
      .finally(() => {
        setLoading(false);
        setIsAddProduct(null);
      });
  };

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      is_show: true,
      sorter: (a, b) => a.id - b.id,
    },
    {
      title: t('title'),
      dataIndex: 'title',
      is_show: true,
      render: (_, row) => row?.translation?.title,
    },
    {
      title: t('price'),
      dataIndex: 'price',
      is_show: true,
      render: (price, row) => numberToPrice(price, row?.currency?.symbol),
    },

    {
      title: t('time'),
      dataIndex: 'time',
      is_show: true,
      render: (time, row) => `${time} ${t(row?.time_type)}`,
    },
    {
      title: t('actions'),
      dataIndex: 'actions',
      is_show: true,
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.details}`}
            onClick={(e) => {
              e.stopPropagation();
              setId(row?.id);
            }}
          >
            <EyeFilled />
          </button>
          <button
            type='button'
            onClick={() => setIsAddProduct(row.id)}
            style={{
              display: 'flex',
              columnGap: '5px',
              border: '1px solid #ccc',
              borderRadius: '6px',
              padding: '5px',
            }}
          >
            <span>{t('assign')}</span>
          </button>
        </div>
      ),
    },
  ];
  const [columns, setColumns] = useState(initialColumns);

  const paramsData = {
    perPage: 10,
    page: 1,
    active: 1,
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchSellerAdverts(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    const paramsData = {
      search,
    };
    dispatch(fetchSellerAdverts(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu.data, search]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;
    dispatch(fetchSellerAdverts({ perPage: pageSize, page: current }));
    dispatch(disableRefetch(activeMenu));
  };

  return (
    <>
      <Card>
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
          {t('ad.packages')}
        </Typography.Title>
        <Divider color='var(--divider)' />
        <Space
          className='w-100 justify-content-end align-items-center'
          style={{ rowGap: '6px', columnGap: '6px', marginBottom: '20px' }}
        >
          <Col style={{ minWidth: '228px' }}>
            <SearchInput
              className='w-100'
              placeholder={t('search')}
              handleChange={(value) => setSearch(value)}
            />
          </Col>
        </Space>
        <Table
          scroll={{ x: true }}
          dataSource={advertList}
          columns={columns?.filter((item) => item.is_show)}
          rowKey={(record) => record.id}
          loading={listLoading || loading}
          pagination={{
            pageSize: meta?.per_page,
            page: meta?.current_page,
            current: meta?.current_page,
            total: meta?.total,
          }}
          onChange={onChangePagination}
        />
      </Card>
      <AdDetail id={id} onClose={() => setId(null)} />
      <AssignProduct
        id={isAddProduct}
        onClose={() => setIsAddProduct(null)}
        handlePurchase={handlePurchase}
        loading={loading}
        form={form}
      />
    </>
  );
}
