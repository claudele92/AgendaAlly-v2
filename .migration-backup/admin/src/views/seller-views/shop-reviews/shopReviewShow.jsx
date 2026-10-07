import React, { useEffect, useState } from 'react';
import { Button, Descriptions, Form, Image, Modal, Rate } from 'antd';
import { useTranslation } from 'react-i18next';
import reviewService from 'services/seller/review';
import moment from 'moment';
import { EditOutlined } from '@ant-design/icons';
import { toast } from 'react-toastify';
import Loading from 'components/loading';
import { getHourFormat } from '../../../helpers/getHourFormat';

export default function OrderReviewShowModal({ id, handleCancel }) {
  const [data, setData] = useState({});
  const [loading, setLoading] = useState(false);
  const { t } = useTranslation();
  const [form] = Form.useForm();
  const [isReplySending, setIsReplySending] = useState(false);
  const hourFormat = getHourFormat();

  const comment = data?.comment?.split(',');
  const name = comment?.at(0);
  const phone = comment?.at(1);
  const description = comment?.at(2);

  function fetchReviews(id) {
    setLoading(true);
    reviewService
      .getById(id)
      .then((res) => {
        setData(res.data);
        form.setFieldsValue({
          reply: res.data.answer,
        });
      })
      .finally(() => setLoading(false));
  }

  const handleReplySubmit = ({ reply }) => {
    setIsReplySending(true);
    reviewService
      .createOrUpdateAnswer(id, { answer: reply })
      .then(() => {
        fetchReviews(id);
        toast.success(t('reply.sent'));
      })
      .finally(() => setIsReplySending(false));
  };

  useEffect(() => {
    fetchReviews(id);
  }, [id]);

  return (
    <Modal
      visible={!!id}
      title={t('shop.review')}
      onCancel={handleCancel}
      footer={
        <Button type='default' onClick={handleCancel}>
          {t('close')}
        </Button>
      }
    >
      {!loading ? (
        <div>
          <Descriptions bordered>
            <Descriptions.Item span={3} label={t('id')}>
              {data.id}
            </Descriptions.Item>
            <Descriptions.Item span={3} label={t('user')}>
              {data?.user?.firstname + data?.user?.lastname}
            </Descriptions.Item>
            <Descriptions.Item span={3} label={t('rating')}>
              <Rate disabled defaultValue={data?.rating ?? 0} />
            </Descriptions.Item>
            <Descriptions.Item span={3} label={t('phone.number')}>
              {data?.user?.phone}
            </Descriptions.Item>
            <Descriptions.Item span={3} label={t('comment')}>
              {data?.comment}
            </Descriptions.Item>
            <Descriptions.Item span={3} label={t('created.at')}>
              {moment(data.created_at).format(`YYYY-MM-DD ${hourFormat}`)}
            </Descriptions.Item>
            {!!data?.galleries?.length && (
              <Descriptions.Item span={3} label={t('images')}>
                <div style={{ display: 'flex', gap: '10px' }}>
                  {data?.galleries?.map((item) => (
                    <div>
                      <Image
                        width={50}
                        height={50}
                        src={item?.path || ''}
                        alt='img'
                        preview
                      />
                    </div>
                  ))}
                </div>
              </Descriptions.Item>
            )}
          </Descriptions>

          <div className='mt-3'>
            <Form onFinish={handleReplySubmit} form={form} layout='vertical'>
              <Form.Item
                rules={[{ required: true, message: t('required') }]}
                name='reply'
                label={t('your.reply')}
              >
                <textarea
                  className='ant-input'
                  placeholder={t('reply')}
                  value={description}
                  maxLength={255}
                />
              </Form.Item>
              <Button
                htmlType='submit'
                type='primary'
                className='w-100'
                icon={<EditOutlined />}
                loading={isReplySending}
              >
                {t('send.reply')}
              </Button>
            </Form>
          </div>
        </div>
      ) : (
        <Loading />
      )}
    </Modal>
  );
}
