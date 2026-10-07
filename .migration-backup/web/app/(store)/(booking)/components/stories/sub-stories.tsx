import ArrowLeftSLineIcon from "remixicon-react/ArrowLeftSLineIcon";
import ArrowRightSLineIcon from "remixicon-react/ArrowRightSLineIcon";
import { Story } from "@/types/story";
import { storyTiming } from "@/config/global";
import { useSwiper } from "swiper/react";
import { Button } from "@/components/button";
import Link from "next/link";
import { useTranslation } from "react-i18next";
import { useEffect, useState } from "react";
import { useCountDown } from "@/hook/use-countdown";
import { StoryLine } from "./story-line";
import { StoryHeader } from "./story-header";
import { SingleStory } from "./story";
import { useStories } from "./stories.provider";
import { Types } from "./stories.reducer";

interface SubStoriesProps {
  stories: Story[];
  onCloseModal: () => void;
  mainStoriesLength: number;
  hasPrev: boolean;
  hasNext: boolean;
}

export const SubStories = ({
  stories,
  onCloseModal,
  mainStoriesLength,
  hasPrev,
  hasNext,
}: SubStoriesProps) => {
  const { t } = useTranslation();
  const mainSwiper = useSwiper();
  const { dispatch, state } = useStories();
  const { counter: time, pause, reset, start } = useCountDown(storyTiming);
  const [imageLoaded, setImageLoaded] = useState(false);
  const [reducedMotion, setReducedMotion] = useState(false);
  const currentStory = stories[state.sub];
  const currentStoryModelType = currentStory?.model_type?.split("\\")?.pop()?.toLowerCase();

  const getUrl = () => {
    switch (currentStoryModelType) {
      case "shop":
        return currentStory?.shop_slug ? `/shops/${currentStory.shop_slug}` : null;
      case "product":
        return currentStory?.model_uuid ? `/products/${currentStory.model_uuid}` : null;
      case "service":
        return currentStory?.shop_slug && currentStory?.model_uuid
          ? `/shops/${currentStory.shop_slug}/booking?serviceId=${currentStory.model_uuid}`
          : null;
      default:
        return null;
    }
  };
  const destination = getUrl();

  const handleSwipeNext = () => {
    if (state.sub < stories.length - 1) {
      dispatch({ type: Types.ChangeSubStoryIndex, payload: state.sub + 1 });
    } else if (mainStoriesLength > mainSwiper?.realIndex) {
      mainSwiper?.slideNext();
      dispatch({ type: Types.ChangeMainStoryIndex, payload: state.main + 1 });
    } else if (mainStoriesLength === mainSwiper?.realIndex) {
      dispatch({ type: Types.ToggleModal, payload: { storyIndex: -1 } });
    }
  };

  const handleSwipePrev = () => {
    if (state.sub > 0 && stories.length !== 1) {
      dispatch({ type: Types.ChangeSubStoryIndex, payload: state.sub - 1 });
      if (!reducedMotion) reset();
      else pause();
    } else if (mainStoriesLength >= mainSwiper?.realIndex) {
      mainSwiper?.slidePrev();
      dispatch({ type: Types.ChangeMainStoryIndex, payload: state.main - 1 });
    }
  };

  useEffect(() => {
    const preference = window.matchMedia("(prefers-reduced-motion: reduce)");
    const updatePreference = () => {
      setReducedMotion(preference.matches);
      if (preference.matches) pause();
    };
    updatePreference();
    preference.addEventListener("change", updatePreference);
    return () => preference.removeEventListener("change", updatePreference);
  }, []);

  useEffect(() => {
    if (imageLoaded && !reducedMotion) {
      start();
    }
  }, [state.main, state.sub, imageLoaded, reducedMotion]);

  useEffect(() => {
    if (!time && !reducedMotion) {
      handleSwipeNext();
      reset();
    }
  }, [time, reducedMotion]);

  useEffect(() => {
    setImageLoaded(false);
  }, [state.main, state.sub]);

  return (
    <>
      <div className="flex items-center gap-2.5 absolute top-0 left-0 w-full z-10 p-2">
        {Array.from(Array(stories.length).keys()).map((step, idx) => (
          <StoryLine
            time={time}
            lineIdx={idx}
            key={step}
            currentIdx={state.sub}
            isBefore={state.sub > idx}
          />
        ))}
      </div>
      <StoryHeader story={stories[state.sub]} onClose={onCloseModal} />
      <SingleStory data={stories[state.sub]} onLoad={() => setImageLoaded(true)} />

      {hasPrev && (
        <button
          type="button"
          className="absolute bottom-0 left-0 z-10 flex h-4/5 w-1/2 items-center justify-start px-3 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white md:bottom-1/2 md:left-[-3.5rem] md:h-12 md:w-12 md:translate-y-1/2 md:items-center md:justify-center md:rounded-full md:border md:bg-[#484848]"
          aria-label={t("previous")}
          onClick={() => {
            if (reducedMotion) pause();
            handleSwipePrev();
          }}
        >
          <ArrowLeftSLineIcon size={30} />
        </button>
      )}
      {hasNext && (
        <button
          type="button"
          className="absolute bottom-0 right-0 z-10 flex h-4/5 w-1/2 items-center justify-end px-3 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white md:bottom-1/2 md:right-[-3.5rem] md:h-12 md:w-12 md:translate-y-1/2 md:items-center md:justify-center md:rounded-full md:border md:bg-[#484848]"
          aria-label={t("next")}
          onClick={() => {
            if (reducedMotion) pause();
            handleSwipeNext();
          }}
        >
          <ArrowRightSLineIcon size={30} />
        </button>
      )}
      {destination && (
        <div className="absolute bottom-0 left-0 z-10 w-full p-2">
          <Button
            as={Link}
            onClick={onCloseModal}
            href={destination}
            fullWidth
          >
            {t(`view.${currentStoryModelType}`)}
          </Button>
        </div>
      )}
    </>
  );
};
