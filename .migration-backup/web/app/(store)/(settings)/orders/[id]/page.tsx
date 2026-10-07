"use client";

import { useRouter } from "next/navigation";
import { Modal } from "@/components/modal";
import { Suspense, use } from "react";
import { OrderDetailLoading } from "@/components/order-detail/order-detail-loading";
import OrderDetail from "../../../../../components/order-detail";

const OrderDetailsPage = (props: { params: Promise<{ id: string }> }) => {
  const params = use(props.params);
  const router = useRouter();
  return (
    <Modal transparent isOpen onClose={() => router.replace("/orders")}>
      <Suspense fallback={<OrderDetailLoading />}>
        <OrderDetail id={Number(params.id)} onRepeat={() => router.push("/cart")} />
      </Suspense>
    </Modal>
  );
};

export default OrderDetailsPage;
