import React from "react";
import BookingProvider from "@/context/booking";

const ShopDetailLayout = async ({ children, params }: {
  children: React.ReactNode; params: Promise<{ id: string }>;
}) => (
  <BookingProvider shopSlug={(await params).id}>{children}</BookingProvider>
);

export default ShopDetailLayout;
