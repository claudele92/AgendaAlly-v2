import React, { useState } from 'react';
import { Alert, Button, Image, Space, Upload } from 'antd';
import { UploadOutlined } from '@ant-design/icons';
import storeisService from 'services/seller/storeis';
import createImage from 'helpers/createImage';

// Story uploads use the seller's shop-owned namespace, not the shared gallery.
export default function StoryMedia({ images, onChange, onBusyChange, disabled }) {
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState(false);
  const upload = async ({ file, onSuccess, onError }) => {
    setBusy(true);
    onBusyChange(true);
    setError(false);
    const body = new FormData();
    body.append('files[0]', file);
    try {
      const result = await storeisService.upload(body);
      onChange([...images, ...result.data.map(createImage)]);
      onSuccess(result);
    } catch (failure) {
      setError(true);
      onError(failure);
    } finally {
      setBusy(false);
      onBusyChange(false);
    }
  };
  return (
    <div>
      <Space wrap align='start'>
        {images.map((image, index) => (
          <div key={`${image.name}-${index}`}>
            <Image src={image.name} alt='Story media' width={128} height={160} style={{ objectFit: 'cover', borderRadius: 8 }} />
            <Button
              disabled={busy || disabled}
              onClick={() => onChange(images.filter((_, i) => i !== index))}
              aria-label={`Remove Story image ${index + 1}`}
              style={{ display: 'block', minHeight: 44 }}
            >
              Remove image
            </Button>
          </div>
        ))}
      </Space>
      <Upload
        accept='.jpg,.jpeg,.png,.webp'
        customRequest={upload}
        showUploadList={false}
        disabled={busy || disabled}
      >
        <Button icon={<UploadOutlined />} disabled={disabled} loading={busy} style={{ marginTop: 12, minHeight: 44 }}>
          Upload Story image
        </Button>
      </Upload>
      {error && <Alert type='error' showIcon message='Story image upload failed. Please try again.' style={{ marginTop: 12 }} />}
    </div>
  );
}