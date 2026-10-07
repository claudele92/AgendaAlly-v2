import { createAsyncThunk } from '@reduxjs/toolkit';
import currencyService from '../../services/currency';
import restCurrencyService from '../../services/rest/currency';
import currencyReducer, {
  resetCurrency,
  setCurrencySession,
} from './currency-state.mjs';

export const fetchCurrencies = createAsyncThunk(
  'currency/fetchCurrencies',
  (params = {}) => {
    return currencyService.getAll(params).then((res) => res);
  }
);
export const fetchRestCurrencies = createAsyncThunk(
  'currency/fetchRestCurrencies',
  (params = {}) => {
    return restCurrencyService.getAll(params).then((res) => res);
  }
);

export { resetCurrency, setCurrencySession };
export default currencyReducer;
