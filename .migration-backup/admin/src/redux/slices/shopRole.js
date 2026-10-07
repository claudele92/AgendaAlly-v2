import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import shopRoleService from '../../services/seller/shopRole';

const initialState = {
  loading: false,
  shopRoles: [],
  error: '',
  params: {
    page: 1,
    perPage: 10,
  },
  meta: {},
};

export const fetchShopRoles = createAsyncThunk(
  'shopRole/fetchShopRoles',
  (params = {}) => {
    return shopRoleService
      .getAll({ ...initialState.params, ...params })
      .then((res) => res);
  },
);

const shopRoleSlice = createSlice({
  name: 'shopRole',
  initialState,
  extraReducers: (builder) => {
    builder.addCase(fetchShopRoles.pending, (state) => {
      state.loading = true;
    });
    builder.addCase(fetchShopRoles.fulfilled, (state, action) => {
      const { payload } = action;
      state.loading = false;
      state.shopRoles = payload.data;
      state.meta = payload.meta;
      state.params.page = payload.meta.current_page;
      state.params.perPage = payload.meta.per_page;
      state.error = '';
    });
    builder.addCase(fetchShopRoles.rejected, (state, action) => {
      state.loading = false;
      state.shopRoles = [];
      state.error = action.error.message;
    });
  },
});

export default shopRoleSlice.reducer;
