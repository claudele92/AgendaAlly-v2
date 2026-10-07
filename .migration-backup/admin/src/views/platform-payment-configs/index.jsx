import React, { useContext, useEffect, useState } from 'react';
import ProviderStateSummary from 'components/payment/provider-state-summary';
import { Button, Card, Space, Table } from 'antd';
import {
  DeleteOutlined,
  EditOutlined,
  PlusCircleOutlined,
} from '@ant-design/icons';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { Context } from 'context/context';
import CustomModal from 'components/modal';
import platformPaymentConfigService from 'services/platformPaymentConfig';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { addMenu, disableRefetch } from 'redux/slices/menu';
import { useTranslation } from 'react-i18next';
import DeleteButton from 'components/delete-button';
import { SuperAdminRoute } from 'context/superadmin-route';

const PlatformPaymentConfigs = () => {
  const { t } = useTranslation();
  const { setIsModalVisible } = useContext(Context);
  const [id, setId] = useState(null);
  const [text, setText] = useState(null);
  const [loading, setLoading] = useState(false);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [rows, setRows] = useState([]);
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);

  const fetchConfigs = () => {
    setLoading(true);
    platformPaymentConfigService
      .paginate()
      .then(({ data }) => setRows(data))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    fetchConfigs();
    dispatch(disableRefetch(activeMenu));
    // eslint-disable-next-line
  }, [activeMenu.refetch]);

  const goToAdd = () => {
    dispatch(
      addMenu({
        id: 'platform-payment-config-add',
        url: 'platform-payment-configs/add',
        name: t('add.platform.payment.config'),
      }),
    );
    navigate('/platform-payment-configs/add');
  };

  const goToEdit = (row) => {
    dispatch(
      addMenu({
        url: `platform-payment-configs/${row.id}`,
        id: 'platform-payment-config-edit',
        name: t('edit.platform.payment.config'),
      }),
    );
    navigate(`/platform-payment-configs/${row.id}`);
  };

  const deleteConfig = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({ [`ids[${index}]`]: item })),
      ),
    };
    platformPaymentConfigService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        setIsModalVisible(false);
        fetchConfigs();
        setText(null);
      })
      .finally(() => {
        setId(null);
        setLoadingBtn(false);
      });
  };

  const rowSelection = {
    selectedRowKeys: id,
    onChange: (key) => setId(key),
  };

  const allDelete = () => {
    if (id === null || id.length === 0) {
      toast.warning(t('select.the.product'));
    } else {
      setIsModalVisible(true);
      setText(false);
    }
  };

  const columns = [
    {
      title: 'Provider state (not activation)',
      dataIndex: 'provider_state',
      key: 'provider_state',
      render: (state) => <ProviderStateSummary state={state} />,
    },
    {
      title: t('country'),
      dataIndex: 'country',
      key: 'country',
      render: (country) => country?.name,
    },
    {
      title: t('payment'),
      dataIndex: 'payment',
      key: 'payment',
      render: (payment) => payment?.tag,
    },
    {
      title: t('active'),
      dataIndex: 'status',
      key: 'status',
      render: (status) => (status ? t('active') : t('inactive')),
    },
    {
      title: t('options'),
      key: 'options',
      dataIndex: 'options',
      render: (_, row) => (
        <Space>
          <Button
            type='primary'
            icon={<EditOutlined />}
            onClick={() => goToEdit(row)}
          />
          <DeleteButton
            icon={<DeleteOutlined />}
            onClick={() => {
              setId([row.id]);
              setIsModalVisible(true);
              setText(true);
            }}
          />
        </Space>
      ),
    },
  ];

  return (
    <SuperAdminRoute>
    <Card
      title={t('platform.payment.configs')}
      extra={
        <Space>
          <Button
            icon={<PlusCircleOutlined />}
            type='primary'
            onClick={goToAdd}
          >
            {t('add.platform.payment.config')}
          </Button>
          <DeleteButton onClick={allDelete}>
            {t('delete.selected')}
          </DeleteButton>
        </Space>
      }
    >
      <Table
        scroll={{ x: true }}
        rowSelection={rowSelection}
        columns={columns}
        dataSource={rows}
        rowKey={(record) => record.id}
        loading={loading}
        pagination={false}
      />
      <CustomModal
        click={deleteConfig}
        text={text ? t('delete') : t('all.delete')}
        setText={setId}
        loading={loadingBtn}
      />
    </Card>
    </SuperAdminRoute>
  );
};

export default PlatformPaymentConfigs;
