import React, { useMemo, useState } from 'react';
import { Card, Steps } from 'antd';
import { shallowEqual, useSelector } from 'react-redux';
import LanguageList from 'components/language-list';
import { useTranslation } from 'react-i18next';
import ShopMain from './main';
import ShopDelivery from './shopDelivery';
import ShopLocations from 'components/shop/shop-locations';
import ShopSocial from './social';
import { steps } from './steps';
import UserEdit from './user';
import ShopGallery from './gallery';

const { Step } = Steps;

const ShopsAdd = () => {
  const { t } = useTranslation();
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
  const [current, setCurrent] = useState(activeMenu.data?.step || 0);
  const { products_enabled } = useSelector(
    (state) => state.globalSettings.settings,
    shallowEqual,
  );

  const actualSteps = useMemo(() => {
    const isProductsEnabled = products_enabled === '1';
    if (isProductsEnabled) {
      return steps;
    } else {
      return steps.filter(
        (step) => step.content !== 'Product-location-content',
      );
    }
  }, [products_enabled]);

  const next = () => {
    const step = current + 1;
    setCurrent(step);
  };
  const prev = () => {
    const step = current - 1;
    setCurrent(step);
  };

  return (
    <Card title={t('add.shop')} extra={<LanguageList />}>
      <Steps current={current}>
        {actualSteps.map((item) => (
          <Step title={t(item.title)} key={item.title} />
        ))}
      </Steps>

      <div className='steps-content'>
        {actualSteps[current].content === 'First-content' && (
          <ShopMain next={next} user={false} />
        )}

        {actualSteps[current].content === 'Product-location-content' && (
          <ShopLocations next={next} prev={prev} />
        )}

        {actualSteps[current].content === 'Service-location-content' && (
          <ShopLocations next={next} prev={prev} />
        )}

        {actualSteps[current].content === 'Third-content' && (
          <ShopSocial next={next} prev={prev} />
        )}

        {actualSteps[current].content === 'Fourth-content' && (
          <ShopDelivery next={next} prev={prev} />
        )}

        {actualSteps[current].content === 'Shop-gallery-content' && (
          <ShopGallery next={next} prev={prev} />
        )}

        {actualSteps[current].content === 'Fifth-content' && (
          <UserEdit next={next} prev={prev} />
        )}
      </div>
    </Card>
  );
};
export default ShopsAdd;
