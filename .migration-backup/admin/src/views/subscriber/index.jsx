import React, { useEffect, useState } from 'react';
import { Divider, Space, Table, Tag, Typography } from 'antd';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { disableRefetch } from 'redux/slices/menu';
import { useTranslation } from 'react-i18next';
import { fetchSubscriber } from 'redux/slices/subscriber';
import FilterColumns from 'components/filter-column';
import hideEmail from 'components/hideEmail';
import useDemo from 'helpers/useDemo';
import useDidUpdate from 'helpers/useDidUpdate';
import Card from 'components/card';

const Subciribed = () => {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const { isDemo } = useDemo();

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
      render: (_, row) => {
        return (
          <div>
            {row?.user?.firstname} {row?.user?.lastname || ''}
          </div>
        );
      },
    },
    {
      title: t('email'),
      dataIndex: 'email',
      key: 'email',
      is_show: true,
      render: (_, row) => {
        return (
          <div>{isDemo ? hideEmail(row.user?.email) : row.user?.email}</div>
        );
      },
    },
    {
      title: t('status'),
      dataIndex: 'active',
      key: 'active',
      is_show: true,
      render: (active) => {
        return (
          <Tag color={active ? 'blue' : 'red'}>
            {active ? t('subscriber') : t('not.subscriber')}
          </Tag>
        );
      },
    },
  ];
  const [columns, setColumns] = useState(initialColumns);

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { subscriber, meta, loading, params } = useSelector(
    (state) => state.subscriber,
    shallowEqual,
  );

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchSubscriber({}));
      dispatch(disableRefetch(activeMenu));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;
    dispatch(fetchSubscriber({ perPage: pageSize, page: current }));
  };

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
          {t('subscribers')}
        </Typography.Title>
        <FilterColumns columns={columns} setColumns={setColumns} />
      </Space>
      <Divider color='var(--divider)' />
      <Table
        scroll={{ x: true }}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={subscriber}
        pagination={{
          pageSize: params.perPage,
          page: activeMenu.data?.page || 1,
          total: meta.total,
          defaultCurrent: activeMenu.data?.page,
          current: activeMenu.data?.page,
        }}
        rowKey={(record) => record.id}
        loading={loading}
        onChange={onChangePagination}
      />
    </Card>
  );
};

export default Subciribed;
