import { useTranslation } from 'react-i18next';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import React, { useContext, useEffect, useState } from 'react';
import { Context } from 'context/context';
import { useNavigate } from 'react-router-dom';
import { Button, Card, Col, Divider, Space, Table, Typography } from 'antd';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import { toast } from 'react-toastify';
import useDidUpdate from 'helpers/useDidUpdate';
import CustomModal from 'components/modal';
import sellerServiceExtraService from 'services/seller/service-extra';
import { fetchSellerServiceExtra } from 'redux/slices/service-extra';
import numberToPrice from 'helpers/numberToPrice';
import OutlinedButton from 'components/outlined-button';
import FilterColumns from 'components/filter-column';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import SearchInput from 'components/search-input';

function SellerServiceExtra() {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { setIsModalVisible } = useContext(Context);

  const { serviceExtra, meta, loading, params } = useSelector(
    (state) => state.serviceExtra,
    shallowEqual,
  );
  const { defaultCurrency } = useSelector(
    (state) => state.currency,
    shallowEqual,
  );
  const navigate = useNavigate();

  const [id, setId] = useState(null);
  const [text, setText] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

  const [columns, setColumns] = useState([
    { title: t('id'), dataIndex: 'id', is_show: true, key: 'id' },
    {
      title: t('title'),
      dataIndex: 'title',
      is_show: true,
      key: 'title',
    },
    {
      title: t('service'),
      dataIndex: 'service',
      is_show: true,
      key: 'service',
    },
    {
      title: t('price'),
      dataIndex: 'price',
      is_show: true,
      key: 'price',
      render: (price) =>
        numberToPrice(
          price || 0,
          defaultCurrency?.symbol,
          defaultCurrency?.position,
        ),
    },
    {
      title: t('actions'),
      key: 'actions',
      is_show: true,
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            onClick={(e) => {
              e.stopPropagation();
              goToEdit(row?.id);
            }}
          >
            <EditOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            onClick={() => {
              setId([row?.id]);
              setIsModalVisible(true);
              setText('delete');
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ]);

  const clearData = () => {
    dispatch(setMenuData({ activeMenu, data: null }));
  };

  const goToAdd = () => {
    dispatch(
      addMenu({
        id: 'seller-service-extra-add',
        url: 'seller/service-extra/add',
        name: t('add.service.extra'),
      }),
    );
    clearData();
    navigate('/seller/service-extra/add');
  };

  const goToEdit = (id) => {
    const url = `seller/service-extra/${id}`;
    dispatch(
      addMenu({
        id: 'seller-service-extra-edit',
        url,
        name: t('edit.service.extra'),
      }),
    );
    navigate(`/${url}`);
  };

  const paramsData = {
    ...params,
    search: activeMenu.data?.search,
  };

  const handleDelete = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };

    sellerServiceExtraService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setId(null);
        setIsModalVisible(false);
        setText('');
        dispatch(fetchSellerServiceExtra(paramsData));
      })
      .finally(() => setLoadingBtn(false));
  };

  const deleteSelected = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.the.service.extra'));
    } else {
      setIsModalVisible(true);
      setText('all.delete');
    }
  };

  const onChangePagination = (pagination) => {
    const { pageSize: perPage, current: page } = pagination;
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...activeMenu.data, perPage, page },
      }),
    );
  };

  const handleSearch = (search) => {
    dispatch(setMenuData({ activeMenu, data: { ...activeMenu.data, search } }));
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchSellerServiceExtra(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    dispatch(fetchSellerServiceExtra(paramsData));
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.data]);

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
            {t('service.extras')}
          </Typography.Title>
          <Button
            type='primary'
            icon={<PlusOutlined />}
            onClick={goToAdd}
            style={{ width: '100%' }}
          >
            {t('add.service.extra')}
          </Button>
        </Space>
        <Divider color='var(--divider)' />
        <Space wrap className='mb-3'>
          <Col style={{ minWidth: '228px' }}>
            <SearchInput
              defaultValue={activeMenu.data?.search}
              resetSearch={!activeMenu.data?.search}
              placeholder={t('search')}
              handleChange={handleSearch}
            />
          </Col>
        </Space>
        <Space
          className='w-100 justify-content-end align-items-center'
          style={{ rowGap: '6px', columnGap: '6px', marginBottom: '20px' }}
        >
          <OutlinedButton onClick={deleteSelected} color='red'>
            {t('delete.selection')}
          </OutlinedButton>
          <FilterColumns columns={columns} setColumns={setColumns} />
        </Space>
        <Table
          scroll={{ x: true }}
          rowSelection={rowSelection}
          columns={columns?.filter((item) => item.is_show)}
          rowKey={(record) => record?.id}
          dataSource={serviceExtra}
          loading={loading}
          pagination={{
            pageSize: params.perPage,
            page: activeMenu.data?.page || 1,
            total: meta.total || 0,
            defaultCurrent: activeMenu.data?.page,
            current: activeMenu.data?.page,
          }}
          onChange={onChangePagination}
        />
      </Card>
      <CustomModal
        click={handleDelete}
        text={text}
        setText={setId}
        loading={loadingBtn}
      />
    </>
  );
}

export default SellerServiceExtra;
