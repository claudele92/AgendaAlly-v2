import { t } from 'i18next';
import { useContext } from 'react';
import { BookingContext } from '../provider';
import { Button } from 'antd';

const UpdateServiceFooter = () => {
  const { serviceForm } = useContext(BookingContext);
  return (
    <div className='d-flex gap-2'>
      <Button type='primary' className='w-100' onClick={serviceForm.submit}>
        {t('apply')}
      </Button>
    </div>
  );
};

export default UpdateServiceFooter;
