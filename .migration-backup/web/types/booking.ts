import { Master } from "@/types/master";
import { ServiceExtras, ServiceMaster } from "@/types/service";
import { Shop, ShopLocationEntry } from "@/types/shop";
import { Review } from "@/types/review";
import { Currency, Transaction } from "@/types/global";

export interface BookingCalculateBody {
  payment_id?: number;
  currency_id?: number;
  data: {
    service_master_id?: number;
    start_date: string;
  }[];
}

export interface BookingDate {
  closed: boolean;
  date: string;
  day: string;
  month: string;
  name: string;
  times: string[];
  disabled_times: string[];
}

export interface ShortServiceMasterData {
  id: number;
  master_id: number;
  master: Master;
}
export interface ServiceMasterDate {
  service_master: ShortServiceMasterData;
  times: BookingDate[];
}

export interface BookingAddress {
  address: string;
  lat: number;
  long: number;
}

export interface ExtraBookingAddress {
  additional_details?: string;
  firstname?: string;
  lastname?: string;
  phone?: string;
  street_house_number?: string;
  zipcode?: string;
}

interface BookingAddressData extends BookingAddress, ExtraBookingAddress {}

export interface BookingCreateBody {
  payment_id?: number;
  currency_id?: number;
  data: {
    service_master_id?: number;
    note?: string;
    data?: BookingAddressData;
    start_date: string;
    // Which branch this booking is at - see BookingService::
    // resolveBookingLocation() on the backend. Required once a shop has
    // more than one SERVICE location; auto-resolved server-side for the
    // unambiguous cases (a single location, or a master only assigned to
    // one), otherwise the create call fails with LOCATION_AMBIGUOUS.
    shop_location_id?: number;
  }[];
  user_gift_cart_id?: number;
  coupon?: string;
  from_wallet_price?: number;
}

export interface Booking {
  extra_price?: number;
  id: number;
  commission_fee: number;
  created_at: string;
  currency_id: number;
  discount?: number | null;
  end_date: string;
  ids_by_parent: string;
  master_id: number;
  note?: string;
  price: number;
  rate: number;
  service_fee: number;
  service_master_id: number;
  shop_id: number;
  start_date: string;
  status: string;
  total_price: number;
  total_price_by_parent: number;
  type: string;
  user_id: number;
  updated_at: string;
  master: Master | null;
  service_master: ServiceMaster | null;
  shop: Shop | null;
  // The branch this booking is actually at - see BookingService::
  // resolveBookingLocation() on the backend. Prefer this address over
  // shop.translation.address (the shop's flat, shop-wide address) when
  // showing where a specific booking happens; it's only null for
  // bookings placed before shop_location_id existed, or a shop with no
  // SERVICE locations at all.
  shop_location?: ShopLocationEntry | null;
  canceled_all: boolean;
  review: Review | null;
  data?: Record<string, any>;
  currency?: Currency;
  notes?: string[];
  parent_id?: number;
  transaction?: {
    status?: string;
    payment_system: {
      tag: string;
    };
  };
  transactions?: Transaction[];
  gift_cart_price?: number;
  user_member_ship?: {
    id: number;
    member_ship_id: number;
    price: number;
  };
  coupon_price?: number;
  extras?: ServiceExtras[];
}

export interface BookingCalculateRes {
  total_extra_price?: number;
  price: number;
  coupon_price: number;
  total_commission_fee: number;
  total_discount: number;
  total_gift_cart_price: number;
  total_price: number;
  total_service_fee: number;
  currency_id?: number;
  currency?: Currency;
  items: {
    errors?: string[];
  }[];
  status: boolean;
}

export interface BookingReviewFormValues {
  rating: number;
  comment: string;
  cleanliness?: boolean;
  masters?: boolean;
  location?: boolean;
  price?: boolean;
  interior?: boolean;
  service?: boolean;
  communication?: boolean;
  equipment?: boolean;
}

export interface BookingBookingPay {
  booking_id: number;
}
