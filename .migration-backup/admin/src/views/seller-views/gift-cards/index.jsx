import { Button, Divider, Space, Table, Typography } from 'antd';
import { useTranslation } from 'react-i18next';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import { useNavigate } from 'react-router-dom';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import React, { useContext, useEffect, useState } from 'react';
import { fetchSellerGiftCards } from 'redux/slices/gift-cards';
import numberToPrice from 'helpers/numberToPrice';
import CustomModal from 'components/modal';
import { toast } from 'react-toastify';
import SellerGiftCardService from 'services/seller/gift-cards';
import { Context } from 'context/context';
import useDidUpdate from 'helpers/useDidUpdate';
import Card from 'components/card';
import OutlinedButton from 'components/outlined-button';
import FilterColumns from 'components/filter-column';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';

function SellerGiftCards() {
  const { t, i18n } = useTranslation();
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const { setIsModalVisible } = useContext(Context);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const {
    giftCards,
    loading,
    meta,
    params: paramsData,
  } = useSelector((state) => state.giftCards);
  const [id, setId] = useState(null);
  const [modalText, setModalText] = useState('');
  const [loadingBtn, setLoadingBtn] = useState(false);

  const goToEdit = (id) => {
    dispatch(
      addMenu({
        id: 'gift_card_edit',
        url: `seller/gift-cards/${id}`,
        name: 'edit.gift.card',
      }),
    );
    clearData();
    navigate(`/seller/gift-cards/${id}`);
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
    },
    {
      title: t('price'),
      dataIndex: 'price',
      key: 'price',
      is_show: true,
      render: (price) => numberToPrice(price),
    },
    {
      title: t('time'),
      dataIndex: 'time',
      key: 'time',
      is_show: true,
    },
    {
      title: t('options'),
      dataIndex: 'options',
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
              setId([record.id]);
              setIsModalVisible(true);
              setModalText('delete');
            }}
          >
            <DeleteOutlined />
          </button>
        </div>
      ),
    },
  ];
  const [columns, setColumns] = useState(initialColumns);

  const clearData = () => {
    dispatch(setMenuData({ activeMenu, data: null }));
  };

  const goToCreate = () => {
    dispatch(
      addMenu({
        id: 'add_gift_card',
        url: 'seller/gift-cards/add',
        name: 'add.gift.card',
      }),
    );
    clearData();
    navigate('/seller/gift-cards/add');
  };

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;

    const params = {
      ...paramsData,
      perPage: pageSize,
      page: current,
    };

    dispatch(fetchSellerGiftCards(params));
  };

  const allDelete = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.gift.card'));
    } else {
      setIsModalVisible(true);
      setModalText('all.delete');
    }
  };

  const giftCardDelete = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };
    SellerGiftCardService.delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setIsModalVisible(false);
        dispatch(fetchSellerGiftCards(params));
        setModalText('');
      })
      .finally(() => setLoadingBtn(false));
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchSellerGiftCards(paramsData));
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
          {t('gift.cards')}
        </Typography.Title>
        <Button
          type='primary'
          icon={<PlusOutlined />}
          onClick={goToCreate}
          style={{ width: '100%' }}
        >
          {t('add.gift.card')}
        </Button>
      </Space>
      <Divider color='var(--divider)' />
      <Space
        className='w-100 justify-content-end align-items-center'
        style={{ rowGap: '6px', columnGap: '6px', marginBottom: '6px' }}
      >
        <OutlinedButton onClick={allDelete} color='red'>
          {t('delete.selection')}
        </OutlinedButton>
        <FilterColumns columns={columns} setColumns={setColumns} />
      </Space>
      <Table
        scroll={{ x: true }}
        rowKey={(record) => record.id}
        rowSelection={rowSelection}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={giftCards}
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
        click={giftCardDelete}
        text={modalText}
        loading={loadingBtn}
        setText={setModalText}
      />
    </Card>
  );
}

export default SellerGiftCards;
