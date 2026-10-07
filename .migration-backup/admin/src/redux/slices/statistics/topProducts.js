import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import statisticService from '../../../services/statistics';
import sellerStatisticService from '../../../services/seller/statistics';
import {
  normalizeTopProductsResponse,
  topProductsFailureState,
} from './topProductsResponse.mjs';

const initialState = {
  loading: false,
  topProducts: [],
  error: '',
  params: {
    time: 'subWeek',
    perPage: 5,
  },
  filterTimeList: ['subWeek', 'subMonth', 'subYear'],
};

export const fetchTopProducts = createAsyncThunk(
  'statistics/fetchTopProducts',
  (params = {}) => {
    return statisticService
      .topProducts({ ...initialState.params, ...params })
      .then(normalizeTopProductsResponse);
  },
);
export const fetchSellerTopProducts = createAsyncThunk(
  'statistics/fetchSellerTopProducts',
  (params = {}) => {
    return sellerStatisticService
      .topProducts({ ...initialState.params, ...params })
      .then(normalizeTopProductsResponse);
  },
);

const topProductSlice = createSlice({
  name: 'topProducts',
  initialState,
  extraReducers: (builder) => {
    builder.addCase(fetchTopProducts.pending, (state) => {
      state.loading = true;
    });
    builder.addCase(fetchTopProducts.fulfilled, (state, action) => {
      state.loading = false;
      state.topProducts = action.payload;
      state.error = '';
    });
    builder.addCase(fetchTopProducts.rejected, (state, action) => {
      Object.assign(state, topProductsFailureState(action.error.message));
    });

    builder.addCase(fetchSellerTopProducts.pending, (state) => {
      state.loading = true;
    });
    builder.addCase(fetchSellerTopProducts.fulfilled, (state, action) => {
      state.loading = false;
      state.topProducts = action.payload;
      state.error = '';
    });
    builder.addCase(fetchSellerTopProducts.rejected, (state, action) => {
      Object.assign(state, topProductsFailureState(action.error.message));
    });
  },
  reducers: {
    filterTopProducts(state, action) {
      const { payload } = action;
      state.params = { ...state.params, ...payload };
    },
  },
});
export const { filterTopProducts } = topProductSlice.actions;
export default topProductSlice.reducer;
