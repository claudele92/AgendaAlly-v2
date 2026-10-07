import { useTranslation } from 'react-i18next';
import { Col } from 'antd';
import { DebounceSelect } from 'components/search';
import React from 'react';
import shopService from 'services/shop';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { setMenuData } from 'redux/slices/menu';
import userService from 'services/user';

const Filter = () => {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);

  const handleFilter = (items) => {
    const data = activeMenu.data;
    dispatch(
      setMenuData({
        activeMenu,
        data: { ...data, ...items },
      }),
    );
  };

  const fetchShops = (search) => {
    const params = {
      perPage: 10,
      page: 1,
      search,
    };

    return shopService.getAll(params).then((res) =>
      res?.data?.map((item) => ({
        label: item?.translation?.title,
        value: item?.id,
        key: item?.id,
      })),
    );
  };

  const fetchUsers = (search) => {
    const params = {
      parPage: 10,
      page: 1,
      search,
    };

    return userService.getAll(params).then((res) =>
      res?.data?.map((item) => ({
        label: `${item?.firstname} ${item?.lastname}`,
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
      <Col style={{ minWidth: '170px' }}>
        <DebounceSelect
          className='w-100'
          fetchOptions={fetchShops}
          placeholder={t('select.shop')}
          onChange={(value) => handleFilter({ shop_id: value?.value })}
        />
      </Col>
      <Col style={{ minWidth: '170px' }}>
        <DebounceSelect
          className='w-100'
          fetchOptions={fetchUsers}
          placeholder={t('select.user')}
          onChange={(value) => handleFilter({ user_id: value?.value })}
        />
      </Col>
    </div>
  );
};

export default Filter;
