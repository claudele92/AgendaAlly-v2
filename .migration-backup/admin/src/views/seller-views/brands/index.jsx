import React, { useEffect, useState, useContext } from 'react';
import { Button, Col, Divider, Space, Table, Typography } from 'antd';
import brandService from 'services/seller/brands';
import { useTranslation } from 'react-i18next';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import FilterColumns from 'components/filter-column';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import CustomModal from 'components/modal';
import { Context } from 'context/context';
import CreateBrand from './createBrand';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import { sellerfetchBrands } from 'redux/slices/brand';
import formatSortType from 'helpers/formatSortType';
import useDidUpdate from 'helpers/useDidUpdate';
import SearchInput from 'components/search-input';
import Card from 'components/card';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import ColumnImage from 'components/column-image';

export default function Brands() {
  const { t, i18n } = useTranslation();
  const [text, setText] = useState(null);
  const [id, setId] = useState(null);
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { setIsModalVisible } = useContext(Context);
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { brands, meta, loading, params } = useSelector(
    (state) => state.brand,
    shallowEqual,
  );
  const data = activeMenu.data;

  const paramsData = {
    search: data?.search,
    sort: data?.sort,
    column: data?.column,
    perPage: data?.perPage,
    page: data?.page,
  };

  const goToEdit = (row) => {
    dispatch(
      addMenu({
        url: `seller/brand/${row.id}`,
        id: 'brand_edit',
        name: t('brands'),
      }),
    );
    navigate(`/seller/brand/${row.id}`);
  };

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      sorter: true,
      is_show: true,
    },
    {
      title: t('title'),
      dataIndex: 'title',
      key: 'title',
      is_show: true,
    },
    {
      title: t('image'),
      dataIndex: 'img',
      key: 'img',
      is_show: true,
      render: (img, row) => <ColumnImage image={img} id={row?.id} />,
    },
    {
      title: t('active'),
      dataIndex: 'active',
      key: 'active',
      is_show: true,
      render: (active) => (
        <span style={{ color: active ? 'var(--green)' : 'var(--red)' }}>
          {t(active ? 'active' : 'inactive')}
        </span>
      ),
    },
    {
      title: t('actions'),
      key: 'actions',
      dataIndex: 'id',
      is_show: true,
      render: (data, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            disabled={!row?.shop_id}
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
            disabled={!row?.shop_id}
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
      ),
    },
  ];
  const [columns, setColumns] = useState(initialColumns);

  const brandDelete = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };
    brandService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        dispatch(sellerfetchBrands({}));
        setIsModalVisible(false);
        setId(null);
      })
      .finally(() => setLoadingBtn(false));
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(sellerfetchBrands({}));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    dispatch(sellerfetchBrands(paramsData));
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

  const handleCancel = () => setIsModalOpen(false);

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

  const goToAdd = () => {
    dispatch(
      addMenu({
        url: `seller/brand/add`,
        id: 'seller/brand/add',
        name: t('add.brand'),
      }),
    );
    navigate(`/seller/brand/add`);
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
      <Space className='justify-content-between align-items-center w-100'>
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
          {t('brands')}
        </Typography.Title>
        <Button
          type='primary'
          icon={<PlusOutlined />}
          onClick={goToAdd}
          style={{ width: '100%' }}
        >
          {t('add.brand')}
        </Button>
      </Space>
      <Divider color='var(--divider)' />
      <Space
        className='w-100 justify-content-end align-items-center'
        style={{ rowGap: '6px', columnGap: '6px', marginBottom: '20px' }}
      >
        <Col style={{ minWidth: '228px' }}>
          <SearchInput
            placeholder={t('search')}
            defaultValue={activeMenu.data?.search}
            resetSearch={!activeMenu.data?.search}
            className='w-100'
            handleChange={(e) => handleFilter({ search: e })}
          />
        </Col>
        <FilterColumns columns={columns} setColumns={setColumns} />
      </Space>
      <Table
        scroll={{ x: true }}
        rowSelection={rowSelection}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={brands}
        pagination={{
          pageSize: params.perPage,
          page: activeMenu.data?.page || 1,
          total: meta.total,
          defaultCurrent: activeMenu.data?.page,
          current: activeMenu.data?.page,
        }}
        rowKey={(record) => record.id}
        onChange={onChangePagination}
        loading={loading}
      />
      {isModalOpen && (
        <CreateBrand handleCancel={handleCancel} isModalOpen={isModalOpen} />
      )}
      <CustomModal
        click={brandDelete}
        text={text ? t('delete') : t('all.delete')}
        setText={setId}
        loading={loadingBtn}
      />
    </Card>
  );
}
