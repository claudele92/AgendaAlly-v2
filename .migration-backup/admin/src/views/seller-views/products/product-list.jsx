import React, { useContext, useEffect, useState } from 'react';
import {
  CopyOutlined,
  DeleteOutlined,
  EditOutlined,
  MessageOutlined,
  PlusOutlined,
} from '@ant-design/icons';
import {
  Button,
  Col,
  Divider,
  Modal,
  Space,
  Switch,
  Table,
  Tabs,
  Typography,
} from 'antd';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { export_url } from 'configs/app-global';
import { Context } from 'context/context';
import CustomModal from 'components/modal';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import productService from 'services/seller/product';
import { fetchSellerProducts } from 'redux/slices/product';
import { useTranslation } from 'react-i18next';
import formatSortType from 'helpers/formatSortType';
import useDidUpdate from 'helpers/useDidUpdate';
import SearchInput from 'components/search-input';
import { DebounceSelect } from 'components/search';
import brandService from 'services/rest/brand';
import categoryService from 'services/rest/category';
import FilterColumns from 'components/filter-column';
import { CgExport, CgImport } from 'react-icons/cg';
import RiveResult from 'components/rive-result';
import ColumnImage from 'components/column-image';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import OutlinedButton from 'components/outlined-button';
import Card from 'components/card';

const { TabPane } = Tabs;
const roles = ['all', 'published', 'pending', 'unpublished'];

const ProductList = () => {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const [active, setActive] = useState(null);
  const [role, setRole] = useState('all');
  const [id, setId] = useState(null);
  const [isVisibleMsgModal, setIsVisibleMsgModal] = useState(false);
  const [modalText, setModalText] = useState('');
  const clearData = () => {
    dispatch(
      setMenuData({
        activeMenu,
        data: null,
      }),
    );
  };

  const goToEdit = (row) => {
    dispatch(
      addMenu({
        id: 'product-edit',
        url: `seller/product/${row.uuid}`,
        name: t('edit.product'),
      }),
    );
    clearData();
    navigate(`/seller/product/${row.uuid}`);
  };

  const goToClone = (row) => {
    dispatch(
      addMenu({
        id: `product-clone`,
        url: `seller/product-clone/${row.uuid}`,
        name: t('clone.product'),
      }),
    );
    clearData();
    navigate(`/seller/product-clone/${row.uuid}`);
  };

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      is_show: true,
      sorter: (a, b) => a.id - b.id,
    },
    {
      title: t('image'),
      dataIndex: 'img',
      key: 'img',
      is_show: true,
      render: (img, row) => <ColumnImage size={50} image={img} id={row?.id} />,
    },
    {
      title: t('name'),
      dataIndex: 'name',
      is_show: true,
    },
    {
      title: t('translations'),
      dataIndex: 'translations',
      key: 'translations',
      is_show: true,
      render: (translations) => (
        <div className={tableRowClasses.locales}>
          {translations?.map((item, index) => (
            <div
              key={item?.id}
              className={`${tableRowClasses.locale} ${1 & index ? tableRowClasses.odd : tableRowClasses.even}`}
            >
              {item?.locale}
            </div>
          ))}
        </div>
      ),
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
    {
      title: t('shop'),
      dataIndex: 'shop_id',
      is_show: true,
      render: (_, row) => row?.shop?.translation?.title,
    },
    {
      title: t('category'),
      dataIndex: 'category_name',
      is_show: true,
    },
    {
      title: t('active'),
      dataIndex: 'active',
      is_show: true,
      render: (active, row) => (
        <Switch
          onChange={() => {
            setIsModalVisible(true);
            setId(row.uuid);
            setActive(true);
          }}
          checked={active}
        />
      ),
    },
    {
      title: t('actions'),
      dataIndex: 'actions',
      is_show: true,
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          {row?.status === 'unpublished' && row?.status_note && (
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
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            onClick={() => {
              setIsModalVisible(true);
              setId([row?.id]);
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

  const { setIsModalVisible } = useContext(Context);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [downloading, setDownloading] = useState(false);
  const [text, setText] = useState(null);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { products, meta, loading, params } = useSelector(
    (state) => state.product,
    shallowEqual,
  );
  const { myShop } = useSelector((state) => state.myShop, shallowEqual);
  const immutable = activeMenu.data?.role || role;
  const data = activeMenu.data;
  const paramsData = {
    search: data?.search,
    brand_id: data?.brand?.value,
    category_id: data?.category?.value,
    status: immutable === 'all' ? undefined : immutable,
    sort: data?.sort,
    column: data?.column,
    perPage: data?.perPage,
    page: data?.page,
  };

  const productDelete = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };
    productService
      .delete(params)
      .then(() => {
        setIsModalVisible(false);
        toast.success(t('successfully.deleted'));
        dispatch(fetchSellerProducts(params));
      })
      .finally(() => setLoadingBtn(false));
  };

  const handleActive = () => {
    setLoadingBtn(true);
    productService
      .setActive(id)
      .then(() => {
        setIsModalVisible(false);
        dispatch(fetchSellerProducts(paramsData));
        toast.success(t('successfully.updated'));
        setActive(true);
      })
      .finally(() => setLoadingBtn(false));
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

  useDidUpdate(() => {
    dispatch(fetchSellerProducts(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu.data]);

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchSellerProducts(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const goToAddProduct = () => {
    dispatch(
      addMenu({
        id: 'product-add',
        url: 'seller/product/add',
        name: t('add.product'),
      }),
    );
    clearData();
    navigate('/seller/product/add');
  };

  const goToImport = () => {
    dispatch(
      addMenu({
        id: 'seller-product-import',
        url: `seller/product/import`,
        name: t('product.import'),
      }),
    );
    navigate(`/seller/product/import`);
  };

  async function fetchBrands(search) {
    const params = {
      shop_id: myShop?.id,
      search,
    };
    return brandService.getAll(params).then(({ data }) =>
      data.map((item) => ({
        label: item.title,
        value: item.id,
      })),
    );
  }

  async function fetchCategories(search) {
    const params = {
      search: search || undefined,
      type: 'main',
      perPage: 5,
    };
    return categoryService.search(params).then(({ data }) =>
      data.map((item) => ({
        label: item?.translation?.title,
        value: item?.id,
        key: item?.id,
      })),
    );
  }

  const handleFilter = (items) => {
    const data = activeMenu.data;
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...data, ...items },
      }),
    );
  };

  const excelExport = () => {
    setDownloading(true);
    const body = {
      shop_id: myShop?.id,
      category_id: activeMenu?.data?.category?.value,
      brand_id: activeMenu?.data?.brand?.value,
    };
    productService
      .export(body)
      .then((res) => {
        if (res?.data?.file_name) {
          window.location.href = export_url + res.data.file_name;
        }
      })
      .finally(() => setDownloading(false));
  };

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
  return (
    <React.Fragment>
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
            {t('products')}
          </Typography.Title>
          <Button
            type='primary'
            icon={<PlusOutlined />}
            onClick={goToAddProduct}
            style={{ width: '100%' }}
          >
            {t('add.product')}
          </Button>
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
          <Col style={{ minWidth: '228px' }}>
            <SearchInput
              placeholder={t('search')}
              handleChange={(e) => handleFilter({ search: e })}
              defaultValue={activeMenu.data?.search}
              resetSearch={!activeMenu.data?.search}
              className='w-100'
            />
          </Col>
          <Col style={{ minWidth: '170px' }}>
            <DebounceSelect
              placeholder={t('select.category')}
              fetchOptions={fetchCategories}
              className='w-100'
              onChange={(e) => handleFilter({ category: e })}
              value={activeMenu.data?.category}
            />
          </Col>
          <Col style={{ minWidth: '170px' }}>
            <DebounceSelect
              placeholder={t('select.brand')}
              fetchOptions={fetchBrands}
              className='w-100'
              onChange={(e) => handleFilter({ brand: e })}
              value={activeMenu.data?.brand}
            />
          </Col>
          <OutlinedButton
            loading={downloading}
            onClick={excelExport}
            key='export'
          >
            <CgExport />
            {t('export')}
          </OutlinedButton>
          <OutlinedButton color='green' onClick={goToImport} key='import'>
            <CgImport />
            {t('import')}
          </OutlinedButton>
        </div>
        <Divider color='var(--divider)' />
        <Space className='w-100 justify-content-between align-items-start'>
          <Tabs
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
        <Table
          locale={{
            emptyText: <RiveResult />,
          }}
          scroll={{ x: true }}
          rowSelection={rowSelection}
          loading={loading}
          columns={columns?.filter((item) => item.is_show)}
          dataSource={products}
          pagination={{
            pageSize: params.perPage,
            page: activeMenu.data?.page || 1,
            total: meta.total,
            defaultCurrent: activeMenu.data?.page,
            current: activeMenu.data?.page,
          }}
          onChange={onChangePagination}
          rowKey={(record) => record.id}
        />
      </Card>
      <CustomModal
        click={active ? handleActive : productDelete}
        text={
          active
            ? t('set.active.product')
            : text
              ? t('delete')
              : t('all.delete')
        }
        loading={loadingBtn}
        setText={setId}
        setActive={setActive}
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
            Close
          </Button>
        </div>
      </Modal>
    </React.Fragment>
  );
};

export default ProductList;
