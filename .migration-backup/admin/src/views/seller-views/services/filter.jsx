import React from 'react';
import { Col, Space } from 'antd';
import { useTranslation } from 'react-i18next';
import categoryService from 'services/seller/category';
import { DebounceSelect } from 'components/search';
import SearchInput from 'components/search-input';

const Filter = ({ filters, setFilters }) => {
  const { t } = useTranslation();

  const fetchCategories = (search = '') => {
    const params = {
      type: 'service',
      perPage: 10,
      page: 1,
      search: search?.length ? search : undefined,
    };

    return categoryService.getAll(params).then((res) =>
      res?.data?.map((item) => ({
        label: item?.translation?.title,
        value: item?.id,
        key: item?.id,
      })),
    );
  };

  return (
    <div
      style={{
        display: 'flex',
        flexWrap: 'wrap',
        rowGap: '6px',
        columnGap: '6px',
      }}
    >
      <Col style={{ minWidth: '228px' }}>
        <SearchInput
          placeholder={t('search')}
          className={'w-200px'}
          onChange={(value) =>
            setFilters({ ...filters, search: value?.target?.value })
          }
        />
      </Col>
      <Col style={{ minWidth: '170px' }}>
        <DebounceSelect
          fetchOptions={fetchCategories}
          className={'w-200px'}
          placeholder={t('select.category')}
          onChange={(value) => setFilters({ ...filters, category: value })}
        />
      </Col>
    </div>
  );
};

export default Filter;
