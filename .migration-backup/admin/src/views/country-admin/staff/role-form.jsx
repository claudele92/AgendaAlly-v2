import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate, useParams } from 'react-router-dom';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { Alert, Button, Card, Form, Input, Spin } from 'antd';
import { toast } from 'react-toastify';
import { removeFromMenu } from 'redux/slices/menu';
import countryRoleService from 'services/countryRole';
import PermissionPicker from 'components/staff/permission-picker';

export default function RoleForm({ isEdit, countryId }) {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const navigate = useNavigate();
  const { id } = useParams();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [form] = Form.useForm();

  const [catalog, setCatalog] = useState([]);
  const [permissionIds, setPermissionIds] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loadError, setLoadError] = useState(null);
  const [loadingBtn, setLoadingBtn] = useState(false);
  const [error, setError] = useState(null);

  const loadData = () => {
    setLoading(true);
    setLoadError(null);

    const requests = [countryRoleService.getPermissions()];

    if (isEdit) {
      requests.push(
        countryRoleService.getById(
          id,
          countryId ? { country_id: countryId } : {},
        ),
      );
    }

    Promise.all(requests)
      .then(([permissionsRes, roleRes]) => {
        setCatalog(permissionsRes.data || []);

        if (roleRes) {
          form.setFieldsValue({ name: roleRes.data.name });
          setPermissionIds(
            (roleRes.data.permissions || []).map((permission) => permission.id),
          );
        }
      })
      .catch((err) =>
        setLoadError(err.response?.data?.message || t('failed.to.load.data')),
      )
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadData();
    // eslint-disable-next-line
  }, [id]);

  const onFinish = (values) => {
    if (!permissionIds.length) {
      setError({ permission_ids: [t('select.at.least.one.permission')] });
      return;
    }

    const body = {
      name: values.name,
      permission_ids: permissionIds,
      ...(countryId ? { country_id: countryId } : {}),
    };
    const nextUrl = 'country-admin/staff';
    setLoadingBtn(true);
    setError(null);

    const request = isEdit
      ? countryRoleService.update(id, body)
      : countryRoleService.create(body);

    request
      .then(() => {
        toast.success(
          t(isEdit ? 'successfully.updated' : 'successfully.created'),
        );
        dispatch(removeFromMenu({ ...activeMenu, nextUrl }));
        navigate(`/${nextUrl}`);
      })
      .catch((err) => setError(err.response?.data?.params))
      .finally(() => setLoadingBtn(false));
  };

  if (loading) {
    return (
      <Card>
        <div className='d-flex justify-content-center p-5'>
          <Spin size='large' />
        </div>
      </Card>
    );
  }

  if (loadError) {
    return (
      <Card title={t(isEdit ? 'edit.role' : 'add.role')}>
        <Alert
          type='error'
          showIcon
          message={t('failed.to.load.permissions.catalog')}
          description={loadError}
        />
        <Button type='primary' className='mt-3' onClick={loadData}>
          {t('retry')}
        </Button>
      </Card>
    );
  }

  return (
    <Card title={t(isEdit ? 'edit.role' : 'add.role')}>
      <Form form={form} layout='vertical' onFinish={onFinish}>
        <Form.Item
          label={t('name')}
          name='name'
          rules={[{ required: true, message: t('required') }]}
          validateStatus={error?.name ? 'error' : undefined}
          help={error?.name?.[0]}
        >
          <Input placeholder={t('e.g.receptionist')} />
        </Form.Item>

        <Form.Item
          label={t('permissions')}
          validateStatus={error?.permission_ids ? 'error' : undefined}
          help={error?.permission_ids?.[0]}
        >
          <PermissionPicker
            catalog={catalog}
            value={permissionIds}
            onChange={setPermissionIds}
          />
        </Form.Item>

        <Button
          type='primary'
          htmlType='submit'
          loading={loadingBtn}
          className='mt-3'
        >
          {t('submit')}
        </Button>
      </Form>
    </Card>
  );
}
