import Card from 'components/card';
import cls from './general.module.scss';
import TopBar from '../top-bar';
import MainCards, { MainBookingCards } from '../main-cards';
import OrderStatisticsOverview from '../order-statistics-overview';
import OrderStatusStatistics from '../order-status-statistics';
import TotalSalesOverview from '../total-sales-overview';
import ToDo from '../to-do';
import TopSellingProducts from '../top-selling-products';
import TopCustomers from '../top-customers';
import BookingsRevenueChart from '../bookings-revenue-chart';
import { shallowEqual, useSelector } from 'react-redux';

const General = ({ role }) => {
  const { parcelMode } = useSelector(
    (state) => state.theme.theme,
    shallowEqual,
  );
  const { products_enabled } = useSelector(
    (state) => state.globalSettings.settings,
    shallowEqual,
  );

  return (
    <div className={cls.container}>
      <TopBar />
      {role !== 'deliveryman' &&
        role !== 'shop_manager' &&
        !(role === 'admin' && parcelMode) && (
        <>
          <Card>
            <MainBookingCards />
          </Card>
        </>
      )}
      {(role === 'seller' || role === 'admin') && (
        <Card>
          <BookingsRevenueChart isSeller={role === 'seller'} />
        </Card>
      )}
      {!!(
        role !== 'master' &&
        (role !== 'seller' || Number(products_enabled))
      ) && (
        <Card>
          <MainCards />
        </Card>
      )}
      {((role === 'seller' && Number(products_enabled)) ||
        role === 'admin') && (
        <Card>
          <div className={cls.orderStatisticsOverviewContainer}>
            <div className={cls.item}>
              <OrderStatisticsOverview />
            </div>
            <div className={cls.item}>
              <OrderStatusStatistics />
            </div>
          </div>
        </Card>
      )}
      {((role === 'seller' && Number(products_enabled)) ||
        role === 'admin') && (
        <Card>
          <div className={cls.orderStatisticsOverviewContainer}>
            <div className={cls.item}>
              <TotalSalesOverview />
            </div>
            <div className={cls.item}>
              <ToDo />
            </div>
          </div>
        </Card>
      )}
      {((role === 'seller' && Number(products_enabled)) ||
        role === 'admin') && (
        <Card>
          <div className={cls.topSellingProductsContainer}>
            <div className={cls.item}>
              <TopSellingProducts />
            </div>
            <div className={cls.item}>
              <TopCustomers />
            </div>
          </div>
        </Card>
      )}
    </div>
  );
};

export default General;
