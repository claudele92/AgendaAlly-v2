import { useNavigate } from 'react-router-dom';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import React, { useContext, useEffect, useState } from 'react';
import { Button, Divider, Space, Table, Typography } from 'antd';
import {
  DeleteOutlined,
  EditOutlined,
  PlusCircleOutlined,
  PlusOutlined,
} from '@ant-design/icons';
import { toast } from 'react-toastify';
import { Context } from 'context/context';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import CustomModal from 'components/modal';
import DeleteButton from 'components/delete-button';
import { fetchSellerFormOptions } from 'redux/slices/form-options';
import sellerFormOptionService from 'services/seller/form-option';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import useDidUpdate from 'helpers/useDidUpdate';
import Card from 'components/card';
import OutlinedButton from 'components/outlined-button';
import FilterColumns from 'components/filter-column';

function FormOptions() {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { t, i18n } = useTranslation();
  const { setIsModalVisible } = useContext(Context);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { formOptions, loading, meta, params } = useSelector(
    (state) => state.formOptions,
    shallowEqual,
  );
  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      is_show: true,
    },
    {
      title: t('title'),
      dataIndex: 'title',
      key: 'title',
      is_show: true,
    },
    {
      title: t('actions'),
      dataIndex: 'actions',
      is_show: true,
      render: (_, record) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            onClick={(e) => {
              e.stopPropagation();
              goToEdit(record?.id);
            }}
          >
            <EditOutlined />
          </button>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            onClick={() => {
              setIsModalVisible(true);
              setId([record?.id]);
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ];
  const [columns, setColumns] = useState(initialColumns);

  const goToCreate = () => {
    dispatch(
      addMenu({
        id: 'seller/add-form-options',
        url: 'seller/form-options/add',
        name: 'add.form.option',
      }),
    );
    clearData();
    navigate('/seller/form-options/add');
  };

  const goToEdit = (id) => {
    dispatch(
      addMenu({
        id: 'seller/form_options-edit',
        url: `seller/form-options/${id}`,
        name: 'seller.edit.form.options',
      }),
    );
    clearData();
    navigate(`${id}`);
  };

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;
    const paramsData = {
      ...params,
      perPage: pageSize,
      page: current,
    };
    dispatch(fetchSellerFormOptions(paramsData));
  };

  const handleDeleteFormOptions = () => {
    setLoadingBtn(true);

    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };

    sellerFormOptionService
      .delete(params)
      .then(() => {
        dispatch(fetchSellerFormOptions({ params }));
        toast.success(t('successfully.deleted'));
        setIsModalVisible(false);
        setId(null);
      })
      .finally(() => setLoadingBtn(false));
  };

  const deleteSelected = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.form.option'));
    } else {
      setIsModalVisible(true);
    }
  };

  const clearData = () => {
    dispatch(
      setMenuData({
        activeMenu,
        data: null,
      }),
    );
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchSellerFormOptions({}));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  return (
    <Card
      extra={
        <Space>
          <DeleteButton icon={<DeleteOutlined />} onClick={deleteSelected}>
            {t('delete.selected')}
          </DeleteButton>
          <Button
            type='primary'
            icon={<PlusCircleOutlined />}
            onClick={goToCreate}
          >
            {t('add.form.option')}
          </Button>
        </Space>
      }
    >
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
          {t('form.options')}
        </Typography.Title>
        <Button
          type='primary'
          icon={<PlusOutlined />}
          onClick={goToCreate}
          style={{ width: '100%' }}
        >
          {t('add.form.option')}
        </Button>
      </Space>
      <Divider color='var(--divider)' />
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
        rowKey={(record) => record.id}
        rowSelection={rowSelection}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={formOptions}
        loading={loading}
        pagination={{
          pageSize: meta?.per_page,
          page: meta?.current_page,
          current: meta?.current_page,
          total: meta?.total,
        }}
        onChange={onChangePagination}
      />
      <CustomModal
        click={handleDeleteFormOptions}
        text={t('are.you.sure.you.want.to.delete.the.selected.products')}
        loading={loadingBtn}
      />
    </Card>
  );
}

export default FormOptions;
