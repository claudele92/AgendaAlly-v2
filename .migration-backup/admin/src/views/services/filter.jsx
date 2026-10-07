import { Col, Space } from 'antd';
import { useTranslation } from 'react-i18next';
import categoryService from 'services/category';
import shopService from 'services/shop';
import { DebounceSelect } from 'components/search';
import SearchInput from 'components/search-input';

const Filter = ({ filters, setFilters }) => {
  const { t } = useTranslation();

  const fetchCategories = (search) => {
    const params = {
      type: 'service',
      perPage: 20,
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

  const fetchShops = (search) => {
    const params = {
      search: search?.length ? search : undefined,
      page: 1,
      perPage: 20,
    };
    return shopService.getAll(params).then((res) =>
      res.data?.map((item) => ({
        label: item?.translation?.title,
        value: item?.id,
        key: item?.id,
      })),
    );
  };

  return (
    <Space wrap>
      <Col style={{ minWidth: '228px' }}>
        <SearchInput
          placeholder={t('search')}
          className='w-100'
          onChange={(value) =>
            setFilters({ ...filters, search: value?.target?.value })
          }
        />
      </Col>
      <Col style={{ minWidth: '170px' }}>
        <DebounceSelect
          fetchOptions={fetchCategories}
          className='w-100'
          placeholder={t('select.category')}
          onChange={(value) => setFilters({ ...filters, category: value })}
        />
      </Col>
      <Col style={{ minWidth: '170px' }}>
        <DebounceSelect
          fetchOptions={fetchShops}
          className='w-100'
          placeholder={t('select.shop')}
          onChange={(value) => setFilters({ ...filters, shop: value })}
        />
      </Col>
    </Space>
  );
};

export default Filter;
