import { useInfiniteQuery } from "@tanstack/react-query";
import { masterService } from "@/services/master";
import { MasterCard } from "@/components/master-card";
import { extractDataFromPagination } from "@/utils/extract-data";
import dynamic from "next/dynamic";
import { InfiniteLoader } from "@/components/infinite-loader";
import { Master } from "@/types/master";
import { useSettings } from "@/hook/use-settings";
import { useShopLocationParams } from "@/hook/use-shop-location-params";

const Empty = dynamic(() =>
  import("@/components/empty").then((component) => ({ default: component.Empty }))
);

interface StaffSelectProps {
  shopId?: number;
  serviceTitle?: string;
  selectedMasterId?: number;
  onSelect: (value: Master) => void;
  serviceId?: number;
}

export const StaffSelect = ({
  shopId,
  serviceTitle,
  selectedMasterId,
  onSelect,
  serviceId,
}: StaffSelectProps) => {
  const { currency } = useSettings();
  const locationParams = useShopLocationParams();
  const {
    data: masters,
    hasNextPage,
    fetchNextPage,
    isFetchingNextPage,
  } = useInfiniteQuery(
    ["masters", shopId, serviceId, locationParams],
    () =>
      masterService.list({
        shop_id: shopId,
        service_id: serviceId,
        currency_id: currency?.id,
        ...locationParams,
      }),
    {
      getNextPageParam: (lastPage) => lastPage.links.next && lastPage.meta.current_page + 1,
      suspense: true,
    }
  );

  const masterList = extractDataFromPagination(masters?.pages);
  return (
    <div className="pt-10 pb-6 px-6">
      <h2 className="text-xl font-semibold">{serviceTitle}</h2>
      {masterList && masterList.length !== 0 ? (
        <InfiniteLoader hasMore={hasNextPage} loadMore={fetchNextPage} loading={isFetchingNextPage}>
          <div className="grid md:grid-cols-3 sm:grid-cols-3 grid-cols-2 gap-4 mt-6">
            {masterList?.map((master) => (
              <div
                key={master.id}
                role="button"
                tabIndex={0}
                onClick={() => onSelect(master)}
                onKeyDown={(e) => {
                  if (e.key === "Enter" || e.key === " ") {
                    e.preventDefault();
                    onSelect(master);
                  }
                }}
                className="cursor-pointer"
              >
                <MasterCard selected={selectedMasterId === master.id} data={master} />
              </div>
            ))}
          </div>
        </InfiniteLoader>
      ) : (
        <Empty animated={false} />
      )}
    </div>
  );
};
