import React, { useEffect, useState } from 'react';
import { Divider, Table, Typography } from 'antd';
import { useTranslation } from 'react-i18next';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { fetchRoles } from 'redux/slices/role';
import { disableRefetch } from 'redux/slices/menu';
import Card from 'components/card';
import useDidUpdate from 'helpers/useDidUpdate';

export default function RoleList() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const { loading, roles } = useSelector((state) => state.role, shallowEqual);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
    },
    {
      title: t('name'),
      dataIndex: 'name',
      key: 'name',
      render: (name) => t(name),
    },
  ];

  const [columns, setColumns] = useState(initialColumns);

  useEffect(() => {
    if (activeMenu?.refetch) {
      dispatch(fetchRoles({}));
      dispatch(disableRefetch(activeMenu));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu?.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  return (
    <Card>
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
        {t('user.roles')}
      </Typography.Title>
      <Divider color='var(--divider)' />
      <Table
        scroll={{ x: true }}
        columns={columns}
        dataSource={roles}
        loading={loading}
        pagination={false}
        rowKey={(record) => record.id}
      />
    </Card>
  );
}
