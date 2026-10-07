import { store } from 'redux/store';
import { FALLBACK_HOUR_FORMAT } from '../constants';

export const getHourFormat = () =>
  store.getState()?.globalSettings?.settings?.hour_format ??
  FALLBACK_HOUR_FORMAT;
