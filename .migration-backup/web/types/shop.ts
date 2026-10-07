import { ProductGallery } from "@/types/product";
import { Coordinate, Location, Translation } from "./global";

interface ShopTranslation extends Translation {
  description: string;
  address: string;
}

export interface ShopClosedDate {
  id: number;
  day: string;
}

export interface WorkingDay {
  id: number;
  created_at: string;
  day: string;
  from: string;
  to: string;
  updated_at: string;
  disabled?: boolean;
}

export interface ShopSocial {
  id: number;
  content: string;
  type: string;
}

// Mirrors ShopLocationResource::toArray() - shared by matched_location
// (the single branch a search matched) and locations (every branch the
// shop has, used by the branch switcher).
export interface ShopLocationEntry {
  id?: number;
  region_id?: number;
  country_id?: number;
  city_id?: number;
  area_id?: number;
  type?: number;
  address?: string;
  alias?: string;
  latitude?: number;
  longitude?: number;
  city?: { translation?: { title: string } };
  region?: { translation?: { title: string } };
  country?: { translation?: { title: string } };
}

export interface Shop {
  /** Unambiguous native branch-country IANA zone, otherwise null. */
  hours_timezone?: string | null;
  background_img: string;
  close_time: string;
  open_time: string;
  created_at: string;
  id: number;
  lat_long: Location;
  logo_img: string;
  open: boolean;
  percentage: number;
  status: string;
  status_note: string;
  tax: number;
  translation: ShopTranslation | null;
  updated_at: string;
  user_id: number;
  uuid: string;
  visibility: boolean;
  verify: boolean;
  shop_working_days: WorkingDay[];
  r_avg?: number;
  r_count?: number;
  distance?: number;
  shop_closed_date: ShopClosedDate[];
  slug: string;
  delivery_time: {
    to: string;
    from: string;
    type: string;
  };
  phone?: string;
  socials?: ShopSocial[];
  /** Shop-wide PRODUCT fulfillment methods; absent means legacy delivery + pickup. */
  product_fulfillment_methods?: Array<"delivery" | "pickup">;
  // Present only when the request carried a region/country/city/area
  // filter and this shop has a branch location matching it - see
  // ShopResource::toArray(). A shop with branches in more than one city
  // can legitimately match a filter through a branch other than the one
  // 'translation.address' describes; this is that matched branch.
  matched_location?: ShopLocationEntry;
  // Every location this shop has (both PRODUCT and SERVICE types) - see
  // ShopResource::toArray()'s 'locations' key. Used by the branch
  // switcher to list a multi-branch shop's other locations.
  locations?: ShopLocationEntry[];
}

export interface IDelivery {
  active: boolean;
  create_at: string;
  id: number;
  note: string;
  price: number;
  shop_id: number;
  times: string[];
  translation: Translation | null;
  type: string;
  updated_at: string;
}

export interface ShopDetail extends Shop {
  seller: {
    fistname: string;
    lastname: string;
    id: number;
    role: string;
  };
  rating_avg: string;
  subscription: {
    id: number;
    shop_id: number;
    subscription_id: number;
    expired_at: string;
    price: number;
    type: string;
    active: number;
    created_at: string;
    updated_at: string;
  };
}

export interface StoreWithDelivery extends Shop {
  deliveries: IDelivery[];
}

export interface CreateShopCredentials {
  lat_long: {
    latitude: number;
    longitude: number;
  };
  phone: string;
  title: {
    [key: string]: string;
  };
  description: {
    [key: string]: string;
  };
  address: {
    [key: string]: string;
  };
  images: string[];
}

export interface CreateShopBody
  extends Omit<
    CreateShopCredentials,
    "images" | "location" | "open_time" | "close_time" | "delivery_time_type" | "delivery_type"
  > {
  location: Coordinate;
  logo_image: string;
  bg_image: string;
  documents: string[];
}

export interface ShopGallery {
  id: number;
  shop_id: number;
  galleries: ProductGallery[];
}

export interface ShopTag {
  translation?: Translation;
  id: number;
}

export interface ShopFilter {
  order_by: { [key: string]: string };
  service_type: { [key: string]: string };
  service_min_price: number;
  service_max_price: number;
  interval_min: number;
  interval_max: number;
  takes: [];
  gender: { [key: string]: string };
  categories: [];
}
