import React, { useContext, useEffect, useState } from 'react';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import { Button, Table, Space, Typography, Divider, Col } from 'antd';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { Context } from 'context/context';
import CustomModal from 'components/modal';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import parcelOptionService from 'services/parcel-option';
import { fetchParcelOptions } from 'redux/slices/parcel-option';
import { useTranslation } from 'react-i18next';
import FilterColumns from 'components/filter-column';
import SearchInput from 'components/search-input';
import useDidUpdate from 'helpers/useDidUpdate';
import moment from 'moment';
import { getHourFormat } from 'helpers/getHourFormat';
import Card from 'components/card';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import OutlinedButton from 'components/outlined-button';

export default function Units() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const [uuid, setUUID] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const { setIsModalVisible } = useContext(Context);
  const [text, setText] = useState(null);
  const hourFormat = getHourFormat();

  const goToEdit = (row) => {
    dispatch(
      addMenu({
        id: 'option.edit',
        url: `options/${row.id}`,
        name: t('edit.option'),
      }),
    );
    navigate(`/options/${row.id}`);
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
      dataIndex: 'translation',
      is_show: true,
      render: (translation) => translation?.title,
    },
    {
      title: t('created.at'),
      dataIndex: 'created_at',
      is_show: true,
      render: (created_at) =>
        moment(created_at).format(`YYYY-MM-DD ${hourFormat}`),
    },
    {
      title: t('actions'),
      dataIndex: 'actions',
      is_show: true,
      render: (_, row) => (
        <div className={tableRowClasses.options}>
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
            className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
            disabled={row?.deleted_at}
            onClick={(e) => {
              e.stopPropagation();
              setUUID([row.id]);
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

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { options, meta, loading, params } = useSelector(
    (state) => state.parcelOptions,
    shallowEqual,
  );

  const unitDelete = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...uuid.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };
    parcelOptionService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setIsModalVisible(false);
        setText(null);
        dispatch(fetchParcelOptions({}));
      })
      .finally(() => setLoadingBtn(false));
  };

  function formatSortType(type) {
    switch (type) {
      case 'ascend':
        return 'asc';

      case 'descend':
        return 'desc';

      default:
        break;
    }
  }

  function onChange(pagination, sorter) {
    const { pageSize: perPage, current: page } = pagination;
    const { field: column, order } = sorter;
    const sort = formatSortType(order);
    dispatch(fetchParcelOptions({ ...params, perPage, page, column, sort }));
  }

  const goToAddUnit = () => {
    dispatch(
      addMenu({
        id: 'options_ad',
        url: 'options/add',
        name: t('add.option'),
      }),
    );
    navigate('/options/add');
  };

  const rowSelection = {
    selectedRowKeys: uuid,
    onChange: (key) => {
      setUUID(key);
    },
  };

  const allDelete = () => {
    if (uuid === null || uuid.length === 0) {
      toast.warning(t('select.the.option'));
    } else {
      setIsModalVisible(true);
      setText(false);
    }
  };

  const handleFilter = (item, name) => {
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...activeMenu.data, [name]: item },
      }),
    );
  };

  useDidUpdate(() => {
    const data = activeMenu.data;
    const paramsData = {
      search: data?.search || undefined,
    };
    dispatch(fetchParcelOptions(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu.data]);

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchParcelOptions({}));
      dispatch(disableRefetch(activeMenu));
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

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
          {t('options')}
        </Typography.Title>
        <Button
          type='primary'
          icon={<PlusOutlined />}
          onClick={goToAddUnit}
          style={{ width: '100%' }}
        >
          {t('add.option')}
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
            className='w-100'
            handleChange={(search) => handleFilter(search, 'search')}
            defaultValue={activeMenu.data?.search}
            resetSearch={!activeMenu.data?.search}
          />
        </Col>
        <OutlinedButton onClick={allDelete} color='red'>
          {t('delete.selection')}
        </OutlinedButton>
        <FilterColumns columns={columns} setColumns={setColumns} />
      </Space>
      <Table
        scroll={{ x: true }}
        rowSelection={rowSelection}
        loading={loading}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={options}
        pagination={{
          pageSize: params.perPage,
          page: params.page,
          total: meta?.total,
          defaultCurrent: params.page,
        }}
        onChange={onChange}
        rowKey={(record) => record.id}
      />
      <CustomModal
        click={unitDelete}
        text={text ? t('delete') : t('all.delete')}
        loading={loadingBtn}
        setText={setUUID}
      />
    </Card>
  );
}
