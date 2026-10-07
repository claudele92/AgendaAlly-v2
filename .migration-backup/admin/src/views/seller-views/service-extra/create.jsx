import { Card, Form } from 'antd';
import { useTranslation } from 'react-i18next';
import LanguageList from 'components/language-list';
import sellerServiceExtraService from 'services/seller/service-extra';
import SellerServiceExtraForm from './form';

function CreateSellerServiceExtra() {
  const { t } = useTranslation();
  const [form] = Form.useForm();

  const onSubmit = (body) => {
    return sellerServiceExtraService.create(body);
  };
  return (
    <Card title={t('create.service.extra')} extra={<LanguageList />}>
      <SellerServiceExtraForm form={form} onSubmit={onSubmit} />
    </Card>
  );
}

export default CreateSellerServiceExtra;
