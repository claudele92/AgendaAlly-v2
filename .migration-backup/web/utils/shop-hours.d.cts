import type { Shop, WorkingDay } from "@/types/shop";
export function shopHours(shop: Shop | undefined, timestamp: number): {
  closed: boolean | null;
  today: WorkingDay | null;
  reason: string | null;
};