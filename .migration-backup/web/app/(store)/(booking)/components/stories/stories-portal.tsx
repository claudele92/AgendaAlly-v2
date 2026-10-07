import React, { useEffect, useRef, useState } from "react";
import { Swiper, SwiperRef, SwiperSlide } from "swiper/react";
import { Story } from "@/types/story";
import { Dialog } from "@headlessui/react";
import { EffectCube } from "swiper/modules";
import { useStories } from "./stories.provider";
import { Types } from "./stories.reducer";
import "swiper/css/effect-cube";
import { SubStories } from "./sub-stories";

export const StoryPortal = ({ stories }: { stories?: Story[][] }) => {
  const mainSwiper = useRef<SwiperRef>(null);
  const { dispatch, state } = useStories();
  const [reducedMotion, setReducedMotion] = useState(false);
  const handleCloseModal = () => {
    dispatch({ type: Types.ToggleModal, payload: { storyIndex: -1 } });
  };

  useEffect(() => {
    const preference = window.matchMedia("(prefers-reduced-motion: reduce)");
    const updatePreference = () => setReducedMotion(preference.matches);
    updatePreference();
    preference.addEventListener("change", updatePreference);
    return () => preference.removeEventListener("change", updatePreference);
  }, []);

  useEffect(() => {
    if (state.main < 0) return;
    const onKeyDown = (event: KeyboardEvent) => {
      if (event.key === "ArrowRight") {
        event.preventDefault();
        const current = stories?.[state.main];
        if (current && state.sub < current.length - 1) {
          dispatch({ type: Types.ChangeSubStoryIndex, payload: state.sub + 1 });
        } else if (state.main < (stories?.length ?? 0) - 1) {
          mainSwiper.current?.swiper.slideNext();
          dispatch({ type: Types.ChangeMainStoryIndex, payload: state.main + 1 });
          dispatch({ type: Types.ChangeSubStoryIndex, payload: 0 });
        }
      }
      if (event.key === "ArrowLeft") {
        event.preventDefault();
        if (state.sub > 0) {
          dispatch({ type: Types.ChangeSubStoryIndex, payload: state.sub - 1 });
        } else if (state.main > 0) {
          mainSwiper.current?.swiper.slidePrev();
          dispatch({ type: Types.ChangeMainStoryIndex, payload: state.main - 1 });
          dispatch({
            type: Types.ChangeSubStoryIndex,
            payload: (stories?.[state.main - 1]?.length ?? 1) - 1,
          });
        }
      }
    };
    window.addEventListener("keydown", onKeyDown);
    return () => window.removeEventListener("keydown", onKeyDown);
  }, [dispatch, state.main, state.sub, stories]);

  const hasPrev = !!(stories?.[state.main]?.[state.sub - 1] || stories?.[state.main - 1]);
  const hasNext = !!(stories?.[state.main]?.[state.sub + 1] || stories?.[state.main + 1]);

  return (
    <Dialog open={state.main >= 0} unmount onClose={handleCloseModal} className="relative z-50">
      <div className="fixed inset-0 sm:bg-black/80 bg-black" aria-hidden="true" />
      <div className="fixed inset-0 flex w-screen items-center justify-center sm:p-4">
        <Dialog.Panel className="h-full w-full focus:outline-none sm:max-w-lg md:max-h-[900px]">
          <Dialog.Title className="sr-only">Business updates viewer</Dialog.Title>
          <Swiper
            ref={mainSwiper}
            effect={reducedMotion ? "slide" : "cube"}
            initialSlide={state.main}
            className="max-w-lg h-full"
            slidesPerView={1}
            modules={[EffectCube]}
            speed={reducedMotion ? 0 : 300}
            onSlideChange={(swiper) => {
              dispatch({
                type: Types.ChangeMainStoryIndex,
                payload: swiper.realIndex,
              });
              dispatch({
                type: Types.ChangeSubStoryIndex,
                payload: 0,
              });
            }}
            cubeEffect={{
              shadow: true,
              slideShadows: true,
              shadowOffset: 20,
              shadowScale: 0.94,
            }}
            watchSlidesProgress
          >
            {stories?.map((storiesItem) => (
              <SwiperSlide className="h-full" key={storiesItem[0].url}>
                {({ isActive }) =>
                  isActive &&
                  storiesItem[state.sub] &&
                  mainSwiper.current && (
                    <SubStories
                      hasPrev={hasPrev}
                      hasNext={hasNext}
                      onCloseModal={handleCloseModal}
                      stories={storiesItem}
                      mainStoriesLength={stories ? stories.length - 1 : 0}
                    />
                  )
                }
              </SwiperSlide>
            ))}
          </Swiper>
        </Dialog.Panel>
      </div>
    </Dialog>
  );
};
