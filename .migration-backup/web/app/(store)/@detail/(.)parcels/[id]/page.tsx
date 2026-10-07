"use client";

import { useRouter } from "next/navigation";
import { OrderDetailLoading } from "@/components/order-detail/order-detail-loading";
import { Modal } from "@/components/modal";
import { Suspense, use } from "react";
import ParcelDetail from "../../../../../components/parcel-detail";

const OrderDetailsModal = (props: { params: Promise<{ id: string }> }) => {
  const params = use(props.params);
  const router = useRouter();
  return (
    <Modal
      disableCloseOnOverlayClick
      withCloseButton
      isOpen
      onClose={() => router.back()}
      transparent
    >
      <Suspense fallback={<OrderDetailLoading />}>
        <ParcelDetail id={Number(params.id)} />
      </Suspense>
    </Modal>
  );
};

export default OrderDetailsModal;
