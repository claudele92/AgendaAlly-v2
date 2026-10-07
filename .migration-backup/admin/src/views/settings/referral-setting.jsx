import {
  Button,
  Col,
  DatePicker,
  Divider,
  Form,
  Input,
  InputNumber,
  Row,
  Space,
  Typography,
  Card,
} from 'antd';
import TextArea from 'antd/lib/input/TextArea';
import moment from 'moment';
import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { toast } from 'react-toastify';
import { disableRefetch, setMenuData } from 'redux/slices/menu';
import referralService from 'services/referral';
import { fetchSettings as getSettings } from 'redux/slices/globalSettings';
import LanguageList from 'components/language-list';
import MediaUpload from 'components/upload';
import getLanguageFields from 'helpers/getLanguageFields';
import useDidUpdate from 'helpers/useDidUpdate';
import getTranslationFields from 'helpers/getTranslationFields';
import { MODELS } from '../../constants';
import AiTranslation from '../../components/ai-translation/ai-translation';

const ReferalSetting = () => {
  const { t } = useTranslation();
  const [form] = Form.useForm();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const dispatch = useDispatch();

  const { settings } = useSelector((state) => state.globalSettings);
  const referral = Number(settings.referral_active);
  const [referralData, setReferralData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const { defaultLang, languages } = useSelector(
    (state) => state.formLang,
    shallowEqual,
  );
  const [image, setImage] = useState(
    activeMenu.data?.logo_img ? [activeMenu.data?.logo_img] : [],
  );

  useEffect(() => {
    return () => {
      const data = form.getFieldsValue(true);
      data.expired_at = JSON.stringify(data?.expired_at);
      dispatch(setMenuData({ activeMenu, data: data }));
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  function fetchSettings() {
    setLoading(true);
    referralService
      .get()
      .then((res) => {
        const data = res?.data?.[0];
        setReferralData(data);
      })
      .finally(() => {
        setLoading(false);
        dispatch(disableRefetch(activeMenu));
      });
  }

  const createImages = (items) => {
    return {
      items,
      uid: items,
      url: items,
      name: items,
    };
  };

  const onFinish = (values) => {
    const data = {
      title: getTranslationFields(languages, values, 'title'),
      description: getTranslationFields(languages, values, 'description'),
      faq: getTranslationFields(languages, values, 'faq'),
      price_from: values?.price_from,
      price_to: values?.price_to,
      expired_at: moment(values?.expired_at).format('YYYY-MM-DD'),
      img: image?.[0]?.name,
    };
    setLoadingBtn(true);

    referralService
      .update(data)
      .then(() => {
        toast.success(t('successfully.updated'));
        dispatch(getSettings());
      })
      .finally(() => setLoadingBtn(false));
  };

  const getInitialTimes = () => {
    if (!activeMenu.data?.expired_at) {
      return {};
    }
    const data = JSON.parse(activeMenu.data?.expired_at);
    return {
      expired_at: moment(data, 'HH:mm:ss'),
    };
  };

  useEffect(() => {
    fetchSettings();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useDidUpdate(() => {
    if (activeMenu.refetch) {
      fetchSettings();
    }
  }, [activeMenu.refetch]);

  useDidUpdate(() => {
    if (languages && referralData) {
      const result = {
        ...getLanguageFields(languages, referralData, [
          'title',
          'description',
          'faq',
        ]),
        price_from: referralData?.price_from,
        price_to: referralData?.price_to,
        expired_at: referralData?.expired_at
          ? moment(referralData.expired_at)
          : undefined,
        active: !!referralData?.active,
        image: [createImages(referralData?.img)],
      };
      form.setFieldsValue(result);
      setImage([createImages(referralData?.img)]);
    }
  }, [languages, referralData]);

  return (
    <Card loading={loading}>
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
          {t('referral.settings')}
        </Typography.Title>
        <Space>
          <LanguageList />
          <Button
            type='primary'
            onClick={() => form.submit()}
            loading={loadingBtn}
            disabled={referral !== 1}
          >
            {t('save')}
          </Button>
        </Space>
      </Space>
      <Divider color='var(--divider)' />
      <Form
        layout='vertical'
        initialValues={{
          ...activeMenu.data,
          active: true,
          ...getInitialTimes(),
        }}
        form={form}
        onFinish={onFinish}
      >
        <Row gutter={24}>
          <Col span={24}>
            {referral !== 1 && (
              <h3 className='text-center mt-2 mb-4'>
                {t('no.active.referral')}
              </h3>
            )}
          </Col>
          <Col span={12}>
            {languages.map((item, idx) => (
              <Form.Item
                key={'title' + idx}
                label={t('title')}
                name={`title[${item.locale}]`}
                rules={[
                  {
                    validator(_, value) {
                      if (!value && item?.locale === defaultLang) {
                        return Promise.reject(new Error(t('required')));
                      } else if (value && value?.trim() === '') {
                        return Promise.reject(new Error(t('no.empty.space')));
                      } else if (value && value?.trim().length < 2) {
                        return Promise.reject(
                          new Error(t('must.be.at.least.2')),
                        );
                      }
                      return Promise.resolve();
                    },
                  },
                ]}
                hidden={item.locale !== defaultLang}
              >
                <AiTranslation
                  model={{
                    type: MODELS.Referral,
                    id: activeMenu.data?.id,
                  }}
                  isTitleField
                >
                  <Input disabled={referral !== 1} />
                </AiTranslation>
              </Form.Item>
            ))}
          </Col>
          <Col span={12}>
            {languages.map((item, idx) => (
              <Form.Item
                key={'description' + idx}
                label={t('description')}
                name={`description[${item.locale}]`}
                rules={[
                  {
                    validator(_, value) {
                      if (!value && item?.locale === defaultLang) {
                        return Promise.reject(new Error(t('required')));
                      } else if (value && value?.trim() === '') {
                        return Promise.reject(new Error(t('no.empty.space')));
                      } else if (value && value?.trim().length < 5) {
                        return Promise.reject(
                          new Error(t('must.be.at.least.5')),
                        );
                      }
                      return Promise.resolve();
                    },
                  },
                ]}
                hidden={item.locale !== defaultLang}
              >
                <AiTranslation
                  model={{
                    type: MODELS.Referral,
                    id: activeMenu.data?.id,
                  }}
                >
                  <TextArea rows={3} disabled={referral !== 1} />
                </AiTranslation>
              </Form.Item>
            ))}
          </Col>
          <Col span={12}>
            {languages.map((item, idx) => (
              <Form.Item
                key={'terms' + idx}
                label={t('terms')}
                name={`faq[${item.locale}]`}
                rules={[
                  {
                    validator(_, value) {
                      if (!value && item?.locale === defaultLang) {
                        return Promise.reject(new Error(t('required')));
                      } else if (value && value?.trim() === '') {
                        return Promise.reject(new Error(t('no.empty.space')));
                      } else if (value && value?.trim().length < 2) {
                        return Promise.reject(
                          new Error(t('must.be.at.least.2')),
                        );
                      }
                      return Promise.resolve();
                    },
                  },
                ]}
                hidden={item.locale !== defaultLang}
              >
                <TextArea rows={3} disabled={referral !== 1} />
              </Form.Item>
            ))}
          </Col>
          <Col span={12}>
            <Form.Item
              name='price_from'
              label={t('sender.price')}
              rules={[
                { required: true, message: t('required') },
                { type: 'number', min: 0, message: t('must.be.positive') },
              ]}
            >
              <InputNumber className='w-100' disabled={referral !== 1} />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item
              name='price_to'
              label={t('distribution.price')}
              rules={[
                { required: true, message: t('required') },
                { type: 'number', min: 0, message: t('must.be.positive') },
              ]}
            >
              <InputNumber className='w-100' disabled={referral !== 1} />
            </Form.Item>
          </Col>
          <Col span={12}>
            <Form.Item
              name='expired_at'
              label={t('expired.at')}
              rules={[{ required: true, message: t('required') }]}
            >
              <DatePicker
                className='w-100'
                disabledDate={(current) => moment().add(-1, 'days') >= current}
                disabled={referral !== 1}
              />
            </Form.Item>
          </Col>
          <Col span={12} disabled={referral !== 1}>
            <Form.Item
              label={t('image')}
              disabled={referral !== 1}
              name='images'
              rules={[
                {
                  required: image.length === 0,
                  message: t('required'),
                },
              ]}
            >
              <MediaUpload
                type='referral'
                imageList={image}
                setImageList={setImage}
                form={form}
                multiple={false}
                name='referral'
                disabled={referral !== 1}
              />
            </Form.Item>
          </Col>
        </Row>
      </Form>
    </Card>
  );
};

export default ReferalSetting;
