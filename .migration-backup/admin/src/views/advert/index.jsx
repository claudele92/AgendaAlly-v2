import React, { useEffect, useState, useContext } from 'react';
import { Button, Table, Space, Switch, Typography, Divider, Col } from 'antd';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import advertService from 'services/advert';
import { fetchAdverts } from 'redux/slices/advert';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import SearchInput from 'components/search-input';
import { useNavigate } from 'react-router-dom';
import CustomModal from 'components/modal';
import { Context } from 'context/context';
import { toast } from 'react-toastify';
import FilterColumns from 'components/filter-column';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import useDidUpdate from 'helpers/useDidUpdate';
import Card from 'components/card';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';

export default function Advert() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const {
    advertList,
    loading: listLoading,
    meta,
  } = useSelector((state) => state.advert, shallowEqual);

  const [id, setId] = useState(null);
  const [text, setText] = useState(null);
  const [search, setSearch] = useState('');
  const [active, setActive] = useState(null);
  const { setIsModalVisible } = useContext(Context);
  const [loading, setLoading] = useState(false);
  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      is_show: true,
      sorter: (a, b) => a.id - b.id,
    },
    {
      title: t('title'),
      dataIndex: 'title',
      is_show: true,
      render: (_, row) => {
        return <span>{row?.translation?.title}</span>;
      },
    },
    {
      title: t('price'),
      dataIndex: 'price',
      is_show: true,
    },

    {
      title: t('time'),
      dataIndex: 'time',
      is_show: true,
    },
    {
      title: t('time.type'),
      dataIndex: 'time_type',
      is_show: true,
    },
    {
      title: t('type'),
      dataIndex: 'type',
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
            setId(row.id);
            setActive(true);
          }}
          checked={active}
        />
      ),
    },
    {
      title: t('options'),
      dataIndex: 'options',
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

  const paramsData = {
    perPage: 10,
    page: 1,
  };

  const goToEdit = (row) => {
    dispatch(
      addMenu({
        url: `advert/${row.id}`,
        id: 'ad_edit',
        name: t('edit.ad'),
      }),
    );
    navigate(`/advert/${row.id}`);
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchAdverts(paramsData));
      dispatch(disableRefetch(activeMenu));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    const paramsData = {
      search: search || undefined,
    };
    dispatch(fetchAdverts(paramsData));
  }, [activeMenu.data, search]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const clearData = () => {
    dispatch(
      setMenuData({
        activeMenu,
        data: null,
      }),
    );
  };

  const advertDelete = () => {
    setLoading(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };

    advertService
      .delete(params)
      .then(() => {
        setIsModalVisible(false);
        toast.success(t('successfully.deleted'));
        dispatch(fetchAdverts(paramsData));
        setText(null);
        setActive(false);
      })
      .finally(() => {
        setLoading(false);
        setId(null);
      });
  };

  const goToAddAdvert = () => {
    dispatch(
      addMenu({
        id: 'advert-add',
        url: 'advert/add',
        name: t('add.advert'),
      }),
    );
    clearData();
    navigate('/advert/add');
  };

  const handleActive = () => {
    setLoading(true);
    advertService
      .setActive(id)
      .then(() => {
        setIsModalVisible(false);
        dispatch(fetchAdverts(paramsData));
        toast.success(t('successfully.updated'));
        setActive(false);
      })
      .finally(() => setLoading(false));
  };

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;
    dispatch(fetchAdverts({ perPage: pageSize, page: current }));
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
            {t('ads')}
          </Typography.Title>
          <Button
            type='primary'
            icon={<PlusOutlined />}
            onClick={goToAddAdvert}
            style={{ width: '100%' }}
          >
            {t('add.advert')}
          </Button>
        </Space>
        <Divider color='var(--divider)' />
        <Space
          className='w-100 justify-content-end align-items-center'
          style={{ rowGap: '6px', columnGap: '6px', marginBottom: '20px' }}
        >
          <Col style={{ minWidth: '170px' }}>
            <SearchInput
              className='w-100'
              handleChange={(value) => setSearch(value)}
              placeholder={t('search')}
            />
          </Col>
          <FilterColumns columns={columns} setColumns={setColumns} />
        </Space>
        <Table
          scroll={{ x: true }}
          dataSource={advertList}
          columns={columns?.filter((item) => item.is_show)}
          rowKey={(record) => record.id}
          loading={loading || listLoading}
          pagination={{
            pageSize: meta.per_page,
            page: meta.current_page,
            total: meta.total,
          }}
          onChange={onChangePagination}
        />
      </Card>
      <CustomModal
        click={active ? handleActive : advertDelete}
        text={
          active ? t('set.active.advert') : text ? t('delete') : t('all.delete')
        }
        setText={setId}
        setActive={setActive}
      />
    </>
  );
}
