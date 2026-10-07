import { Button, Card, Col, Form, Row } from 'antd';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useTranslation } from 'react-i18next';
import { useEffect, useState } from 'react';
import MediaUpload from 'components/upload';
import settingService from 'services/settings';
import { toast } from 'react-toastify';
import { fetchSettings } from 'redux/slices/globalSettings';
import createImage from 'helpers/createImage';

const DeliverymanSettings = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const [form] = Form.useForm();
  const { settings } = useSelector(
    (state) => state.globalSettings,
    shallowEqual,
  );

  const [loadingBtn, setLoadingBtn] = useState(false);
  const [images, setImages] = useState({
    first: [],
    second: [],
    third: [],
  });

  const onFinish = () => {
    setLoadingBtn(true);
    const body = {
      driver_story_image_1: images.first?.[0]?.name,
      driver_story_image_2: images.second?.[0]?.name,
      driver_story_image_3: images.third?.[0]?.name,
    };
    settingService
      .update(body)
      .then(() => {
        toast.success(t('successfully.updated'));
        dispatch(fetchSettings());
      })
      .finally(() => {
        setLoadingBtn(false);
      });
  };

  useEffect(() => {
    if (settings?.driver_story_image_1) {
      setImages((prev) => ({
        ...prev,
        first: [createImage(settings?.driver_story_image_1)],
      }));
    }
    if (settings?.driver_story_image_2) {
      setImages((prev) => ({
        ...prev,
        second: [createImage(settings?.driver_story_image_2)],
      }));
    }
    if (settings?.driver_story_image_3) {
      setImages((prev) => ({
        ...prev,
        third: [createImage(settings?.driver_story_image_3)],
      }));
    }
  }, [settings]);

  return (
    <Card>
      <h1 style={{ fontSize: '24px' }}>{t('deliveryman.story.images')}</h1>
      <p style={{ fontSize: '16px', marginBottom: '50px' }}>
        {t('deliveryman.story.images.recommended.size')}
      </p>
      <Form form={form} layout='vertical' onFinish={onFinish}>
        <Row gutter={12}>
          <Col span={8}>
            <Form.Item
              name='driver_story_image_1'
              label={t('deliveryman.story.1')}
              rules={[
                { required: !images.first?.length, message: t('required') },
              ]}
            >
              <MediaUpload
                form={form}
                type='deliveryman'
                name='driver_story_image_1'
                imageList={images.first}
                setImageList={(image) => {
                  setImages((prev) => ({
                    ...prev,
                    first: image,
                  }));
                }}
                multiple={false}
              />
            </Form.Item>
          </Col>
          <Col span={8}>
            <Form.Item
              name='driver_story_image_2'
              label={t('deliveryman.story.2')}
              rules={[
                { required: !images.second?.length, message: t('required') },
              ]}
            >
              <MediaUpload
                form={form}
                type='deliveryman'
                name='driver_story_image_2'
                imageList={images.second}
                setImageList={(image) => {
                  setImages((prev) => ({
                    ...prev,
                    second: image,
                  }));
                }}
                multiple={false}
              />
            </Form.Item>
          </Col>
          <Col span={8}>
            <Form.Item
              name='driver_story_image_3'
              label={t('deliveryman.story.3')}
              rules={[
                { required: !images.third?.length, message: t('required') },
              ]}
            >
              <MediaUpload
                form={form}
                name='driver_story_image_3'
                type='deliveryman'
                imageList={images.third}
                setImageList={(image) => {
                  setImages((prev) => ({
                    ...prev,
                    third: image,
                  }));
                }}
                multiple={false}
              />
            </Form.Item>
          </Col>
        </Row>
        <Button
          type='primary'
          htmlType='submit'
          style={{ marginTop: '50px' }}
          loading={loadingBtn}
        >
          {t('submit')}
        </Button>
      </Form>
    </Card>
  );
};

export default DeliverymanSettings;
