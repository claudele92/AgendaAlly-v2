import React, { useContext, useEffect, useState } from 'react';
import { Alert, Button, Divider, Space, Table, Typography } from 'antd';
import { useNavigate } from 'react-router-dom';
import { DeleteOutlined, EditOutlined, PlusOutlined } from '@ant-design/icons';
import CustomModal from 'components/modal';
import { Context } from 'context/context';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch } from 'redux/slices/menu';
import { toast } from 'react-toastify';
import { useTranslation } from 'react-i18next';
import { fetchMessageSubscriber } from 'redux/slices/messegeSubscriber';
import messageSubscriberService from 'services/messageSubscriber';
import moment from 'moment';
import FilterColumns from 'components/filter-column';
import { getHourFormat } from 'helpers/getHourFormat';
import Card from 'components/card';
import tableRowClasses from 'assets/scss/components/table-row.module.scss';
import useDidUpdate from 'helpers/useDidUpdate';
import OutlinedButton from 'components/outlined-button';

const typeNames = {
  verify: 'Email Verification',
  reset: 'Password Reset',
  subscribe: 'Subscription / Digest',
  order: 'Order (financial/deferred)',
  refund_requested: 'Refund request received',
  refund_approved: 'Refund approved — not yet refunded',
  refund_completed: 'Refund completed',
  refund_rejected: 'Refund request rejected',
  payout_requested: 'Payout request received',
  payout_approved: 'Payout approved — not yet paid',
  payout_completed: 'Payout completed',
  payout_rejected: 'Payout request rejected',
};

const isSystemTemplate = (row) =>
  Boolean(row?.system_template) || ['verify', 'reset'].includes(row?.type);

const MessageSubscribed = () => {
  const { t, i18n } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const hourFormat = getHourFormat();

  const goToEdit = (row) => {
    dispatch(
      addMenu({
        url: `message/subscriber/${row.id}`,
        id: 'subciribed_edit',
        name: t('edit.subscriber'),
      }),
    );
    navigate(`/message/subscriber/${row.id}`);
  };

  const initialColumns = [
    {
      title: t('id'),
      dataIndex: 'id',
      key: 'id',
      is_show: true,
    },
    {
      title: t('type'),
      dataIndex: 'type',
      key: 'type',
      is_show: true,
      render: (value) => typeNames[value] || value,
    },
    {
      title: t('subject'),
      dataIndex: 'subject',
      key: 'subject',
      is_show: true,
      render: (value) => value || '—',
    },
    {
      title: 'Management',
      key: 'template_class',
      is_show: true,
      render: (_, row) => row.template_class === 'financial_library'
        ? 'Committed manual financial event / SMTP disabled'
        : isSystemTemplate(row)
        ? 'Protected system template'
        : row.type === 'subscribe'
          ? 'Custom content / Subscription workflow'
          : 'Application controlled (legacy record)',
    },
    {
      title: t('created.at'),
      dataIndex: 'created_at',
      key: 'created_at',
      is_show: true,
      render: (created_at) =>
        moment(created_at).format(`YYYY-MM-DD ${hourFormat}`),
    },
    {
      title: t('actions'),
      key: 'actions',
      dataIndex: 'actions',
      is_show: true,
      render: (_, row) => (
        <div className={tableRowClasses.options}>
          <button
            type='button'
            className={`${tableRowClasses.option} ${tableRowClasses.edit}`}
            disabled={row.type === 'order' || row.editable === false}
            title={row.type === 'order' ? 'Order/Invoice is application controlled' : 'Edit template'}
            onClick={(e) => {
              e.stopPropagation();
              goToEdit(row);
            }}
          >
            <EditOutlined />
          </button>
          {!isSystemTemplate(row) && (
            <button
              type='button'
              className={`${tableRowClasses.option} ${tableRowClasses.delete}`}
              aria-label={t('delete')}
              onClick={() => {
                setIsModalVisible(true);
                setId([row.id]);
                setType(false);
                setText(true);
              }}
            >
              <DeleteOutlined />
            </button>
          )}
        </div>
      ),
    },
  ];
  const [columns, setColumns] = useState(initialColumns);

  const { setIsModalVisible } = useContext(Context);
  const [id, setId] = useState(null);
  const [type, setType] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [text, setText] = useState(null);

  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { subscribers, loading, meta } = useSelector(
    (state) => state.messageSubscriber,
    shallowEqual,
  );

  const subscriberDelete = () => {
    setLoadingBtn(true);
    const currentRows = Array.isArray(subscribers) ? subscribers : [];
    const deletableIds = (id || []).filter((selectedId) => {
      const row = currentRows.find((item) => item.id === selectedId);
      return row && !isSystemTemplate(row);
    });
    if (!deletableIds.length) {
      setLoadingBtn(false);
      setIsModalVisible(false);
      toast.warning(
        t(
          'email.template.system.delete.prevented',
          'System email templates cannot be deleted.',
        ),
      );
      return;
    }
    const params = {
      ...Object.assign(
        {},
        ...deletableIds.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };
    messageSubscriberService
      .delete(params)
      .then(() => {
        dispatch(fetchMessageSubscriber({}));
        toast.success(t('successfully.deleted'));
      })
      .finally(() => {
        setIsModalVisible(false);
        setLoadingBtn(false);
        setText(null);
      });
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      dispatch(fetchMessageSubscriber({}));
      dispatch(disableRefetch(activeMenu));
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    setColumns(initialColumns);
  }, [i18n?.store?.data?.[`${i18n?.language}`]?.translation]);

  const onChangePagination = (pageNumber) => {
    const { pageSize, current } = pageNumber;
    dispatch(fetchMessageSubscriber({ perPage: pageSize, page: current }));
  };

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => {
      const currentRows = Array.isArray(subscribers) ? subscribers : [];
      setId(
        key.filter((selectedId) => {
          const row = currentRows.find((item) => item.id === selectedId);
          return row && !isSystemTemplate(row);
        }),
      );
    },
    getCheckboxProps: (record) => ({ disabled: isSystemTemplate(record) }),
  };

  const allDelete = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.message.subscriber'));
    } else {
      setIsModalVisible(true);
      setText(false);
    }
  };

  const goToAdd = () => {
    dispatch(
      addMenu({
        id: 'message_subscriber_add',
        url: `message/subscriber/add`,
        name: t('add.subciribed'),
      }),
    );
    navigate(`/message/subscriber/add`);
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
          Email Template Library
        </Typography.Title>
        <Button
          type='primary'
          icon={<PlusOutlined />}
          onClick={goToAdd}
          style={{ width: '100%' }}
        >
          Create Email Template
        </Button>
      </Space>
      <Alert
        type='info'
        showIcon
        style={{ marginTop: 16 }}
        message='Presentation templates are separate from email workflows'
        description='Email Verification, Password Reset and the built-in Subscription / Digest default are protected system templates. Existing Subscription workflows remain separate. Refund/Payout templates render committed manual financial events locally; financial SMTP is disabled. Order / Invoice, Driver Invitation and Admin Test Email remain application controlled.'
      />
      <Divider color='var(--divider)' />
      <Space
        className='w-100 justify-content-end align-items-center'
        style={{ rowGap: '6px', columnGap: '6px', marginBottom: '20px' }}
      >
        <OutlinedButton onClick={allDelete} color='red'>
          {t('delete.selection')}
        </OutlinedButton>
        <FilterColumns columns={columns} setColumns={setColumns} />
      </Space>
      <Table
        pagination={{
          current: meta?.current_page || 1,
          pageSize: meta?.per_page || 10,
          total: meta?.total ?? (Array.isArray(subscribers) ? subscribers.length : 0),
          showSizeChanger: true,
        }}
        scroll={{ x: true }}
        rowSelection={rowSelection}
        columns={columns?.filter((item) => item.is_show)}
        dataSource={Array.isArray(subscribers) ? subscribers : []}
        rowKey={(record) => record.id}
        loading={loading}
        onChange={onChangePagination}
      />
      <CustomModal
        click={subscriberDelete}
        text={
          type ? t('set.active.banner') : text ? t('delete') : t('all.delete')
        }
        loading={loadingBtn}
        setText={setId}
      />
    </Card>
  );
};

export default MessageSubscribed;
