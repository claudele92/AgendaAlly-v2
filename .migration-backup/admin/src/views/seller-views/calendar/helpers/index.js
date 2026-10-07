import currencyService from 'services/currency';
import paymentService from 'services/seller/payment';
import mastersService from 'services/seller/booking-masters';
import restServicesService from 'services/seller/services';
import shopService from 'services/shop';
import userService from 'services/seller/user';
import bookingService from 'services/booking';
import download from 'downloadjs';
import { buildMasterOptions } from 'views/calendar/helpers/master-options.mjs';

export async function fetchUsers(search) {
  const params = {
    search,
    perPage: 10,
  };
  return userService.shopUsers(params).then(({ data }) => {
    return data.map((item) => ({
      label: `${item?.firstname} ${item?.lastname}`,
      value: item?.id,
      key: item?.id,
    }));
  });
}

export async function fetchMasterList({
  service_id,
  shop_id,
  shop_location_id,
  search,
}) {
  const params = {
    perPage: 10,
    role: 'master',
    search: search?.length ? search : undefined,
    service_id,
    shop_id,
    shop_location_id,
  };
  return mastersService.getAll(params).then(({ data }) => {
    return buildMasterOptions(data, search);
  });
}

export const fetchService = async (params, items = []) => {
  return await restServicesService.getAll(params).then(({ data }) => {
    return data?.map((item) => ({
      label: item?.translation?.title,
      value: item?.id,
      key: `${item?.id} ${item.type}`,
      disabled: items?.length
        ? !!items?.find(
            (calcData) => calcData?.service_master?.service?.id === item?.id,
          )
        : false,
    }));
  });
};

export async function fetchShops(search) {
  const params = { search, status: 'approved' };
  return shopService.getAll(params).then(({ data }) =>
    data.map((item) => ({
      label: item.translation?.title,
      value: item.id,
      key: item.id,
    })),
  );
}
export async function fetchPayments() {
  return paymentService.getAll().then(({ data }) => {
    return data.map((item) => ({
      value: item.payment.id,
      label: item.payment.tag,
      key: item.payment.id,
    }));
  });
}
export async function fetchCurrency() {
  return currencyService.getAll().then(({ data }) => {
    return data
      .filter((item) => item.active)
      .map((item) => ({
        value: item.id,
        label: `${item.title} (${item.symbol || ''})`,
        key: item.id,
      }));
  });
}

export async function downloadInvoice(id) {
  return bookingService
    .downloadInvoice(id)
    .then((res) =>
      download(res, `booking-invoice-${id}.pdf`, 'application/pdf'),
    );
}
