import React, { useState } from 'react';
import { Form, Input, Switch } from 'antd';
import { useTranslation } from 'react-i18next';
import { CKEditor } from '@ckeditor/ckeditor5-react';
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import { IMG_URL } from '../../configs/app-global';
import galleryService from '../../services/gallery';

export default function TextEditor({ form, lang, languages, accountTemplate = false }) {
  const { t } = useTranslation();
  const [sourceMode, setSourceMode] = useState(true);

  function uploadAdapter(loader) {
    return {
      upload: () => {
        return new Promise((resolve, reject) => {
          const formData = new FormData();
          loader.file.then((file) => {
            formData.append('image', file);
            formData.append('type', 'blogs');
            galleryService
              .upload(formData)
              .then(({ data }) => {
                resolve({
                  default: `${IMG_URL + data.title}`,
                });
              })
              .catch((err) => {
                reject(err);
              });
          });
        });
      },
    };
  }

  function uploadPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
      return uploadAdapter(loader);
    };
  }

  const handleChange = (e, editor) => {
    const data = editor.getData();
  };

  return (
    <div className='textEditor'>
      {accountTemplate && (
        <div style={{ marginBottom: 8 }}>
          <Switch
            checked={sourceMode}
            onChange={setSourceMode}
            checkedChildren='Source'
            unCheckedChildren='Rich text'
          />{' '}
          Source preserves the exact stored wording. Rich text may normalize HTML.
        </div>
      )}
      {accountTemplate && sourceMode ? (
        <Form.Item label='Body content' name='body' rules={[{ required: true, message: t('required') }]}>
          <Input.TextArea autoSize={{ minRows: 5, maxRows: 18 }} />
        </Form.Item>
      ) : (
      <Form.Item
        label={t('newsletter.content')}
        name={'body'}
        valuePropName='data'
        getValueFromEvent={(event, editor) => {
          const data = editor.getData();
          return data;
        }}
        rules={[
          {
            required: true,
            message: t('required'),
          },
        ]}
        className='description-editor'
      >
        <CKEditor
          editor={ClassicEditor}
          config={{
            extraPlugins: accountTemplate ? [] : [uploadPlugin],
            ...(accountTemplate ? {
              removePlugins: ['ImageUpload', 'MediaEmbed', 'Link'],
              toolbar: ['heading', '|', 'bold', 'italic', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', 'undo', 'redo'],
            } : {}),
          }}
          onChange={handleChange}
          onBlur={(event, editor) => {
            const data = editor.getData();
            form.setFieldsValue({
              body: data,
            });
          }}
        />
      </Form.Item>
      )}
    </div>
  );
}
