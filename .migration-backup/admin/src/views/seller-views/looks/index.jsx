import React, { Fragment, useContext, useState, useEffect } from 'react';
import { Button, Space, Switch, Table, Typography, Divider, Col } from 'antd';
import { useTranslation } from 'react-i18next';
import moment from 'moment';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import SearchInput from 'components/search-input';
import { Context } from 'context/context';
import { toast } from 'react-toastify';
import CustomModal from 'components/modal';
import { shallowEqual, useSelector, useDispatch, batch } from 'react-redux';
import useDidUpdate from 'helpers/useDidUpdate';
import { sellerFetchLooks } from 'redux/slices/looks';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import sellerLooksService from 'services/seller/banner';
import { useNavigate } from 'react-router-dom';
import { getHourFormat } from 'helpers/getHourFormat';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import Card from 'components/card';
import ColumnImage from 'components/column-image';
import OutlinedButton from 'components/outlined-button';
import FilterColumns from 'components/filter-column';

export default function Looks() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { setIsModalVisible } = useContext(Context);

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { looks, loading, params, meta } = useSelector(
    (state) => state.looks,
    shallowEqual,
  );

  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [active, setActive] = useState(false);
  const hourFormat = getHourFormat();

  const paramsData = {
    search: activeMenu?.data?.search ?? null,
    ...params,
  };

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
      render: (_, record) => record?.translation?.title,
    },
    {
      title: t('image'),
      dataIndex: 'img',
      key: 'img',
      is_show: true,
      render: (_, record) => (
        <ColumnImage image={record?.img} id={record?.id} />
      ),
    },
    {
      title: t('created.at'),
      dataIndex: 'created_at',
      key: 'created_at',
      is_show: true,
      render: (_, record) =>
        moment(record?.created_at).format(`DD-MM-YYYY ${hourFormat}`),
    },
    {
      title: t('active'),
      dataIndex: 'active',
      key: 'active',
      is_show: true,
      render: (_, record) => (
        <Switch
          onChange={() => {
            setId([record?.id]);
            setActive(true);
            setIsModalVisible(true);
          }}
          checked={record?.active}
        />
      ),
    },
    {
      title: t('actions'),
      key: 'actions',
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

  const handleActive = () => {
    setLoadingBtn(true);
    sellerLooksService
      .setActive(id)
      .then(() => {
        dispatch(sellerFetchLooks({ paramsData }));
        toast.success(t('successfully.updated'));
        setIsModalVisible(false);
        setActive(false);
        setId(null);
      })
      .finally(() => setLoadingBtn(false));
  };

  const handleDeleteLook = () => {
    setLoadingBtn(true);

    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };

    sellerLooksService
      .delete(params)
      .then(() => {
        dispatch(sellerFetchLooks({ paramsData }));
        toast.success(t('successfully.deleted'));
        setIsModalVisible(false);
        setActive(false);
        setId(null);
      })
      .finally(() => setLoadingBtn(false));
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

  const deleteSelected = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.look'));
    } else {
      setIsModalVisible(true);
    }
  };

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

  const clearData = () => {
    dispatch(
      setMenuData({
        activeMenu,
        data: null,
      }),
    );
  };

  const goToAdd = () => {
    dispatch(
      addMenu({
        id: 'look_add',
        url: 'seller/looks/add',
        name: 'add.look',
      }),
    );
    clearData();
    navigate('add');
  };

  const goToEdit = (id) => {
    dispatch(
      addMenu({
        id: 'look_edit',
        url: `seller/looks/${id}`,
        name: 'edit.look',
      }),
    );
    clearData();
    navigate(`${id}`);
  };

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;

    const params = {
      ...paramsData,
      perPage: pageSize,
      page: current,
    };

    dispatch(sellerFetchLooks(params));
  };

  useDidUpdate(() => {
    dispatch(sellerFetchLooks(paramsData));
    dispatch(disableRefetch(activeMenu));
  }, [activeMenu.data]);

  useEffect(() => {
    if (activeMenu.refetch) {
      batch(() => {
        dispatch(sellerFetchLooks(paramsData));
        dispatch(disableRefetch(activeMenu));
      });
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  return (
    <Fragment>
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
            {t('looks')}
          </Typography.Title>
          <Button
            type='primary'
            icon={<PlusOutlined />}
            onClick={goToAdd}
            style={{ width: '100%' }}
          >
            {t('add.look')}
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
              handleChange={(e) => handleFilter({ search: e })}
            />
          </Col>
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
          dataSource={looks}
          loading={loading}
          pagination={{
            pageSize: meta.per_page,
            page: meta.current_page,
            total: meta.total,
          }}
          onChange={onChangePagination}
        />
      </Card>
      <CustomModal
        click={active ? handleActive : handleDeleteLook}
        text={
          active
            ? t('set.active')
            : t('are.you.sure.you.want.to.delete.the.selected.products')
        }
        loading={loadingBtn}
        setActive={setActive}
      />
    </Fragment>
  );
}
