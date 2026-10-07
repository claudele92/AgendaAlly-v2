import { FaRegCircleCheck, FaRegCircleXmark } from 'react-icons/fa6';
import styles from './ai-loading.module.scss';
import { useTranslation } from 'react-i18next';

export default function AiLoading() {
  const { t } = useTranslation();
  return (
    <div className={styles.loadingOverlay}>
      <div className={styles.spinner}>
        <div className={styles.spinnerCore}></div>
        <div className={styles.spinnerRing}></div>
      </div>
      <span className={styles.loadingText}>{t('translating')}</span>
    </div>
  );
}

/**
 * AiActionFeedback component displays feedback based on the action type.
 * @param {Object} props - Component properties.
 * @param {'success' | 'error'} props.type - Type of action ('success', 'error', 'success' is default).
 * @returns {JSX.Element} Feedback message with appropriate icon and style.
 */

export const AiActionFeedback = ({ type }) => {
  switch (type) {
    case 'success':
      return (
        <div style={{ color: '#84CC16' }} className={styles.loadingOverlay}>
          <FaRegCircleCheck size={20} />
          Completed
        </div>
      );
    case 'error':
      return (
        <div style={{ color: '#DC2626' }} className={styles.loadingOverlay}>
          <FaRegCircleXmark size={20} />
          <span className={styles.errorText}>Error</span>
        </div>
      );
    default:
      return (
        <div style={{ color: '#84CC16' }} className={styles.loadingOverlay}>
          <FaRegCircleCheck size={20} />
          Completed
        </div>
      );
  }
};
