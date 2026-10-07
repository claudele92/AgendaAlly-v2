"use client";

import { Swiper, SwiperSlide } from "swiper/react";
import dynamic from "next/dynamic";
import { Story } from "@/types/story";
import { StoryPortal } from "./stories-portal";
import StoriesProvider from "./stories.provider";
import { useState } from "react";
import type { Swiper as SwiperInstance } from "swiper";

const buttons = {
  "1": dynamic(() =>
    import("./story-bubble-ui-1").then((component) => ({ default: component.StoryBubbleUi1 }))
  ),
  "2": dynamic(() =>
    import("./story-bubble").then((component) => ({ default: component.StoryBubble }))
  ),
};

interface StoriesProps {
  buttonVariant?: keyof typeof buttons;
  data?: Story[][];
}

const Stories = ({ buttonVariant = "1", data }: StoriesProps) => {
  const [rail, setRail] = useState<SwiperInstance | null>(null);
  const [edges, setEdges] = useState({ start: true, end: true });
  const ButtonUi = buttons[buttonVariant];
  if (!data?.length) {
    return null;
  }
  return (
    <div
      role="region"
      aria-label="Business updates"
      className="my-4 min-w-0 max-w-full overflow-hidden md:mb-2"
    >
      <StoriesProvider>
        {data.length > 1 && (
          <div className="mb-3 flex items-center justify-end gap-2">
            <span className="mr-auto text-xs text-[#665c50]">Swipe to browse business updates</span>
            <button type="button" aria-label="Previous businesses" disabled={edges.start}
              onClick={() => rail?.slidePrev()}
              className="h-11 w-11 rounded-md border border-[#ded8cd] text-[#5d4932] disabled:opacity-40 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#946b3d]">←</button>
            <button type="button" aria-label="Next businesses" disabled={edges.end}
              onClick={() => rail?.slideNext()}
              className="h-11 w-11 rounded-md border border-[#ded8cd] text-[#5d4932] disabled:opacity-40 focus-visible:outline focus-visible:outline-2 focus-visible:outline-[#946b3d]">→</button>
          </div>
        )}
        <Swiper
          onSwiper={(instance) => {
            setRail(instance);
            setEdges({ start: instance.isBeginning, end: instance.isEnd });
          }}
          onSlideChange={(instance) => setEdges({ start: instance.isBeginning, end: instance.isEnd })}
          onResize={(instance) => setEdges({ start: instance.isBeginning, end: instance.isEnd })}
          slidesPerView="auto"
          spaceBetween={16}
          className="!max-w-full !px-0"
        >
          {data?.map((stories, idx) => (
            <SwiperSlide
              className="!z-[2] !h-auto !w-[112px] lg:!w-[168px]"
              key={stories[0]?.shop_id ?? idx}
            >
              <ButtonUi storyIndex={idx} stories={stories} />
            </SwiperSlide>
          ))}
        </Swiper>
        <StoryPortal stories={data} />
      </StoriesProvider>
    </div>
  );
};

export default Stories;
