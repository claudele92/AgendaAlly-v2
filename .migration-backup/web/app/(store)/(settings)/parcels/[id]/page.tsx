"use client";

import { useRouter } from "next/navigation";
import { Modal } from "@/components/modal";
import { Suspense, use } from "react";
import { OrderDetailLoading } from "@/components/order-detail/order-detail-loading";
import ParcelDetail from "../../../../../components/parcel-detail";

const OrderDetailsPage = (props: { params: Promise<{ id: string }> }) => {
  const params = use(props.params);
  const router = useRouter();
  return (
    <Modal transparent withCloseButton isOpen onClose={() => router.replace("/parcels")}>
      <Suspense fallback={<OrderDetailLoading />}>
        <ParcelDetail id={Number(params.id)} />
      </Suspense>
    </Modal>
  );
};

export default OrderDetailsPage;
