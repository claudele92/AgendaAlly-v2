import { cloneElement, forwardRef, isValidElement, useReducer } from 'react';
import { HiOutlineSparkles } from 'react-icons/hi2';
import cls from './ai-translation.module.scss';
import { reducer, initialState, AI_TRANSLATION_ACTIONS } from './utils';
import { AiActionFeedback } from './ai-loading';
import { aiTranslationService as adminAiTranslationService } from 'services/ai-translations';
import { aiTranslationService as sellerAiTranslationService } from 'services/seller/ai-translations';
import { Form, Spin } from 'antd';
import { shallowEqual, useSelector } from 'react-redux';
import { toast } from 'react-toastify';
import { useTranslation } from 'react-i18next';

const ReactAppIsDemo = import.meta.env.VITE_IS_DEMO;

function AiTranslation(
  {
    model,
    children,
    staticTranslateTo,
    fieldsMap = { title: 'title', description: 'description' },
    isTitleField,
    ...props
  },
  ref,
) {
  const { t } = useTranslation();
  const form = Form.useFormInstance();
  const defaultLang = useSelector((state) => state.formLang.defaultLang);
  const role = useSelector((state) => state.auth?.user?.role, shallowEqual);
  const activeInGeneralSettings =
    Number(
      useSelector(
        (state) => state.globalSettings?.settings?.ai_active,
        shallowEqual,
      ),
    ) === 1;
  const activeInShopSettings = useSelector(
    (state) => state.myShop?.myShop?.ai_access,
    shallowEqual,
  );

  const [state, dispatch] = useReducer(reducer, initialState);

  const aiTranslationService =
    role === 'seller' ? sellerAiTranslationService : adminAiTranslationService;

  const handleSuccess = () => {
    dispatch(AI_TRANSLATION_ACTIONS.setResult('success'));
    setTimeout(() => dispatch(AI_TRANSLATION_ACTIONS.setResult(null)), 1000);
  };

  const handleFail = () => {
    dispatch(AI_TRANSLATION_ACTIONS.setResult('error'));
    setTimeout(() => dispatch(AI_TRANSLATION_ACTIONS.setResult(null)), 1000);
  };

  const sendAiRequest = async (contentName, contentText) => {
    return aiTranslationService.translate({
      model_id: model.id,
      model_type: model.type,
      content: contentText,
      lang: defaultLang,
    });
  };

  const handleTranslate = async () => {
    // if (ReactAppIsDemo === 'true') {
    //   return toast.warning(t('ai.feature.disabled.in.demo'), {
    //     toastId: 'ai-feature-disabled',
    //   });
    // }
    dispatch(AI_TRANSLATION_ACTIONS.setLoading(true));
    try {
      const contentName = props.id?.split('[')[0];
      const contentSource = isTitleField ? contentName : fieldsMap.title; // take only title for description generation
      const contentText = form.getFieldValue(
        contentSource + `[${defaultLang}]`,
      );
      const res = await sendAiRequest(contentName, contentText);
      const payload = {};
      Object.keys(res.data).forEach((locale) => {
        payload[`${contentName}[${locale}]`] =
          res?.data[locale]?.[fieldsMap[contentName]];
        if (isTitleField) {
          const hasDescriptionFieldValue = form.getFieldValue(
            `${fieldsMap.description}[${locale}]`,
          );
          if (!hasDescriptionFieldValue) {
            payload[`${fieldsMap.description}[${locale}]`] =
              res?.data[locale]?.description;
          }
        }
      });
      form.setFieldsValue(payload);

      handleSuccess();
    } catch (e) {
      handleFail();
      console.log(e);
    } finally {
      dispatch(AI_TRANSLATION_ACTIONS.setLoading(false));
    }
  };

  if (!activeInGeneralSettings) {
    return isValidElement(children)
      ? cloneElement(children, { ...props, ref })
      : null;
  }

  return (
    <div className={cls.root}>
      <div className={cls.panelContainer}>
        <div className={cls.panel}>
          {state.isLoading ? (
            <Spin size='small' />
          ) : state.result ? (
            <AiActionFeedback type={state.result} />
          ) : (
            <button
              onClick={handleTranslate}
              className={cls.translateBtn}
              type='button'
            >
              <HiOutlineSparkles size={15} className={cls.icon} />
              <span>Generate</span>
            </button>
          )}
        </div>
      </div>
      {isValidElement(children)
        ? cloneElement(children, { ...props, ref })
        : null}
    </div>
  );
}

export default forwardRef(AiTranslation);
