import { Alert, Button, Col, Drawer, Select } from 'antd';
import React, { useEffect, useState } from 'react';
import mastersService from 'services/seller/booking-masters';
import { fetchSellerBookingList } from 'redux/slices/booking';
import { fetchMasterDisabledTimesAsSeller } from 'redux/slices/disabledTimes';
import { useDispatch } from 'react-redux';
import { useTranslation } from 'react-i18next';
import SearchInput from '../../../../components/search-input';
import { buildMasterOptions } from 'views/calendar/helpers/master-options.mjs';

const BookingFilter = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const [options, setOptions] = useState([]);
  const [filterValues, setFilterValues] = useState({});
  const [filtersOpen, setFiltersOpen] = useState(false);
  const [draftMasterId, setDraftMasterId] = useState(undefined);
  const [mastersLoading, setMastersLoading] = useState(false);
  const [mastersError, setMastersError] = useState('');

  async function fetchMasterList() {
    const params = {
      perPage: 100,
      role: 'master',
    };
    setMastersLoading(true);
    setMastersError('');
    try {
      const { data } = await mastersService.getAll(params);
      setOptions(buildMasterOptions(data));
    } catch (error) {
      setOptions([]);
      setMastersError(error?.response?.data?.message || 'Assigned Specialists could not be loaded.');
    } finally {
      setMastersLoading(false);
    }
  }

  const handleFilter = (newFilterParam) => {
    setFilterValues((prev) => ({ ...prev, ...newFilterParam }));
  };

  const fetchFilteredData = () => {
    dispatch(fetchSellerBookingList(filterValues));
    dispatch(
      fetchMasterDisabledTimesAsSeller({
        perPage: 100,
        ...filterValues,
      }),
    );
  };

  const openFilters = () => {
    setDraftMasterId(filterValues.master_id);
    setFiltersOpen(true);
  };
  const applyMobileFilters = () => {
    handleFilter({ master_id: draftMasterId || undefined });
    setFiltersOpen(false);
  };
  const clearMobileFilters = () => {
    setDraftMasterId(undefined);
    handleFilter({ master_id: undefined });
    setFiltersOpen(false);
  };

  useEffect(() => {
    fetchFilteredData();
  }, [filterValues]);

  useEffect(() => {
    fetchMasterList();
  }, []);

  return (
    <div className='aa-vendor-filter'>
      <Col style={{ minWidth: 0, width: '253px', maxWidth: '100%' }}>
        <SearchInput
          defaultValue={filterValues.search}
          // resetSearch={!data?.search}
          placeholder={t('search')}
          handleChange={(search) => handleFilter({ search })}
        />
      </Col>
      <Col className='aa-vendor-filter__desktop' style={{ minWidth: 0, width: '189px', maxWidth: '100%' }}>
        <Select
          className='w-100'
          allowClear
          placeholder={t('all')}
          aria-label='Filter by assigned Specialist'
          value={filterValues.master_id}
          loading={mastersLoading}
          onChange={(masterId) => handleFilter({ master_id: masterId || undefined })}
        >
          {options.map((item) => (
            <Select.Option key={item.key} value={item.value}>
              {item.label}
            </Select.Option>
          ))}
        </Select>
      </Col>
      {mastersError && (
        <Alert
          className='aa-vendor-filter__error'
          type='error'
          showIcon
          message={mastersError}
          action={<Button size='small' onClick={fetchMasterList}>Retry</Button>}
        />
      )}
      <Button className='aa-vendor-filter__mobile-trigger' onClick={openFilters}>
        Filters{filterValues.master_id ? ' · 1' : ''}
      </Button>
      <Drawer
        title='Schedule filters'
        placement='bottom'
        height='min(72vh, 520px)'
        visible={filtersOpen}
        onClose={() => setFiltersOpen(false)}
        className='aa-vendor-filter__drawer'
      >
        <label className='aa-vendor-filter__drawer-label' htmlFor='vendor-calendar-specialist-filter'>
          Assigned Specialist
        </label>
        <Select
          id='vendor-calendar-specialist-filter'
          className='w-100'
          allowClear
          placeholder={t('all')}
          value={draftMasterId}
          loading={mastersLoading}
          onChange={(masterId) => setDraftMasterId(masterId || undefined)}
        >
          {options.map((item) => (
            <Select.Option key={item.key} value={item.value}>
              {item.label}
            </Select.Option>
          ))}
        </Select>
        <div className='aa-vendor-filter__actions'>
          <Button onClick={clearMobileFilters}>Clear Specialist</Button>
          <Button type='primary' onClick={applyMobileFilters}>Apply filters</Button>
        </div>
      </Drawer>
    </div>
  );
};

export default BookingFilter;
