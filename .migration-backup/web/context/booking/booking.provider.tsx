"use client";

import React, { createContext, useContext, useMemo, useReducer, useEffect, useState } from "react";
import { BookingActions, bookingReducer, InitialStateType, Types } from "./booking.reducer";
import useUserStore from "@/global-store/user";
import { usePathname } from "next/navigation";
import { keyFor, readDraft, writeDraft } from "./booking-draft.cjs";

export const initialState: InitialStateType = {
  services: [],
  coupon: undefined,
  dateAndTimes: [],
  fromWalletPrice: undefined,
};

const BookingContext = createContext<{
  state: InitialStateType;
  dispatch: React.Dispatch<BookingActions>;
}>({ state: initialState, dispatch: () => null });

const BookingProvider = ({ children, shopSlug }: { children: React.ReactNode; shopSlug: string }) => {
  const [state, dispatch] = useReducer(bookingReducer, initialState);
  const actor = useUserStore((store) => store.user?.id);
  const draftKey = keyFor(shopSlug, actor);
  const startingBooking = usePathname().endsWith("/booking");
  const [hydratedKey, setHydratedKey] = useState<string>();
  useEffect(() => {
    let restored: InitialStateType | null = null;
    try {
      if (startingBooking) sessionStorage.removeItem(draftKey);
      else restored = readDraft(sessionStorage, draftKey);
    } catch { /* Private/blocked storage. */ }
    dispatch({ type: Types.RestoreDraft, payload: restored || initialState });
    setHydratedKey(draftKey);
  }, [draftKey, startingBooking]);
  useEffect(() => {
    if (hydratedKey === draftKey) {
      try { writeDraft(sessionStorage, draftKey, state); } catch { /* Keep live context usable. */ }
    }
  }, [state, hydratedKey, draftKey]);
  const memoizedValue = useMemo(() => ({ state, dispatch }), [state]);
  return <BookingContext.Provider value={memoizedValue}>
    {hydratedKey === draftKey ? children : <p role="status">Loading booking selections…</p>}
  </BookingContext.Provider>;
};

export default BookingProvider;
export const useBooking = () => {
  const bookingContext = useContext(BookingContext);

  if (!bookingContext) {
    throw new Error("useBooking has to be used within <Booking.Provider>");
  }

  return bookingContext;
};
