import Image from "next/image";
import { resolveServiceMediaUrl } from "@/utils/service-media-url.cjs";
import QuestionLineIcon from "remixicon-react/QuestionLineIcon";
import { Service } from "@/types/service";
import PlusOutlinedIcon from "@/assets/icons/plus-outlined";
import { IconButton } from "@/components/icon-button";
import { Translate } from "@/components/translate";
import { Price } from "@/components/price";
import { useParams, useRouter } from "next/navigation";
import { useQueryParams } from "@/hook/use-query-params";
import CheckOutlinedIcon from "@/assets/icons/check-outlined";
import { useBooking } from "@/context/booking";
import { BookingService, Types } from "@/context/booking/booking.reducer";
import { Modal } from "@/components/modal";
import { useModal } from "@/hook/use-modal";
import { ServiceFaqs } from "./service-faqs";
import { useState } from "react";

interface ServiceCardProps {
  data?: Service;
  onCardClick?: (trigger: HTMLElement) => void;
  isBookingPage?: boolean;
}

export const ServiceCard = ({ data, onCardClick, isBookingPage }: ServiceCardProps) => {
  const router = useRouter();
  const params = useParams();
  const { dispatch, state } = useBooking();
  const { setQueryParams } = useQueryParams();
  const selectedService = state.services.find((service) => service.id === data?.id);
  const selectedAssignment = state.master?.service_masters?.find(
    (assignment) => assignment.service_id === data?.id
  );
  const [isModalOpen, openModal, closeModal] = useModal();
  const isSelected = !!selectedService;
  const hasFAQs = data && data.service_faqs?.length > 0;
  const handleButtonClick = (trigger: HTMLElement) => {
    if (data) {
      if (data?.service_extras?.length && onCardClick) {
        onCardClick(trigger);
      } else {
        const servicePayload: BookingService = { ...data };
        if (state.master) {
          servicePayload.master = {
            ...state.master,
            service_master:
              state.master?.service_masters?.find((item) => item.service_id === data.id) || null,
          };
        }
        dispatch({
          type: Types.SelectService,
          payload: servicePayload,
        });
        if (!isBookingPage) {
          router.push(`/shops/${params.id}/booking?offerId=${data.id}`);
          return;
        }
        setQueryParams({ offerId: data.id });
      }
    }
  };
  return (
    <>
      <div
        role="button"
        tabIndex={0}
        onClick={(event) => onCardClick?.(event.currentTarget)}
        onKeyDown={(e) => {
          if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            onCardClick?.(e.currentTarget);
          }
        }}
        className="aa-s2-service-row w-full text-start border-t border-gray-link hover:bg-gray-link active:bg-gray-card transition-all cursor-pointer"
      >
        <ServiceImage src={data?.img} alt={data?.translation?.title || "Service"} />
        <div className="aa-s2-service-info">
          <div className="aa-s2-service-titleline">
            <p className="text-lg font-bold">{data?.translation?.title}</p>
            {hasFAQs && (
              <button
                type="button"
                aria-label="Show service details"
                className="ml-2"
                onClick={(e) => {
                  e.stopPropagation();
                  openModal();
                }}
              >
                <QuestionLineIcon />
              </button>
            )}
          </div>
          <span className="aa-s2-service-description">{data?.translation?.description}</span>
          <div className="aa-s2-service-meta">
            <span>
              {selectedAssignment?.interval ?? data?.interval} <Translate value="min" />
            </span>
            {data?.type && <span><Translate value={data.type} /></span>}
          </div>
        </div>
        <div className="aa-s2-service-purchase">
          <span className="aa-s2-service-price">
            <Price
              number={
                (data?.min_price ?? data?.price ?? 0) +
                (selectedService?.selected_service_extra?.price || 0)
              }
              groupThousands
            />
          </span>
          <IconButton
            aria-label={isSelected ? "Remove selected service" : "Select service"}
            onClick={(e) => {
              e.stopPropagation();
              handleButtonClick(e.currentTarget);
            }}
          >
            {isSelected ? <CheckOutlinedIcon /> : <PlusOutlinedIcon />}
          </IconButton>
        </div>
      </div>
      {hasFAQs && data.service_faqs?.length > 0 && (
        <Modal withCloseButton isOpen={isModalOpen} onClose={closeModal}>
          <ServiceFaqs faqs={data.service_faqs} />
        </Modal>
      )}
    </>
  );
};

const SERVICE_PLACEHOLDER = "/img/agendaally-service-placeholder.svg";
const renderableImageSource = (source?: string | null) =>
  source && (source.startsWith("/") || /^https?:\/\//.test(source)) ? source : undefined;

const ServiceImage = ({
  src,
  alt,
}: {
  src?: string | null;
  alt: string;
}) => {
  const serviceSource = renderableImageSource(resolveServiceMediaUrl(src));
  const [currentSource, setCurrentSource] = useState(serviceSource || SERVICE_PLACEHOLDER);
  const [failed, setFailed] = useState(false);

  return (
    <div className="aa-s2-service-media">
      {failed ? (
        <div className="aa-s2-service-fallback" role="img" aria-label="Service image unavailable">
          <span aria-hidden="true" />
        </div>
      ) : (
        <Image
          src={currentSource}
          alt={alt}
          fill
          sizes="(max-width: 700px) 92px, 120px"
          className="aa-s2-service-image"
          onError={() => {
            if (currentSource !== SERVICE_PLACEHOLDER) {
              setCurrentSource(SERVICE_PLACEHOLDER);
            } else {
              setFailed(true);
            }
          }}
        />
      )}
    </div>
  );
};
