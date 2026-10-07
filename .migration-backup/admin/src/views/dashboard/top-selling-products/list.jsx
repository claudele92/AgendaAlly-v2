import cls from './list.module.scss';
import { useTranslation } from 'react-i18next';
import ColumnImage from 'components/column-image';
import RiveResult from 'components/rive-result';
import Loading from 'components/loading';
import { Alert } from 'antd';

const TopSellingProductsList = ({ data, loading, error }) => {
  const { t } = useTranslation();
  if (loading) {
    return (
      <div role='status'>
        <Loading size='small' />
      </div>
    );
  }
  if (error) {
    return (
      <Alert
        message={t('top.selling.products.load.failed')}
        showIcon
        type='error'
        role='alert'
      />
    );
  }
  if (!data?.length) {
    return <RiveResult id='noproductsfound' />;
  }
  return (
    <div className={cls.container}>
      {data?.map((item) => (
        <div key={item?.id} className={cls.item}>
          <div className={cls.main}>
            <ColumnImage
              image={item?.img}
              size={57}
              preview={false}
              id={item?.id}
              borderColor='#fff'
            />
            <span className={cls.title}>{item?.title || t('N/A')}</span>
          </div>
          <div className={cls.sales}>
            {t('sales')}: {item?.count || 0}
          </div>
        </div>
      ))}
    </div>
  );
};

export default TopSellingProductsList;
