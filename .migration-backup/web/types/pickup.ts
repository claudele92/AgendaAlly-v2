export interface PickupSelection {
  shop_location_id?: number;
  legacy_ready_based?: boolean;
  date?: string;
  window_start?: string;
  window_end?: string;
}

export interface PickupWindow {
  start?: string;
  end?: string;
  window_start: string;
  window_end: string;
  local_start?: string;
  local_end?: string;
  available?: boolean;
  remaining_capacity?: number | null;
}

export interface PickupLocation {
  id: number;
  alias?: string | null;
  address?: string | null;
  city?: { translation?: { title?: string } } | null;
  country?: { translation?: { title?: string } } | null;
  timing_mode?: "asap" | "as_soon_as_ready" | "scheduled";
  timezone?: string | null;
  eligible?: boolean;
}

export interface PickupAvailability {
  eligible: boolean;
  timing_mode?: "asap" | "as_soon_as_ready" | "scheduled";
  timezone?: string | null;
  date?: string | null;
  today?: string;
  locations: PickupLocation[];
  windows?: PickupWindow[];
}

export interface OrderPickup {
  timing_mode: "asap" | "as_soon_as_ready" | "scheduled";
  shop_id: number;
  shop_location_id: number | null;
  address: string | null;
  alias?: string | null;
  timezone?: string | null;
  window_start?: string | null;
  window_end?: string | null;
}