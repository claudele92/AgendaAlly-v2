import React, { useEffect, useState } from 'react';
import { Divider, Space, Table, Typography } from 'antd';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { disableRefetch } from 'redux/slices/menu';
import { useTranslation } from 'react-i18next';
import { fetchAdminStoreis } from 'redux/slices/storeis';
import FilterColumns from 'components/filter-column';
import ColumnImage from 'components/column-image';
import useDidUpdate from 'helpers/useDidUpdate';
import Card from 'components/card';
import moment from 'moment';
import tableRowClasses from '../../assets/scss/components/table-row.module.scss';
import getFullDateTime from '../../helpers/getFullDateTime';

const Storeis = () => {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { storeis, meta, loading } = useSelector(
    (state) => state.storeis,
    shallowEqual,
  );

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      is_show: true,
    },
    {
      title: t('image'),
      dataIndex: 'file_urls',
      key: 'file_urls',
      is_show: true,
      render: (img, row) => <ColumnImage image={img} id={row?.id} />,
    },
    {
      title: t('type'),
      key: 'type',
      dataIndex: 'model_type',
      is_show: true,
      render: (type) => t(type),
    },
    {
      title: t('expired.at'),
      dataIndex: 'created_at',
      is_show: true,
      render: (date) => {
        const expiredAt = moment(date).add(24, 'hours');
        const isExpired = moment(new Date()).isAfter(expiredAt);
        return (
          <div className={tableRowClasses.status}>
            <span
              className={`${isExpired ? tableRowClasses.unpublished : tableRowClasses.published}`}
            >
              {getFullDateTime(expiredAt)}
            </span>
          </div>
        );
      },
    },
    {
      title: t('title'),
      key: 'title',
      dataIndex: 'model',
      is_show: true,
      render: (model) => model?.translation?.title,
    },
  ];

  const [columns, setColumns] = useState(initialColumns);

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchAdminStoreis({}));
      dispatch(disableRefetch(activeMenu));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;
    dispatch(fetchAdminStoreis({ perPage: pageSize, page: current }));
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
          {t('stories')}
        </Typography.Title>
        <FilterColumns columns={columns} setColumns={setColumns} />
      </Space>
      <Divider color='var(--divider)' />
      <Table
        scroll={{ x: true }}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={storeis}
        pagination={{
          pageSize: meta.per_page,
          page: meta.current_page,
          total: meta.total,
        }}
        rowKey={(record) => record.id}
        loading={loading}
        onChange={onChangePagination}
      />
    </Card>
  );
};

export default Storeis;
