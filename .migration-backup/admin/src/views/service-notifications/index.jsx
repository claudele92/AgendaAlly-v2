import { useTranslation } from 'react-i18next';
import { batch, shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import React, { useContext, useEffect, useState } from 'react';
import { Context } from 'context/context';
import { Button, Divider, Space, Table, Typography } from 'antd';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import { toast } from 'react-toastify';
import { fetchAdminServiceNotifications } from 'redux/slices/service-notifications';
import { addMenu, disableRefetch, setMenuData } from 'redux/slices/menu';
import formatSortType from 'helpers/formatSortType';
import CustomModal from 'components/modal';
import serviceNotificationsService from 'services/service-notifications';
import getFullDateTime from 'helpers/getFullDateTime';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import useDidUpdate from 'helpers/useDidUpdate';
import Card from 'components/card';
import OutlinedButton from 'components/outlined-button';
import FilterColumns from 'components/filter-column';

function ServiceNotifications() {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { setIsModalVisible } = useContext(Context);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { serviceNotifications, loading, params, meta } = useSelector(
    (state) => state.serviceNotifications,
    shallowEqual,
  );

  const [text, setText] = useState(null);
  const [id, setId] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      is_show: true,
      sorter: true,
      key: 'id',
    },
    {
      title: t('service.master.id'),
      is_show: true,
      dataIndex: 'service_master_id',
      key: 'service_master_id',
    },
    {
      title: t('last.sent.at'),
      is_show: true,
      dataIndex: 'last_sent_at',
      key: 'last_sent_at',
      render: (last_sent_at) => getFullDateTime(last_sent_at),
    },
    {
      title: t('notification.time'),
      is_show: true,
      dataIndex: 'notification_time',
      key: 'notification_time',
      render: (notification_time, row) =>
        `${notification_time} ${row?.notification_type}`,
    },
    {
      title: t('actions'),
      dataIndex: 'actions',
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
  ];
  const [columns, setColumns] = useState(initialColumns);

  const deleteSelected = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.service.notification'));
    } else {
      setIsModalVisible(true);
      setText('all.delete');
    }
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

    serviceNotificationsService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setId(null);
        setIsModalVisible(false);
        setText('');
        dispatch(fetchAdminServiceNotifications({}));
      })
      .finally(() => setLoadingBtn(false));
  };

  const goToEdit = (id) => {
    const url = `service-notifications/${id}`;
    dispatch(
      addMenu({
        id: 'service-notifications-edit',
        url,
        name: t('edit.service.notifications'),
      }),
    );

    navigate('/' + url);
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
        id: 'service-notifications-add',
        url: `service-notifications/add`,
        name: t('add.service.notifications'),
      }),
    );
    clearData();
    navigate(`/service-notifications/add`);
  };

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      setId(key);
    },
  };

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

  useEffect(() => {
    if (activeMenu.refetch) {
      batch(() => {
        dispatch(fetchAdminServiceNotifications({}));
        dispatch(disableRefetch(activeMenu));
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

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
            {t('service.notifications')}
          </Typography.Title>
          <Button
            type='primary'
            icon={<PlusOutlined />}
            onClick={goToAdd}
            style={{ width: '100%' }}
          >
            {t('add.service.notification')}
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
          rowSelection={rowSelection}
          columns={columns?.filter((item) => item.is_show)}
          dataSource={serviceNotifications}
          loading={loading}
          pagination={{
            pageSize: params?.perPage,
            page: activeMenu.data?.page || 1,
            total: meta?.total,
            defaultCurrent: activeMenu.data?.page,
            current: activeMenu.data?.page,
          }}
          rowKey={(record) => record?.id}
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

export default ServiceNotifications;
