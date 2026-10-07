import React, { useContext, useEffect, useState } from 'react';
import { Table, Button, Space, Modal, Typography, Divider } from 'antd';
import { useTranslation } from 'react-i18next';
import { EditOutlined, EyeOutlined, MessageOutlined } from '@ant-design/icons';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { toast } from 'react-toastify';
import CustomModal from 'components/modal';
import { Context } from 'context/context';
import { useNavigate } from 'react-router-dom';
import FilterColumns from 'components/filter-column';
import formatSortType from 'helpers/formatSortType';
import useDidUpdate from 'helpers/useDidUpdate';
import { fetchRequestModels } from 'redux/slices/request-models';
import { HiArrowNarrowRight } from 'react-icons/hi';
import requestAdminModelsService from 'services/request-models';
import moment from 'moment';
import ProductStatusModal from './productStatusModal';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';
import ColumnImage from 'components/column-image';

const body = {
  type: 'product',
};

export default function ProductRequestList({ parentId }) {
  const { t, i18n } = useTranslation();
  const { setIsModalVisible } = useContext(Context);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [isVisibleMsgModal, setIsVisibleMsgModal] = useState(false);
  const [modalText, setModalText] = useState('');
  const [id, setId] = useState(null);
  const [productDetails, setProductDetails] = useState(null);
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const {
    data: requests,
    meta,
    loading,
    params,
  } = useSelector((state) => state.requestModels, shallowEqual);
  const data = activeMenu.data;

  const paramsData = {
    search: data?.search,
    sort: data?.sort,
    column: data?.column,
    perPage: data?.perPage,
    page: data?.page,
    parent_id: parentId,
    type: 'product',
  };

  const goToEdit = (row) => {
    dispatch(
      addMenu({
        url: `product-request/${row.id}`,
        id: 'product_request_edit',
        name: t('product.request.edit'),
      }),
    );
    navigate(`/product-request/${row.id}`);
  };

  const goToShow = (row) => {
    dispatch(
      addMenu({
        url: `product-request-details/${row.id}`,
        id: 'product_request_details',
        name: t('product.request.details'),
      }),
    );
    navigate(`/product-request-details/${row.id}`);
  };

  const initialColumns = [
    {
      title: t('created.by'),
      dataIndex: 'createdBy',
      key: 'createdBy',
      is_show: true,
      render: (createdBy) => (
        <span>
          {createdBy.firstname} {createdBy?.lastname}{' '}
          <a href={`tel:${createdBy?.phone}`}>{createdBy?.phone}</a>
        </span>
      ),
    },
    {
      title: t('created.at'),
      dataIndex: 'created_at',
      key: 'created_at',
      is_show: true,
      render: (createdAt) => moment(createdAt).format('YYYY-MM-DD HH:mm'),
    },
    {
      title: t('name'),
      dataIndex: 'name',
      key: 'name',
      is_show: true,
      render: (_, row) => (
        <Space>
          {row.model.translation?.title} <HiArrowNarrowRight />{' '}
          {row.data[`title[${row.model.translation.locale}]`]}
        </Space>
      ),
    },
    {
      title: t('image'),
      dataIndex: 'img',
      key: 'img',
      is_show: true,
      render: (_, row) => {
        return (
          <Space>
            <ColumnImage size={50} image={row?.model?.img} />
            <HiArrowNarrowRight />
            <ColumnImage
              size={50}
              image={row.data.images?.at(0).url || row.data.images?.at(0)}
            />
          </Space>
        );
      },
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
          onClick={() => setProductDetails(row)}
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
      key: 'actions',
      dataIndex: 'actions',
      is_show: true,
      render: (_, row) => {
        return (
          <div className={tableRowClasses.options}>
            {row?.status === 'canceled' && row?.status_note && (
              <button
                type='button'
                className={`${tableRowClasses.option} ${tableRowClasses.details}`}
                onClick={(e) => {
                  e.stopPropagation();
                  setIsVisibleMsgModal(true);
                  setModalText(row.status_note);
                }}
              >
                <MessageOutlined />
              </button>
            )}
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.details}`}
              onClick={(e) => {
                e.stopPropagation();
                goToShow(row);
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
          </div>
        );
      },
    },
  ];
  const [columns, setColumns] = useState(initialColumns);

  useEffect(() => {
    dispatch(fetchRequestModels(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    dispatch(fetchRequestModels(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu.data]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

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

  const requestStatusChange = () => {
    setLoadingBtn(true);
    const params = {
      status: id?.at(0).status,
    };
    requestAdminModelsService
      .changeStatus(id?.at(0).id, params)
      .then(() => {
        toast.success(t('successfully.changed'));
        dispatch(fetchRequestModels(body));
        setIsModalVisible(false);
        setId(null);
      })
      .finally(() => setLoadingBtn(false));
  };

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
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
          {t('product.requests')}
        </Typography.Title>
        <FilterColumns columns={columns} setColumns={setColumns} />
      </Space>
      <Divider color='var(--divider)' />
      <Table
        scroll={{ x: true }}
        rowSelection={rowSelection}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={requests}
        pagination={{
          pageSize: params.perPage,
          page: activeMenu.data?.page || 1,
          total: meta.total,
          defaultCurrent: activeMenu.data?.page,
          current: activeMenu.data?.page,
        }}
        rowKey={(record) => record.key}
        onChange={onChangePagination}
        loading={loading}
      />
      {productDetails && (
        <ProductStatusModal
          orderDetails={productDetails}
          handleCancel={() => setProductDetails(null)}
          paramsData={paramsData}
          listType='request'
        />
      )}
      <CustomModal
        click={requestStatusChange}
        text={t('change.status')}
        setText={setId}
        loading={loadingBtn}
      />
      <Modal
        title='Reject message'
        closable={false}
        visible={isVisibleMsgModal}
        footer={null}
        centered
      >
        <p>{modalText}</p>
        <div className='d-flex justify-content-end'>
          <Button
            type='primary'
            className='mr-2'
            onClick={() => setIsVisibleMsgModal(false)}
          >
            {t('close')}
          </Button>
        </div>
      </Modal>
    </Card>
  );
}
