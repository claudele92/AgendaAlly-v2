import React, { useEffect, useState, useContext } from 'react';
import { Table, Space, Tag, Image, Typography, Divider } from 'antd';
import { DeleteOutlined, EditOutlined } from '@ant-design/icons';
import { useDispatch, useSelector, shallowEqual } from 'react-redux';
import { fetchShopAds } from 'redux/slices/shop-ads';
import { Context } from 'context/context';
import { useTranslation } from 'react-i18next';
import { toast } from 'react-toastify';
import FilterColumns from 'components/filter-column';
import { disableRefetch, setMenuData } from 'redux/slices/menu';
import useDidUpdate from 'helpers/useDidUpdate';
import CustomModal from 'components/modal';
import ShopAdsStatusModal from './shop-ads-status-modal';
import shopAdsService from 'services/shop-ads';
import formatSortType from 'helpers/formatSortType';
import { IMG_URL } from 'configs/app-global';
import Card from 'components/card';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import TransactionStatusModal from './transaction-status-modal';
import OutlinedButton from 'components/outlined-button';
import getFullDateTime from 'helpers/getFullDateTime';
import ColumnImage from '../../components/column-image';

export default function ShopAds() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const {
    shopAdsList,
    loading: listLoading,
    meta,
  } = useSelector((state) => state.shopAds, shallowEqual);
  const { setIsModalVisible } = useContext(Context);

  const [id, setId] = useState(null);
  const [text, setText] = useState(null);
  const [active, setActive] = useState(null);
  const [loading, setLoading] = useState(false);
  const [shopAdsDetails, setShopAdsDetails] = useState(null);
  const [singleAds, setSingleAds] = useState([]);
  const [loadingSingleAds, setLoadingSingleAds] = useState(false);
  const [expandId, setExpandId] = useState(null);
  const [transactionDetail, setTransactionDetail] = useState(null);

  const data = activeMenu.data;

  const paramsData = {
    search: data?.serach,
    perPage: data?.perPage,
    page: data?.page,
    sort: data?.sort,
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchShopAds(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    dispatch(fetchShopAds(paramsData));
  }, [activeMenu.data]);

  const onChangePagination = (pagination, filter, sorter) => {
    const { pageSize: perPage, current: page } = pagination;
    const { field: column, order } = sorter;
    const sort = formatSortType(order);
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...activeMenu.data, perPage, page, column, sort },
      }),
    );
  };

  const shopAdsDelete = () => {
    setLoading(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };

    shopAdsService
      .delete(params)
      .then(() => {
        setIsModalVisible(false);
        toast.success(t('successfully.deleted'));
        dispatch(fetchShopAds(paramsData));
        setText(null);
        setActive(false);
      })
      .finally(() => {
        setLoading(false);
        setId(null);
      });
  };

  const handleActive = () => {
    setLoading(true);
    shopAdsService
      .setActive(id)
      .then(() => {
        setIsModalVisible(false);
        dispatch(fetchShopAds(paramsData));
        toast.success(t('successfully.updated'));
        setActive(false);
      })
      .finally(() => setLoading(false));
  };

  const allDelete = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.shop.ad'));
    } else {
      setIsModalVisible(true);
      setText(false);
    }
  };

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

  const showAds = ({ expanded, record }) => {
    setSingleAds([]);
    if (expanded) {
      setLoadingSingleAds(true);
      setExpandId(record?.id);
      shopAdsService
        .getById(record?.id)
        .then((res) => {
          setSingleAds(res.data?.shop_ads_products);
        })
        .finally(() => setLoadingSingleAds(false));
    } else {
      setExpandId(null);
    }
  };

  const expandedRowRender = () => {
    const columns = [
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
        render: (_, row) => (
          <ColumnImage image={row?.product?.img} id={row?.id} />
        ),
      },
      {
        title: t('name'),
        dataIndex: 'name',
        is_show: true,
        render: (img, row) => row.product?.translation?.title,
      },
      {
        title: t('active'),
        dataIndex: 'active',
        is_show: true,
        render: (img, row) =>
          row.product?.active ? t('active') : t('inactive'),
      },
      {
        title: t('status'),
        is_show: true,
        dataIndex: 'status',
        key: 'status',
        render: (status) => (
          <div className={tableRowClasses.status}>
            <span className={tableRowClasses[status || 'pending']}>
              {t(status)}
            </span>
          </div>
        ),
      },
    ];

    return (
      <Table
        columns={columns}
        dataSource={singleAds}
        pagination={false}
        loading={loadingSingleAds}
      />
    );
  };

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      is_show: true,
      sorter: (a, b) => a.id - b.id,
    },
    {
      title: t('shop'),
      dataIndex: 'shop',
      is_show: true,
      render: (shop) => shop?.translation?.title,
    },
    {
      title: t('expired.at'),
      dataIndex: 'expired_at',
      is_show: true,
      render: (expiredAt) => (
        <>
          {expiredAt ? (
            getFullDateTime(expiredAt)
          ) : (
            <span>{t('not.expired')}</span>
          )}
        </>
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
          onClick={(e) => {
            e.stopPropagation();
            setShopAdsDetails(row);
          }}
          className={tableRowClasses.status}
        >
          <span className={tableRowClasses[status || 'new']}>{t(status)}</span>
          <EditOutlined size={12} />
        </button>
      ),
    },
    {
      title: t('transaction'),
      dataIndex: 'transaction',
      is_show: true,
      render: (transaction) => (
        <div className={tableRowClasses.paymentStatuses}>
          {transaction ? (
            <button
              type='button'
              className='cursor-pointer d-flex align-items-center'
              style={{ columnGap: '5px' }}
              onClick={(e) => {
                e.stopPropagation();
                setTransactionDetail(transaction);
              }}
            >
              <span
                className={`${tableRowClasses.paymentStatus} ${tableRowClasses[transaction?.status]}`}
              >
                {t(transaction?.status)}
              </span>
              <EditOutlined size={18} />
            </button>
          ) : (
            <span>{t('not.paid')}</span>
          )}
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
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            onClick={() => {
              setIsModalVisible(true);
              setId([row.id]);
              setText(true);
              setActive(false);
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ];

  const [columns, setColumns] = useState(initialColumns);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

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
          {t('shop.ads')}
        </Typography.Title>
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
          onExpand={(expanded, record) => showAds({ expanded, record })}
          expandable={{
            expandedRowRender,
            defaultExpandedRowKeys: ['0'],
            expandedRowKeys: [expandId],
          }}
          scroll={{ x: true }}
          dataSource={shopAdsList}
          columns={columns?.filter((item) => item?.is_show)}
          rowSelection={rowSelection}
          rowKey={(record) => record.id}
          loading={loading || listLoading}
          pagination={{
            pageSize: meta.per_page,
            page: meta.current_page,
            total: meta.total,
          }}
          onChange={onChangePagination}
        />
      </Card>
      {shopAdsDetails && (
        <ShopAdsStatusModal
          data={shopAdsDetails}
          handleCancel={() => setShopAdsDetails(null)}
          paramsData={paramsData}
        />
      )}
      <CustomModal
        click={active ? handleActive : shopAdsDelete}
        text={
          active ? t('set.active.advert') : text ? t('delete') : t('all.delete')
        }
        setText={setId}
        setActive={setActive}
      />
      {transactionDetail && (
        <TransactionStatusModal
          data={transactionDetail}
          handleCancel={() => setTransactionDetail(false)}
          paramsData={paramsData}
        />
      )}
    </>
  );
}
