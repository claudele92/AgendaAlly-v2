import { Col, Row } from 'antd';
import React, { useEffect, useState } from 'react';
import mastersService from 'services/rest/masters';
import { fetchBookingList } from 'redux/slices/booking';
import { fetchMasterDisabledTimesAsAdmin } from 'redux/slices/disabledTimes';
import { useDispatch } from 'react-redux';
import SearchInput from 'components/search-input';
import { useTranslation } from 'react-i18next';
import { DebounceSelect } from 'components/search';
import shopService from 'services/restaurant';
import { buildMasterOptions } from '../helpers/master-options.mjs';

const BookingFilter = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const [filterValues, setFilterValues] = useState({});
  const fetchMasterList = (search) => {
    const params = {
      search: !!search?.length ? search : undefined,
      page: 1,
      perPage: 20,
      role: 'master',
      shop_id: filterValues?.shop_id || undefined,
    };
    return mastersService.getAll(params).then(({ data }) => {
      return buildMasterOptions(data, search);
    });
  };

  const handleFilter = (newFilterParam) => {
    setFilterValues((prev) => ({ ...prev, ...newFilterParam }));
  };

  const fetchFilteredData = () => {
    dispatch(fetchBookingList(filterValues));
    dispatch(
      fetchMasterDisabledTimesAsAdmin({
        perPage: 100,
        ...filterValues,
      }),
    );
  };

  async function fetchShops(search) {
    const params = { search, status: 'approved' };
    return shopService.getAll(params).then(({ data }) =>
      data.map((item) => ({
        label: item?.translation?.title,
        value: item?.id,
        key: item?.id,
      })),
    );
  }

  useEffect(() => {
    fetchFilteredData();
  }, [filterValues]);

  useEffect(() => {
    fetchMasterList();
  }, []);

  return (
    <div
      style={{
        display: 'flex',
        flexWrap: 'wrap',
        rowGap: '6px',
        columnGap: '6px',
      }}
    >
      <Row gutter={24}>
        <Col style={{ minWidth: '253px' }}>
          <SearchInput
            defaultValue={filterValues.search}
            // resetSearch={!data?.search}
            placeholder={t('search')}
            handleChange={(search) => handleFilter({ search })}
          />
        </Col>
        <Col style={{ minWidth: '189px' }}>
          <DebounceSelect
            className='w-100'
            fetchOptions={fetchMasterList}
            placeholder={t('select.master')}
            refetchOptions={true}
            onClear={() => handleFilter({ master_id: null })}
            onSelect={(master) => handleFilter({ master_id: master?.value })}
          />
        </Col>
        <Col style={{ minWidth: '189px' }}>
          <DebounceSelect
            placeholder={t('select.shop')}
            fetchOptions={fetchShops}
            style={{ width: '100%' }}
            onSelect={(shop) => handleFilter({ shop_id: shop?.value })}
            onDeselect={() => handleFilter({ shop_id: null })}
            onClear={() => handleFilter({ shop_id: null })}
            allowClear={true}
            value={filterValues?.shop_id}
          />
        </Col>
      </Row>
    </div>
  );
};

export default BookingFilter;
