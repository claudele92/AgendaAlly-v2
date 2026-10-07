import React, { useContext, useEffect, useState } from 'react';
import {
  Button,
  Card,
  Divider,
  Space,
  Switch,
  Table,
  Tabs,
  Typography,
} from 'antd';
import { useTranslation } from 'react-i18next';
import {
  ClearOutlined,
  CopyOutlined,
  DeleteOutlined,
  EditOutlined,
  EyeOutlined,
  PlusOutlined,
} from '@ant-design/icons';
import { fetchSellerCategory } from '../../../redux/slices/category';
import {
  addMenu,
  disableRefetch,
  setMenuData,
} from '../../../redux/slices/menu';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { toast } from 'react-toastify';
import CustomModal from '../../../components/modal';
import { Context } from '../../../context/context';
import sellerCategory from '../../../services/seller/category';
import { useNavigate, useParams } from 'react-router-dom';
import FilterColumns from '../../../components/filter-column';
import formatSortType from '../../../helpers/formatSortType';
import useDidUpdate from '../../../helpers/useDidUpdate';
import SearchInput from '../../../components/search-input';
import { CgExport, CgImport } from 'react-icons/cg';
import { export_url } from 'configs/app-global';
import ColumnImage from 'components/column-image';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import OutlinedButton from 'components/outlined-button';

const { TabPane } = Tabs;

const roles = ['all', 'pending', 'published', 'unpublished'];

const CategoryList = ({
  parentId,
  type = 'main',
  parent_type,
  isRefetch = false,
  handleAddAction = () => {},
}) => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [role, setRole] = useState('all');
  const immutable = activeMenu.data?.role || role;
  const { uuid: parentUuid } = useParams();
  const [active, setActive] = useState(null);
  // const [categoryDetails, setCategoryDetails] = useState(null);

  function goToEdit(uuid) {
    dispatch(
      addMenu({
        url: `seller/category/${uuid}`,
        id: parentId ? 'category_sub_edit' : 'category_edit',
        name: parentId ? t('edit.sub.category') : t('edit.category'),
      }),
    );
    navigate(`/seller/category/${uuid}`, {
      state: { parentId, parentUuid },
    });
  }
  function goToShow(uuid) {
    dispatch(
      addMenu({
        url: `seller/category/show/${uuid}`,
        id: 'category_show',
        name: t('category.show'),
      }),
    );
    navigate(`/seller/category/show/${uuid}`, {
      state: { parentId, parentUuid },
    });
  }

  const goToAddCategory = () => {
    if (parentId) {
      handleAddAction(parentId);
    } else {
      dispatch(
        addMenu({
          id: parentId ? 'sub-category-add' : 'category-add',
          url: 'seller/category/add',
          name: parentId ? t('add.sub.category') : t('add.category'),
        }),
      );
      navigate('/seller/category/add', { state: { parentId, parentUuid } });
    }
  };

  const goToImport = () => {
    dispatch(
      addMenu({
        url: `seller/catalog/categories/import`,
        id: parentId ? 'sub_category_import' : 'category_import',
        name: parentId ? t('import.sub.category') : t('import.category'),
      }),
    );
    navigate(`/seller/catalog/categories/import`, {
      state: { parentId, parentUuid },
    });
  };

  const goToClone = (uuid) => {
    dispatch(
      addMenu({
        id: parentId ? 'sub-category-clone' : `category-clone`,
        url: `seller/category-clone/${uuid}`,
        name: parentId ? t('sub.category.clone') : t('category.clone'),
      }),
    );
    navigate(`/seller/category-clone/${uuid}`, {
      state: { parentId, parentUuid },
    });
  };

  const [columns, setColumns] = useState([
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      is_show: true,
    },
    {
      title: t('name'),
      dataIndex: 'name',
      key: 'name',
      is_show: true,
    },
    {
      title: t('translations'),
      dataIndex: 'locales',
      key: 'translations',
      is_show: true,
      render: (locales) => (
        <div className={tableRowClasses.locales}>
          {locales?.map((item, index) => (
            <div
              key={item}
              className={`${tableRowClasses.locale} ${1 & index ? tableRowClasses.odd : tableRowClasses.even}`}
            >
              {item}
            </div>
          ))}
        </div>
      ),
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
      is_show: true,
      render: (active, row) => {
        return (
          <Switch
            onChange={() => {
              setIsModalVisible(true);
              setId(row.uuid);
              setActive(true);
            }}
            checked={active}
          />
        );
      },
    },
    {
      title: t('status'),
      is_show: true,
      dataIndex: 'status',
      key: 'status',
      render: (status) => {
        // const isCanEdit = !row?.deleted_at;
        return (
          <button
            type='button'
            className={tableRowClasses.status}
            disabled
            // onClick={(e) => {
            //   e.stopPropagation();
            //   if (isCanEdit) {
            //     setCategoryDetails(row);
            //   }
            // }}
          >
            <span className={tableRowClasses[status || 'pending']}>
              {t(status)}
            </span>
            {/*{isCanEdit && <EditOutlined />}*/}
          </button>
        );
      },
    },
    {
      title: t('actions'),
      key: 'actions',
      dataIndex: 'uuid',
      is_show: true,
      render: (uuid, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.details}`}
            disabled={row?.deleted_at}
            onClick={(e) => {
              e.stopPropagation();
              goToShow(uuid);
            }}
          >
            <EyeOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            disabled={!!row?.deleted_at}
            onClick={(e) => {
              e.stopPropagation();
              goToEdit(uuid);
            }}
          >
            <EditOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.clone}`}
            disabled={!!row?.deleted_at}
            onClick={(e) => {
              e.stopPropagation();
              goToClone(uuid);
            }}
          >
            <CopyOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            disabled={!!row?.deleted_at}
            onClick={(e) => {
              e.stopPropagation();
              setId([row.id]);
              setIsModalVisible(true);
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ]);

  const { setIsModalVisible } = useContext(Context);
  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [downloading, setDownloading] = useState(false);
  const [text, setText] = useState(null);

  const { categories, meta, loading } = useSelector(
    (state) => state.category,
    shallowEqual,
  );

  const data = activeMenu.data;
  const paramsData = {
    search: data?.search,
    perPage: activeMenu?.data?.perPage || 10,
    page: data?.page || 1,
    status: immutable === 'all' ? undefined : immutable,
    type: type,
    parent_id: parentId,
  };

  const categoryDelete = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };
    sellerCategory
      .delete(params)
      .then(() => {
        dispatch(fetchSellerCategory(paramsData));
        toast.success(t('successfully.deleted'));
      })
      .finally(() => {
        setIsModalVisible(false);
        setLoadingBtn(false);
        setText(null);
        setId(null);
      });
  };

  useEffect(() => {
    dispatch(fetchSellerCategory(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu.refetch, type, parentId]);

  useEffect(() => {
    if (isRefetch) dispatch(fetchSellerCategory(paramsData));
  }, [isRefetch]);

  useDidUpdate(() => {
    dispatch(fetchSellerCategory(paramsData));
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

  const excelExport = () => {
    setDownloading(true);
    sellerCategory
      .export(paramsData)
      .then((res) => {
        const body = export_url + res.data.file_name;
        window.location.href = body;
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

  const handleFilter = (items) => {
    const data = activeMenu.data;
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...data, ...items },
      }),
    );
  };

  const handleClear = () => {
    dispatch(
      setMenuData({
        activeMenu,
        data: undefined,
      }),
    );
  };

  const handleActive = () => {
    setLoadingBtn(true);
    sellerCategory
      .setActive(id)
      .then(() => {
        setIsModalVisible(false);
        dispatch(fetchSellerCategory(paramsData));
        toast.success(t('successfully.updated'));
        setActive(false);
      })
      .finally(() => setLoadingBtn(false));
  };

  return (
    <>
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
            {parentId ? t('sub.category') : t('categories')}
          </Typography.Title>
          {parent_type !== 'child' && (
            <Button
              type='primary'
              icon={<PlusOutlined />}
              onClick={goToAddCategory}
              style={{ width: '100%' }}
            >
              {t('add.category')}
            </Button>
          )}
        </Space>
        <Divider color='var(--divider)' />
        {parentId ? (
          <Space
            wrap
            style={{ rowGap: '6px', columnGap: '6px' }}
            className='w-100 justify-content-end'
            size={[14, 20]}
          >
            <SearchInput
              placeholder={t('search')}
              className='w-25'
              handleChange={(e) => {
                handleFilter({ search: e });
              }}
              defaultValue={activeMenu.data?.search}
              resetSearch={!activeMenu.data?.search}
              style={{ minWidth: 300 }}
            />

            <OutlinedButton color='green' onClick={goToImport}>
              <CgImport className='mr-2' />
              {t('import')}
            </OutlinedButton>
            <OutlinedButton loading={downloading} onClick={excelExport}>
              <CgExport className='mr-2' />
              {t('export')}
            </OutlinedButton>
            <Button
              icon={<ClearOutlined />}
              onClick={handleClear}
              disabled={!activeMenu.data}
              style={{ minWidth: 100 }}
            />
            <FilterColumns columns={columns} setColumns={setColumns} />
          </Space>
        ) : (
          <Space
            wrapAdd
            commentMore
            actions
            style={{ rowGap: '6px', columnGap: '6px' }}
            className='w-100 justify-content-end'
            size={[14, 20]}
          >
            <SearchInput
              placeholder={t('search')}
              className='w-25'
              handleChange={(e) => {
                handleFilter({ search: e });
              }}
              defaultValue={activeMenu.data?.search}
              resetSearch={!activeMenu.data?.search}
              style={{ minWidth: 300 }}
            />
            <OutlinedButton color='green' onClick={goToImport}>
              <CgImport className='mr-2' />
              {t('import')}
            </OutlinedButton>
            <OutlinedButton loading={downloading} onClick={excelExport}>
              <CgExport className='mr-2' />
              {t('export')}
            </OutlinedButton>
            <Button
              onClick={handleClear}
              disabled={!activeMenu.data}
              style={{ minWidth: 100 }}
            >
              {t('clear')}
            </Button>
          </Space>
        )}
        <Divider color='var(--divider)' />
        <Space className='w-100 justify-content-between align-items-start'>
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
          <Space className='gap-10'>
            <OutlinedButton color='red' onClick={allDelete}>
              {t('delete.selected')}
            </OutlinedButton>
            <FilterColumns columns={columns} setColumns={setColumns} />
          </Space>
        </Space>
        <Table
          scroll={{ x: true }}
          rowSelection={rowSelection}
          columns={columns?.filter((item) => item.is_show)}
          dataSource={categories}
          pagination={{
            pageSize: activeMenu.data?.perPage || 10,
            page: data?.page || 1,
            total: meta.total,
            defaultCurrent: data?.page,
            current: activeMenu.data?.page,
          }}
          rowKey={(record) => record.id}
          onChange={onChangePagination}
          loading={loading}
        />
      </Card>

      <CustomModal
        click={active ? handleActive : categoryDelete}
        text={
          active
            ? t('set.active.category')
            : text
              ? t('delete')
              : t('all.delete')
        }
        setText={setId}
        loading={loadingBtn}
      />

      {/* {categoryDetails && (
        <CategoryStatusModal
          categoryDetails={categoryDetails}
          handleCancel={() => setCategoryDetails(null)}
          paramsData={paramsData}
        />
      )} */}
    </>
  );
};

export default CategoryList;
