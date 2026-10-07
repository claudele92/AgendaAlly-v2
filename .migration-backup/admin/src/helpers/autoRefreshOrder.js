import { store } from 'redux/store';
import { DEFAULT_ORDER_TIMEOUT } from '../constants';

const MAX_SAFE_TIMEOUT = 2147483.647;

export default function autoRefreshOrder(cb) {
  let duration = (() => {
    const valueFromSettings = Number(
      store.getState()?.globalSettings?.settings?.order_refresh_timeout,
    );
    const isValidNumber = !isNaN(valueFromSettings);

    if (isValidNumber) {
      return valueFromSettings;
    } else {
      return DEFAULT_ORDER_TIMEOUT;
    }
  })();

  if (!duration) return null;
  if (duration > MAX_SAFE_TIMEOUT) {
    duration = MAX_SAFE_TIMEOUT;
  }

  return setInterval(
    () => {
      cb();
    },
    Number(duration) * 1000,
  );
}
