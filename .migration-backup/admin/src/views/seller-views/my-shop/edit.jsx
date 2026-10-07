import { Card, Steps } from 'antd';
import { shallowEqual, useDispatch, useSelector } from 'react-redux';
import { setMenuData } from 'redux/slices/menu';
import LanguageList from 'components/language-list';
import { useTranslation } from 'react-i18next';
import { useQueryParams } from 'helpers/useQueryParams';
import ShopMain from './main';
import Delivery from './shopDelivery';
import ShopLocations from 'components/shop/seller-shop-locations';
import ShopSocial from './social';
import ShopGallery from './gallery';
import { useMemo } from 'react';

const steps = [
  {
    title: 'shop',
    content: 'First-content',
  },
  {
    title: 'product.locations',
    content: 'Product-location-content',
  },
  {
    title: 'service.locations',
    content: 'Service-location-content',
  },
  {
    title: 'shop.social',
    content: 'Third-content',
  },
  {
    title: 'gallery',
    content: 'Shop-gallery-content',
  },
  {
    title: 'delivery',
    content: 'Fourth-content',
  },
];

const { Step } = Steps;

export default function MyShopEdit() {
  const { t } = useTranslation();
  const dispatch = useDispatch();
  const queryParams = useQueryParams();
  const current = Number(queryParams.values?.step || 0);
  const { activeMenu } = useSelector((state) => state.menu, shallowEqual);
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
    queryParams.set('step', step);
  };
  const prev = () => {
    const step = current - 1;
    queryParams.set('step', step);
  };

  const onChange = (step) => {
    dispatch(setMenuData({ activeMenu, data: { ...activeMenu.data, step } }));
    queryParams.set('step', step);
  };

  return (
    <Card title={t('shop.edit')} extra={<LanguageList />}>
      <Steps current={current} onChange={onChange}>
        {actualSteps.map((item) => (
          <Step title={t(item.title)} key={item.title} />
        ))}
      </Steps>
      <div className='steps-content'>
        {actualSteps[current].content === 'First-content' && (
          <ShopMain next={next} />
        )}

        {actualSteps[current].content === 'Product-location-content' && (
          <ShopLocations
            next={next}
            prev={prev}
            locationType={{ label: 'product', value: 1, key: 1 }}
          />
        )}

        {actualSteps[current].content === 'Service-location-content' && (
          <ShopLocations
            next={next}
            prev={prev}
            locationType={{ label: 'service', value: 2, key: 2 }}
          />
        )}

        {actualSteps[current].content === 'Third-content' && (
          <ShopSocial prev={prev} next={next} />
        )}

        {actualSteps[current].content === 'Shop-gallery-content' && (
          <ShopGallery next={next} prev={prev} />
        )}

        {actualSteps[current].content === 'Fourth-content' && (
          <Delivery prev={prev} />
        )}
      </div>
    </Card>
  );
}
