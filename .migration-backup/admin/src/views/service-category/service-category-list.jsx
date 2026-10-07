import React, { useContext, useEffect, useState } from 'react';
import {
  CopyOutlined,
  DeleteOutlined,
  EditOutlined,
  EyeOutlined,
  PlusOutlined,
} from '@ant-design/icons';
import {
  Button,
  Col,
  Divider,
  Space,
  Switch,
  Table,
  Tabs,
  Typography,
} from 'antd';
import { export_url } from 'configs/app-global';
import { Context } from 'context/context';
import { useLocation, useNavigate, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import CustomModal from 'components/modal';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import categoryService from 'services/category';
import { fetchCategories } from 'redux/slices/category';
import { useTranslation } from 'react-i18next';
import FilterColumns from 'components/filter-column';
import SearchInput from 'components/search-input';
import useDidUpdate from 'helpers/useDidUpdate';
import { CgExport, CgImport } from 'react-icons/cg';
import formatSortType from 'helpers/formatSortType';
import { fetchServiceCategories } from 'redux/slices/serviceCategories';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';
import ServiceCategoryStatusModal from './serviceCategoryServiceModal';
import ColumnImage from 'components/column-image';
import OutlinedButton from 'components/outlined-button';

const { TabPane } = Tabs;
const roles = ['all', 'pending', 'published', 'unpublished'];

const ServiceCategoryList = ({
  parentId,
  type = 'service',
  parent_type,
  isRefetch = false,
  handleAddAction = () => {},
}) => {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { state } = useLocation();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [role, setRole] = useState('all');
  const immutable = activeMenu.data?.role || role;
  const { uuid: parentUuid } = useParams();
  const [active, setActive] = useState(null);
  const [serviceCategoryDetails, setServiceCategoryDetails] = useState(null);

  function goToEdit(row) {
    dispatch(
      addMenu({
        url: `service-category/${row.uuid}`,
        id: parentId ? 'service_category_sub_edit' : 'service_category_edit',
        name: parentId
          ? t('edit.sub.service.category')
          : t('edit.service.category'),
      }),
    );
    navigate(`/service-category/${row.uuid}`, {
      state: { parentId: parentUuid },
    });
  }

  function goToShow(row) {
    dispatch(
      addMenu({
        url: `service-category/show/${row.uuid}`,
        id: 'service_category_show',
        name: t('service.category.show'),
      }),
    );
    navigate(`/service-category/show/${row.uuid}`, {
      state: { parentId, parentUuid },
    });
  }

  const goToAddCategory = () => {
    if (parentId) {
      handleAddAction(parentId);
    } else {
      dispatch(
        addMenu({
          id: parentId ? 'sub-service-category' : 'service-category-add',
          url: 'service-category/add',
          name: parentId
            ? t('sub.service.category')
            : t('add.service.category'),
        }),
      );
      navigate('/service-category/add', { state: { parentId, parentUuid } });
    }
  };

  const goToImport = () => {
    if (parentId) {
      handleAddAction(parentId);
    } else {
      dispatch(
        addMenu({
          url: `catalog/service-category/import`,
          id: parentId
            ? 'sub_service_category_import'
            : 'service_category_import',
          name: parentId
            ? t('import.sub.service.category')
            : t('import.service.category'),
        }),
      );
      navigate(`/catalog/service-category/import`, {
        state: { parentId, parentUuid },
      });
    }
  };

  const goToClone = (uuid) => {
    dispatch(
      addMenu({
        id: parentId ? 'sub-category-clone' : `service-category-clone`,
        url: `service-category-clone/${uuid}`,
        name: parentId
          ? t('sub.service.category')
          : t('service.category.clone'),
      }),
    );
    navigate(`/service-category-clone/${uuid}`, {
      state: { parentId, parentUuid },
    });
  };

  const initialColumns = [
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
      key: 'locales',
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
      render: (img, row) => <ColumnImage size={50} image={img} id={row?.id} />,
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
      title: t('status'),
      is_show: true,
      dataIndex: 'status',
      key: 'status',
      render: (status, row) => (
        <button
          type='button'
          className={tableRowClasses.status}
          onClick={() => {
            setServiceCategoryDetails(row);
          }}
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
      render: (_, row) => (
        <div className={tableRowClasses.options}>
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
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.clone}`}
            onClick={(e) => {
              e.stopPropagation();
              goToClone(row?.uuid);
            }}
          >
            <CopyOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            onClick={() => {
              setId([row?.id]);
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
  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [downloading, setDownloading] = useState(false);
  const [text, setText] = useState(null);

  const { serviceCategories, meta, loading } = useSelector(
    (state) => state.serviceCategories,
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
      type: 'service',
    };
    categoryService
      .delete(params)
      .then(() => {
        dispatch(fetchServiceCategories(paramsData));
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
    if (
      paramsData.type === 'child_service' &&
      paramsData.parent_id === state.parentId
    ) {
      return; // stop fetching data if the parent_id is the same as the previous one
    }
    dispatch(fetchServiceCategories(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu.refetch, type, parentId]);

  useEffect(() => {
    if (isRefetch) {
      dispatch(fetchServiceCategories(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
  }, [isRefetch]);

  useDidUpdate(() => {
    dispatch(fetchServiceCategories(paramsData));
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

  const excelExport = () => {
    setDownloading(true);
    categoryService
      .export(paramsData)
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
      toast.warning(t('select.service.category'));
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

  const handleActive = () => {
    setLoadingBtn(true);
    categoryService
      .setActive(id)
      .then(() => {
        setIsModalVisible(false);
        dispatch(fetchCategories(paramsData));
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
            {t('service.categories')}
          </Typography.Title>
          {parent_type !== 'sub_service' && (
            <Button
              type='primary'
              icon={<PlusOutlined />}
              onClick={goToAddCategory}
              style={{ width: '100%' }}
            >
              {t('add.service.category')}
            </Button>
          )}
        </Space>
        <Divider color='var(--divider)' />
        {!parentId && (
          <>
            <Space
              className='w-100 justify-content-end align-items-center'
              style={{ rowGap: '6px', columnGap: '6px' }}
            >
              <Col style={{ minWidth: '228px' }}>
                <SearchInput
                  placeholder={t('search')}
                  className='w-100'
                  handleChange={(e) => {
                    handleFilter({ search: e });
                  }}
                  defaultValue={activeMenu.data?.search}
                  resetSearch={!activeMenu.data?.search}
                  style={{ minWidth: 300 }}
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
            </Space>
            <Divider color='var(--divider)' />
          </>
        )}
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
          scroll={{ x: true }}
          rowSelection={rowSelection}
          columns={columns?.filter((item) => item.is_show)}
          dataSource={serviceCategories}
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

      {serviceCategoryDetails && (
        <ServiceCategoryStatusModal
          categoryDetails={serviceCategoryDetails}
          handleCancel={() => setServiceCategoryDetails(null)}
          paramsData={paramsData}
        />
      )}
    </>
  );
};

export default ServiceCategoryList;
