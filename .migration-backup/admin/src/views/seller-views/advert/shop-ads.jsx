import React, { useEffect, useState } from 'react';
import { Button, Table, Space, Typography, Divider, Col } from 'antd';
import { fetchShopAdverts } from 'redux/slices/advert';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import SearchInput from 'components/search-input';
import FilterColumns from 'components/filter-column';
import { disableRefetch } from 'redux/slices/menu';
import useDidUpdate from 'helpers/useDidUpdate';
import numberToPrice from 'helpers/numberToPrice';
import { EyeFilled } from '@ant-design/icons';
import AdDetail from './ad-detail';
import moment from 'moment';
import AdPurchaseModal from './ad-purchase-modal';
import { getHourFormat } from 'helpers/getHourFormat';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';

export default function Advert() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const [id, setId] = useState(null);
  const [buyingAd, setBuyingAd] = useState(null);

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const {
    shopAdList,
    loading: listLoading,
    meta,
  } = useSelector((state) => state.advert, shallowEqual);

  const [search, setSearch] = useState('');
  const hourFormat = getHourFormat();

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      is_show: true,
      sorter: (a, b) => a.id - b.id,
    },
    {
      title: t('ad.package'),
      dataIndex: 'ads_package',
      is_show: true,
      render: (ad) => {
        return (
          <Space>
            <span>{ad?.translation?.title}</span>
            <Button icon={<EyeFilled />} onClick={() => setId(ad.id)} />
          </Space>
        );
      },
    },
    {
      title: t('price'),
      dataIndex: 'price',
      is_show: true,
      render: (_, row) =>
        numberToPrice(
          row?.ads_package?.price,
          row?.ads_package?.currency?.symbol,
        ),
    },
    {
      title: t('expired.at'),
      dataIndex: 'expired_at',
      is_show: true,
      render: (_, row) =>
        moment(row?.expired_at).format(`YYYY-MM-DD ${hourFormat}`),
    },
    {
      title: t('status'),
      dataIndex: 'status',
      is_show: true,
      render: (status) => (
        <div className={tableRowClasses.status}>
          <span className={tableRowClasses[status || 'pending']}>
            {t(status)}
          </span>
        </div>
      ),
    },
    {
      title: t('transaction'),
      dataIndex: 'transaction',
      is_show: true,
      render: (transaction) => (
        <div className={tableRowClasses.paymentStatuses}>
          <span
            className={`${tableRowClasses.paymentStatus} ${tableRowClasses[transaction?.status || 'progress']}`}
          >
            {t(transaction?.status || 'not.paid')}
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
          <Button
            onClick={() => {
              setBuyingAd(row);
            }}
            disabled={row?.transaction?.status === 'paid'}
          >
            {t('purchase')}
          </Button>
        </div>
      ),
    },
  ];
  const [columns, setColumns] = useState(initialColumns);

  const paramsData = {
    perPage: 10,
    page: 1,
    sort: 'desc',
    column: 'created_at',
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchShopAdverts(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    const paramsData = {
      search: search || undefined,
    };
    dispatch(fetchShopAdverts(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu.data, search]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;
    dispatch(fetchShopAdverts({ perPage: pageSize, page: current }));
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
          {t('ads')}
        </Typography.Title>
        <Divider color='var(--divider)' />
        <Space
          className='w-100 justify-content-end align-items-center'
          style={{ rowGap: '6px', columnGap: '6px', marginBottom: '20px' }}
        >
          <Col style={{ minWidth: '228px' }}>
            <SearchInput
              className='w-100'
              handleChange={(value) => setSearch(value)}
              placeholder={t('search')}
            />
          </Col>
          <FilterColumns columns={columns} setColumns={setColumns} />
        </Space>
        <Table
          scroll={{ x: true }}
          dataSource={shopAdList}
          columns={columns?.filter((item) => item.is_show)}
          rowKey={(record) => record.id}
          loading={listLoading}
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
      {buyingAd && (
        <AdPurchaseModal
          buyingAd={buyingAd}
          handleCancel={() => setBuyingAd(null)}
        />
      )}
    </>
  );
}
