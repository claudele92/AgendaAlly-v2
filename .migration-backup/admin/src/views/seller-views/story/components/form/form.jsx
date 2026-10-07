import { Alert, Button, Card, Col, Form, Row, Select, Switch } from 'antd';
import { useTranslation } from 'react-i18next';
import { STORY_TYPES } from 'constants/index';
import { InfiniteSelect } from 'components/infinite-select';
import React, { useEffect, useState } from 'react';
import StoryMedia from './story-media';
import productService from 'services/seller/product';
import { batch, shallowEqual, useDispatch, useSelector } from 'react-redux';
import servicesService from 'services/seller/services';
import { toast } from 'react-toastify';
import { useNavigate } from 'react-router-dom';
import { removeFromMenu, setRefetch } from 'redux/slices/menu';
import storeisService from '../../../../../services/seller/storeis';
import useDidUpdate from '../../../../../helpers/useDidUpdate';
import createImage from '../../../../../helpers/createImage';
import { useNavigationScope } from 'context/navigation-scope';

const StoryForm = ({ id, onSubmit }) => {
  const { t } = useTranslation();
  const [form] = Form.useForm();
  const dispatch = useDispatch();
  const navigate = useNavigate();

  const { myShop } = useSelector((state) => state.myShop, shallowEqual);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const { status, scope } = useNavigationScope();
  const canManage = status === 'ready' && scope?.scope_status === 'known' &&
    (scope?.shop?.owner || scope?.shop_scope?.permission_keys?.includes('stories.manage'));

  const [hasMore, setHasMore] = useState({
    product: false,
    service: false,
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isLoading, setIsLoading] = useState(!!id);
  const [image, setImage] = useState([]);
  const [uploading, setUploading] = useState(false);
  const [expiresAt, setExpiresAt] = useState(null);
  const [loadError, setLoadError] = useState(false);
  const [submitError, setSubmitError] = useState(false);

  const modelType = Form.useWatch('modelType', form);
  const modelTypeOptions = STORY_TYPES.map((item) => ({
    label: t(item),
    value: item,
    key: item,
  }));

  const fetchStory = () => {
    setIsLoading(true);
    setLoadError(false);
    storeisService
      .getById(id)
      .then((res) => {
        const body = {
          active: res?.data?.active ?? true,
          modelType: {
            label: t(res?.data?.model_type),
            value: res?.data?.model_type,
            key: res?.data?.model_type,
          },
          [res?.data?.model_type]: {
            label: res?.data?.model?.translation?.title,
            value: res?.data?.model?.id,
            key: res?.data?.model?.id,
          },
        };
        setImage((res?.data?.file_urls || []).map(createImage));
        setExpiresAt(res?.data?.expires_at);
        form.setFieldsValue(body);
      })
      .catch(() => {
        setLoadError(true);
      })
      .finally(() => {
        setIsLoading(false);
      });
  };

  const fetchProducts = ({ search = '', page = 1 }) => {
    const params = {
      search: search || undefined,
      shop_id: myShop?.id,
      status: 'published',
      active: 1,
      rest: 1,
      page,
      perPage: 10,
    };
    return productService.getAll(params).then((res) => {
      setHasMore((prev) => ({
        ...prev,
        product: res?.meta?.last_page > res?.meta?.current_page,
      }));
      return res?.data?.map((product) => ({
        label: product?.translation?.title,
        value: product?.id,
        key: product?.id,
      }));
    });
  };

  const fetchServices = ({ search = '', page = 1 }) => {
    const params = {
      page,
      search: search || undefined,
      perPage: 10,
    };
    return servicesService.getAll(params).then((res) => {
      setHasMore((prev) => ({
        ...prev,
        service: res?.meta?.last_page > res?.meta?.current_page,
      }));
      return res?.data?.map((service) => ({
        label: service?.translation?.title,
        value: service?.id,
        key: service?.id,
      }));
    });
  };

  const onFinish = (values) => {
    setIsSubmitting(true);
    setSubmitError(false);
    const modelTypeValue = values?.modelType?.value;
    const body = {
      model_type: modelTypeValue,
      model_id:
        modelTypeValue === 'shop'
          ? myShop?.id
          : values?.[modelTypeValue]?.value,
      file_urls: image.map((item) => item.name),
      active: !!values.active,
    };
    onSubmit(body)
      .then(() => {
        const nextUrl = 'seller/stories';
        toast.success(t('successfully.saved'));
        batch(() => {
          dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
          dispatch(setRefetch(activeMenu));
        });
        navigate(`/${nextUrl}`);
      })
      .catch(() => {
        setSubmitError(true);
      })
      .finally(() => {
        setIsSubmitting(false);
      });
  };

  useEffect(() => {
    if (id) {
      fetchStory();
    }
  }, [id]);

  useDidUpdate(() => {
    if (activeMenu.refetch && id) {
      fetchStory();
    }
  }, [activeMenu.refetch, id]);

  const renderView = () => {
    switch (modelType?.value) {
      case 'product':
        return (
          <Col xs={24} md={12}>
            <Form.Item
              label={t('product')}
              name='product'
              rules={[{ required: true, message: t('required') }]}
            >
              <InfiniteSelect
                // refetchOnFocus
                hasMore={hasMore.product}
                fetchOptions={fetchProducts}
              />
            </Form.Item>
          </Col>
        );
      case 'service':
        return (
          <Col xs={24} md={12}>
            <Form.Item
              label={t('service')}
              name='service'
              rules={[{ required: true, message: t('required') }]}
            >
              <InfiniteSelect
                // refetchOnFocus
                hasMore={hasMore.service}
                fetchOptions={fetchServices}
              />
            </Form.Item>
          </Col>
        );
    }
  };

  return (
    <Card title={t(id ? 'edit.story' : 'add.story')} loading={isLoading}>
      {loadError ? (
        <Alert
          type='error'
          showIcon
          message={t('something.went.wrong')}
          action={
            <Button onClick={fetchStory} loading={isLoading}>
              {t('retry')}
            </Button>
          }
        />
      ) : (
      <Form
        disabled={!canManage}
        form={form}
        layout='vertical'
        onFinish={onFinish}
        initialValues={{
          modelType: modelTypeOptions[0],
          active: true,
        }}
      >
        <Row gutter={12}>
          <Col xs={24} md={12}>
            <Form.Item
              label={t('type')}
              name='modelType'
              rules={[{ required: true, message: t('required') }]}
            >
              <Select
                labelInValue
                options={modelTypeOptions}
                onChange={() =>
                  form.setFieldsValue({ product: undefined, service: undefined })
                }
              />
            </Form.Item>
          </Col>
          {renderView()}
          <Col xs={24} md={12}>
            <Form.Item label='Active' name='active' valuePropName='checked'>
              <Switch />
            </Form.Item>
            <p style={{ color: '#665c50' }}>
              Stories expire 24 hours after creation. Editing or reactivating does not extend expiry.
              {expiresAt && ` Expires: ${new Date(expiresAt).toLocaleString()}.`}
            </p>
          </Col>
          <Col span={24}>
            <Form.Item
              label={t('image')}
              name={'image'}
              rules={[
                {
                  required: image?.length === 0,
                  message: t('required'),
                },
              ]}
            >
              <StoryMedia
                disabled={!canManage}
                images={image}
                onChange={(next) => {
                  setImage(next);
                  form.setFieldsValue({ image: next });
                }}
                onBusyChange={setUploading}
              />
            </Form.Item>
          </Col>
        </Row>
        {submitError && (
          <Alert
            style={{ marginBottom: 16 }}
            type='error'
            showIcon
            message={t('something.went.wrong')}
          />
        )}
        <div className='d-flex justify-content-end'>
          <Button
            htmlType='submit'
            type='primary'
            loading={isSubmitting}
            disabled={!canManage || isLoading || loadError || uploading || !image.length}
            style={{ minHeight: 44 }}
          >
            {t('save')}
          </Button>
        </div>
      </Form>
      )}
    </Card>
  );
};

export default StoryForm;
