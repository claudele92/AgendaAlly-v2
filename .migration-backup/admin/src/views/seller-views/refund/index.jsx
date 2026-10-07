import React, { useContext, useEffect, useState } from 'react';
import { Button, Card, Divider, Space, Table, Tag, Typography } from 'antd';
import { useNavigate } from 'react-router-dom';
import { DeleteOutlined, EyeOutlined } from '@ant-design/icons';
import CustomModal from '../../../components/modal';
import { Context } from '../../../context/context';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch } from '../../../redux/slices/menu';
import { toast } from 'react-toastify';
import { useTranslation } from 'react-i18next';
import FilterColumn from '../../../components/filter-column';
import { sellerfetchRefund } from '../../../redux/slices/refund';
import refundService from '../../../services/seller/refund';
import moment from 'moment/moment';
import { getHourFormat } from '../../../helpers/getHourFormat';
import OutlinedButton from '../../../components/outlined-button';
import tableRowClasses from '../../../assets/scss/components/table-row.module.scss';

export default function SellerRefunds() {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const hourFormat = getHourFormat();

  const goToShow = (id) => {
    dispatch(
      addMenu({
        url: `seller/refund/details/${id}`,
        id: 'refund_details',
        name: t('refund.details'),
      }),
    );
    navigate(`/seller/refund/details/${id}`);
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
      title: t('client'),
      is_show: true,
      dataIndex: 'user',
      key: 'user',
      render: (user, row) => (
        <div>
          {row.order.user?.firstname} {row.order.user?.lastname || ''}
        </div>
      ),
    },
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
      render: (data, row) => {
        return (
          <div className={tableRowClasses.options}>
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.details}`}
              disabled={row?.deleted_at}
              onClick={(e) => {
                e.stopPropagation();
                goToShow(row.id);
              }}
            >
              <EyeOutlined />
            </button>
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
          </div>
        );
      },
    },
  ]);

  const { setIsModalVisible } = useContext(Context);
  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [text, setText] = useState(null);

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { refund, meta, loading } = useSelector(
    (state) => state.refund,
    shallowEqual,
  );

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
        dispatch(sellerfetchRefund());
        toast.success(t('successfully.deleted'));
      })
      .finally(() => {
        setIsModalVisible(false);
        setLoadingBtn(false);
      });
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(sellerfetchRefund({}));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;
    dispatch(sellerfetchRefund({ perPage: pageSize, page: current }));
  };

  const onSelectChange = (newSelectedRowKeys) => {
    setId(newSelectedRowKeys);
  };

  const rowSelection = {
    id,
    onChange: onSelectChange,
  };

  const allDelete = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.the.product'));
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
          {t(`refunds`)}
        </Typography.Title>
      </Space>
      <Divider color='var(--divider)' />
      <Space className='justify-content-end align-items-start w-100 mb-3'>
        <Space>
          <OutlinedButton color='red' size='' onClick={allDelete}>
            {t('delete.selected')}
          </OutlinedButton>
          <FilterColumn columns={columns} setColumns={setColumns} />
        </Space>
      </Space>
      <Table
        scroll={{ x: true }}
        rowSelection={rowSelection}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={refund}
        pagination={{
          pageSize: meta.per_page,
          page: meta.current_page,
          total: meta.total,
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
}
