"use client";

import { useEffect, useState } from "react";
import type { Shop } from "@/types/shop";
import { shopHours } from "@/utils/shop-hours.cjs";

export const useShopHours = (shop: Shop | undefined, initialTime?: number) => {
  const [time, setTime] = useState(initialTime);
  useEffect(() => {
    const refresh = () => setTime(Date.now());
    refresh();
    const timer = setInterval(refresh, 15000);
    window.addEventListener("focus", refresh);
    return () => {
      clearInterval(timer);
      window.removeEventListener("focus", refresh);
    };
  }, [initialTime]);
  return time === undefined
    ? { closed: null, today: null, reason: "Checking business hours" }
    : shopHours(shop, time);
};