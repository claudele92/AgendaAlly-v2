import { Service } from "@/types/service";
import dynamic from "next/dynamic";
import { LoadingCard } from "@/components/loading";
import { Button } from "@/components/button";
import { useModal } from "@/hook/use-modal";
import { useTranslation } from "react-i18next";
import { Modal } from "@/components/modal";
import { Review } from "@/types/review";
import { useQuery } from "@tanstack/react-query";
import { reviewService } from "@/services/review";

const BookingReviewCreate = dynamic(
  () =>
    import("./booking-review-create").then((component) => ({
      default: component.BookingReviewCreate,
    })),
  {
    loading: () => <LoadingCard />,
  }
);

const BookingReviewEdit = dynamic(
  () =>
    import("./booking-review-edit").then((component) => ({
      default: component.BookingReviewEdit,
    })),
  {
    loading: () => <LoadingCard />,
  }
);

interface BookingReviewProps {
  service?: Service | null;
  bookingId?: number;
  bookingParentId?: number;
  initialData: Review | null;
}

export const BookingReview = ({
  service,
  bookingId,
  bookingParentId,
  initialData,
}: BookingReviewProps) => {
  const { t } = useTranslation();
  const { data, refetch } = useQuery({
    queryKey: ["canReview", "booking", service?.id],
    queryFn: () => reviewService.checkCanReview({ type: "booking", type_id: bookingParentId }),
    enabled: !!service,
    retry: false,
  });
  const [isModalOpen, openModal, closeModal] = useModal();

  const onSuccess = () => {
    refetch();
    closeModal();
  };

  if (data?.data?.added_review || !data) {
    return null;
  }

  return (
    <>
      <Button fullWidth onClick={openModal}>
        {t(data?.data?.added_review ? "edit.review" : "add.review")}
      </Button>
      <Modal isOpen={isModalOpen} onClose={closeModal} withCloseButton>
        <div className="pt-5 pb-7 md:px-6 px-4">
          {initialData ? (
            <BookingReviewEdit
              service={service}
              bookingId={bookingId}
              onSuccess={onSuccess}
              bookingParentId={bookingParentId}
              initialData={initialData}
            />
          ) : (
            <BookingReviewCreate
              service={service}
              bookingId={bookingId}
              onSuccess={onSuccess}
              bookingParentId={bookingParentId}
            />
          )}
        </div>
      </Modal>
    </>
  );
};
