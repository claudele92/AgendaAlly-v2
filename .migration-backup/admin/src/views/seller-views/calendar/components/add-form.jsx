import React, { useContext, useEffect, useMemo, useState } from 'react';
import { Alert, Empty, Menu, Modal, Spin } from 'antd';
import { BookingContext } from '../provider';
import { t } from 'i18next';
import formOptionsRestService from 'services/rest/form-options';
import FormItems from './form-items';
import { getInitialFormOptionId } from 'views/calendar/helpers/calendar-context.mjs';

const AddForm = () => {
  const { isAddForm, setIsAddForm } = useContext(BookingContext);
  const [formList, setFormList] = useState([]);
  const [selectedId, setSelectedId] = useState(null);
  const [isLoading, setIsLoading] = useState(false);
  const [loadError, setLoadError] = useState('');

  const handleCancel = () => {
    setIsAddForm(false);
  };

  const items = formList.map((item) => {
    return {
      key: item?.id,
      label: item?.translation?.title,
    };
  });

  const selectedItem = useMemo(() => {
    const item = formList.find((item) => item.id === selectedId);

    return item;
  }, [selectedId, formList]);

  useEffect(() => {
    if (!isAddForm) return undefined;

    let isCurrentRequest = true;
    setIsLoading(true);
    setLoadError('');
    formOptionsRestService
      .getAll()
      .then(({ data }) => {
        if (!isCurrentRequest) return;
        if (!Array.isArray(data)) {
          throw new Error('The form options response did not contain a list.');
        }
        const availableForms = data.filter(
          (item) => item?.id !== null && item?.id !== undefined,
        );
        setFormList(availableForms);
        setSelectedId(getInitialFormOptionId(availableForms));
      })
      .catch((error) => {
        if (!isCurrentRequest) return;
        setFormList([]);
        setSelectedId(null);
        setLoadError(
          error?.response?.data?.message ||
            error?.message ||
            t('failed.to.load.forms'),
        );
      })
      .finally(() => {
        if (isCurrentRequest) setIsLoading(false);
      });

    return () => {
      isCurrentRequest = false;
    };
  }, [isAddForm]);

  return (
    <Modal
      centered
      width={912}
      footer={null}
      visible={isAddForm}
      onCancel={handleCancel}
      title={t('select.a.form')}
    >
      <Spin spinning={isLoading}>
        {loadError ? (
          <Alert
            type='error'
            showIcon
            message={t('failed.to.load.forms')}
            description={loadError}
          />
        ) : formList.length === 0 && !isLoading ? (
          <Empty description={t('no.forms.available')} />
        ) : (
          <div className='d-flex gap-2'>
            <Menu
              style={{
                width: 256,
              }}
              mode='vertical'
              items={items}
              onClick={({ key }) => setSelectedId(Number(key))}
            />
            {selectedItem && <FormItems item={selectedItem} formList={formList} />}
          </div>
        )}
      </Spin>
    </Modal>
  );
};

export default AddForm;
