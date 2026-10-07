import { Currency, DeliveryPoint, DeliveryPrice, Payment } from "@/types/global";
import { Shop } from "@/types/shop";
import { Address } from "@/types/address";
import { IUser } from "@/types/user";
import { CartDetailProduct } from "@/types/cart";
import { Coupon } from "@/types/product";
import { OrderPickup, PickupSelection } from "@/types/pickup";

export interface Transaction {
  id: number;
  status: string;
  note: string;
  payable_id: number;
  payable_type: string;
  price: number;
  status_description: string;
  payment_system: Payment;
  perform_time: string;
}

export interface OrderCreateBody {
  cart_id?: number;
  currency_id?: number;
  rate?: number;
  delivery_date: string;
  delivery_type: string;
  delivery_point_id?: number;
  delivery_price_id?: number;
  address_id?: number;
  notes?: {
    order?: Record<number, string | undefined>;
    product?: Record<number, string | undefined>;
  };
  payment_id?: number;
  coupon?: Record<number, string | undefined>;
  tips?: number;
  from_wallet_price?: number;
  pickup_selections?: Record<number, PickupSelection>;
}

export interface OrderDetail {
  id: number;
  order_id: number;
  shop_id: number;
  commission_fee: number;
  total_price: number;
  status: string;
  created_at: string;
  updated_at: string;
  shop: Shop;
}

export interface Order {
  id: number;
  user_id: number;
  address_id: number | null;
  delivery_price_id?: number;
  currency_id: number;
  status: string;
  total_price: number;
  delivery_fee?: number;
  total_tax: number;
  origin_price: number;
  rate: number;
  delivery_date?: string | null;
  delivery_type: string;
  order_details_count: number;
  created_at: string;
  updated_at: string;
  currency: Currency;
  details: OrderDetail[];
  total_discount?: number;
  total_shop_tax?: number;
  service_fee: number;
  shop: Shop;
  tax?: number;
  transactions?: Transaction[];
  pickup?: OrderPickup | null;
}

export interface Refund {
  answer?: string;
  cause?: string;
  id: number;
  order: Order;
  status?: "pending" | "accepted" | "canceled";
  created_at?: string;
  updated_at?: string;
}

export interface OrderNoteItem {
  created_at: string;
  title: Record<string, string>;
}

export interface OrderNote {
  id: number;
  order_id: number;
  status: string;
  created_at: string;
  updated_at: string;
  notes: OrderNoteItem[];
}

export interface OrderFull extends Omit<Order, "details"> {
  delivery_point: DeliveryPoint | null;
  my_address: Address | null;
  deliveryPrice: DeliveryPrice | null;
  deliveryman: IUser | null;
  transaction: Transaction | null;
  details: CartDetailProduct[];
  note?: string;
  order_refunds: Refund[];
  user: IUser;
  coupon?: Coupon;
  parent_id?: number;
  track_name?: string;
  track_id?: string;
  track_url?: string;
  notes: OrderNote[];
  otp?: number;
  tips?: number;
}

export interface RefundCreateBody {
  cause: string;
  order_id?: number;
}

export interface DeliverymanLocation {
  type?: string;
  data?: {
    id?: number;
    location: {
      latitude: number;
      longitude: number;
    };
    user_id?: number;
    order?: Order;
  };
}

export interface DeliverymanLocationCustomData {
  location: {
    latitude: number;
    longitude: number;
  };
  firstname?: string;
  lastname?: string;
}
