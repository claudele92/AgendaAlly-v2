import React, { useState } from 'react';
import { Button, Card, Col, Form, Row } from 'antd';
import { useTranslation } from 'react-i18next';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom';
import { toast } from 'react-toastify';
import { removeFromMenu } from 'redux/slices/menu';
import countryAdminService from 'services/countryAdmin';
import countryService from 'services/deliveryzone/country';
import { InfiniteSelect } from 'components/infinite-select';
import { SuperAdminRoute } from 'context/superadmin-route';

export default function CountryAdminAdd() {
  const { t } = useTranslation();
  const [form] = Form.useForm();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [loadingBtn, setLoadingBtn] = useState(false);

  const fetchUser = ({ search, page }) =>
    countryAdminService
      .searchUser({ search: !!search?.length ? search : undefined, page })
      .then((res) =>
        res.data.map((user) => ({
          label:
            `${user.firstname || ''} ${user.lastname || ''}`.trim() ||
            user.email ||
            user.phone,
          value: user.id,
        })),
      );

  const fetchCountry = ({ search, page }) =>
    countryService
      .get({ search: !!search?.length ? search : undefined, page })
      .then((res) =>
        res.data.map((country) => ({
          label: country?.translation?.title,
          value: country.id,
        })),
      );

  const onFinish = (values) => {
    setLoadingBtn(true);
    countryAdminService
      .create({
        user_id: values?.user_id?.value,
        country_id: values?.country_id?.value,
      })
      .then(() => {
        toast.success(t('successfully.created'));
        const nextUrl = 'country-admins';
        dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
        navigate(`/${nextUrl}`);
      })
      .catch((err) => {
        toast.error(
          err?.response?.data?.params?.user_id?.[0] ||
            err?.response?.data?.message,
        );
      })
      .finally(() => setLoadingBtn(false));
  };

  return (
    <SuperAdminRoute>
      <Card title={t('add.country.admin')} className='h-100'>
        <Form layout='vertical' form={form} onFinish={onFinish}>
          <Row gutter={12}>
            <Col span={12}>
              <Form.Item
                name='user_id'
                label={t('user')}
                rules={[{ required: true, message: t('required') }]}
              >
                <InfiniteSelect fetchOptions={fetchUser} />
              </Form.Item>
            </Col>
            <Col span={12}>
              <Form.Item
                name='country_id'
                label={t('country_id')}
                rules={[{ required: true, message: t('required') }]}
              >
                <InfiniteSelect fetchOptions={fetchCountry} />
              </Form.Item>
            </Col>
          </Row>
          <Button type='primary' htmlType='submit' loading={loadingBtn}>
            {t('submit')}
          </Button>
        </Form>
      </Card>
    </SuperAdminRoute>
  );
}
