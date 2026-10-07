import React, { useContext, useEffect, useState } from 'react';
import { Badge, Button, Col, Row, Space, Spin } from 'antd';
import subscriptionService from 'services/subscriptions';
import { shallowEqual, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import SubscriptionEditModal from './subscriptions-edit';
import SubscriptionAddModal from './subscriptions-add';
import {
  DeleteOutlined,
  EditOutlined,
  PlusCircleOutlined,
} from '@ant-design/icons';
import { useDispatch } from 'react-redux';
import { disableRefetch } from 'redux/slices/menu';
import Card from 'components/card';
import { Context } from 'context/context';
import { toast } from 'react-toastify';
import DeleteButton from 'components/delete-button';
import CustomModal from 'components/modal';

const features = [];

const Subscriptions = () => {
  const { t } = useTranslation();
  const { defaultCurrency } = useSelector(
    (state) => state.currency,
    shallowEqual,
  );
  const { setIsModalVisible } = useContext(Context);
  const [add, setAdd] = useState(null);
  const [edit, setEdit] = useState(null);
  const [id, setId] = useState(null);
  const [loading, setLoading] = useState(false);
  const [data, setData] = useState([]);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const colCount = data.length;
  const dispatch = useDispatch();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);

  const fetchSubscriptionList = () => {
    setLoading(true);
    subscriptionService
      .getAll()
      .then((res) => {
        setData(res.data);
      })
      .finally(() => {
        setLoading(false);
        dispatch(disableRefetch(activeMenu));
      });
  };

  useEffect(() => {
    if (activeMenu.refetch) {
      fetchSubscriptionList();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [activeMenu.refetch]);

  const subscriptionDelete = () => {
    setLoadingBtn(true);
    const params = {
      ...Object.assign(
        {},
        ...id.map((item, index) => ({
          [`ids[${index}]`]: item,
        })),
      ),
    };
    subscriptionService
      .delete(params)
      .then(() => {
        toast.success(t('successfully.deleted'));
        fetchSubscriptionList();
        setIsModalVisible(false);
        setId(null);
      })
      .finally(() => {
        setLoadingBtn(false);
      });
  };

  return (
    <>
      <Card
        className='h-100'
        extra={
          <Space>
            <Button
              type='primary'
              icon={<PlusCircleOutlined />}
              onClick={() => setAdd(true)}
            >
              {t('add.subscription')}
            </Button>
          </Space>
        }
      >
        {!loading ? (
          <div>
            <div className='text-center mb-4'>
              <h2 className='font-weight-semibold'>{t('pick.base.plan')}</h2>
              <Row type='flex' justify='center'>
                <Col sm={24} md={12} lg={8}>
                  <p>{t('pick.base.plan.subtitle')}</p>
                </Col>
              </Row>
            </div>
            <Row>
              {data.map((elm, i) => (
                <Col
                  key={`price-column-${i}`}
                  span={6}
                  className={colCount === i + 1 ? '' : 'border-right'}
                >
                  <div className='p-3'>
                    <div className='text-center'>
                      <h1 className='display-4 mt-4'>
                        {defaultCurrency?.position === 'before' && (
                          <span
                            className='font-size-md d-inline-block mr-1'
                            style={{ transform: 'translate(0px, -17px)' }}
                          >
                            {defaultCurrency?.symbol}
                          </span>
                        )}
                        <span>{elm.price}</span>
                        {defaultCurrency?.position === 'after' && (
                          <span
                            className='font-size-md d-inline-block mr-1'
                            style={{ transform: 'translate(0px, -17px)' }}
                          >
                            {defaultCurrency?.symbol}
                          </span>
                        )}
                      </h1>
                      <p className='mb-0 text-lowercase'>
                        {elm.month} {t('month')}
                      </p>
                    </div>
                    <div className='mt-4'>
                      <h2
                        className='text-center font-weight-semibold cursor-pointer'
                        onClick={() => setEdit(elm)}
                      >
                        {elm.title}{' '}
                        <span className='ant-btn-link'>
                          <EditOutlined />
                        </span>
                      </h2>
                      <div className='text-center'>
                        <DeleteButton
                          icon={<DeleteOutlined />}
                          onClick={() => {
                            setId([elm.id]);
                            setIsModalVisible(true);
                          }}
                        />
                      </div>
                    </div>
                    <div className='d-flex justify-content-center mt-3'>
                      <div>
                        {features?.map((elm, i) => {
                          return (
                            <p key={`pricing-feature-${i}`}>
                              <Badge color={'blue'} />
                              <span>{elm}</span>
                            </p>
                          );
                        })}
                      </div>
                    </div>
                  </div>
                </Col>
              ))}
            </Row>
          </div>
        ) : (
          <Col className='ant-col-spin d-flex justify-content-center'>
            <Spin size='large' />
          </Col>
        )}
      </Card>
      {edit && (
        <SubscriptionEditModal
          modal={edit}
          handleCancel={() => setEdit(null)}
          refetch={fetchSubscriptionList}
        />
      )}
      {add && (
        <SubscriptionAddModal
          modal={add}
          handleCancel={() => setAdd(null)}
          refetch={fetchSubscriptionList}
        />
      )}
      <CustomModal
        click={subscriptionDelete}
        text={t('delete')}
        setText={setId}
        loading={loadingBtn}
      />
    </>
  );
};

export default Subscriptions;
