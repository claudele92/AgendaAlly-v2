import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import staffInviteService from '../../services/seller/staffInvite';

const initialState = {
  loading: false,
  staffInvites: [],
  error: '',
  params: {
    page: 1,
    perPage: 10,
  },
  meta: {},
};

export const fetchStaffInvites = createAsyncThunk(
  'staffInvite/fetchStaffInvites',
  (params = {}) => {
    return staffInviteService
      .getAll({ ...initialState.params, ...params })
      .then((res) => res);
  },
);

const staffInviteSlice = createSlice({
  name: 'staffInvite',
  initialState,
  extraReducers: (builder) => {
    builder.addCase(fetchStaffInvites.pending, (state) => {
      state.loading = true;
    });
    builder.addCase(fetchStaffInvites.fulfilled, (state, action) => {
      const { payload } = action;
      state.loading = false;
      state.staffInvites = payload.data;
      state.meta = payload.meta;
      state.params.page = payload.meta.current_page;
      state.params.perPage = payload.meta.per_page;
      state.error = '';
    });
    builder.addCase(fetchStaffInvites.rejected, (state, action) => {
      state.loading = false;
      state.staffInvites = [];
      state.error = action.error.message;
    });
  },
});

export default staffInviteSlice.reducer;
