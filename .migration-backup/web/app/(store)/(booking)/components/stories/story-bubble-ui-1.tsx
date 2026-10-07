import { Story } from "@/types/story";
import { ImageWithFallBack } from "@/components/image";
import { useStories } from "./stories.provider";
import { Types } from "./stories.reducer";

interface StoryBubbleProps {
  stories: Story[];
  isPost?: boolean;
  storyIndex: number;
}

export const StoryBubbleUi1 = ({ stories, isPost, storyIndex }: StoryBubbleProps) => {
  const { dispatch } = useStories();
  const leadStory = stories?.[0];
  const businessName = leadStory?.title?.trim() || "Local business";
  const handleClick = () => {
    dispatch({
      type: Types.ToggleModal,
      payload: { storyIndex },
    });
  };

  return (
    <button
      type="button"
      aria-label={`View updates from ${businessName}`}
      data-testid={`button-business-story-${leadStory?.shop_id ?? storyIndex}`}
      className="group flex h-[168px] w-[112px] flex-col overflow-hidden rounded-lg border border-[#ded8cd] bg-[#fbf8f1] text-left shadow-[0_2px_8px_rgba(47,42,35,0.06)] transition-transform duration-200 hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#946b3d] motion-reduce:transform-none lg:h-[266px] lg:w-[168px]"
      onClick={!isPost ? handleClick : () => null}
      disabled={!!isPost}
    >
      <div className="relative h-[140px] w-full shrink-0 overflow-hidden bg-[#e8e1d5] lg:h-[210px]">
        <ImageWithFallBack
          src={leadStory?.url}
          alt={leadStory?.model_title || `Latest update from ${businessName}`}
          fill
          sizes="(min-width: 1024px) 168px, 112px"
          className="object-cover transition-transform duration-300 group-hover:scale-[1.025] motion-reduce:transform-none"
        />
      </div>
      <div className="flex h-7 min-w-0 w-full items-center gap-1.5 px-1.5 lg:h-14 lg:gap-2 lg:px-3">
        {leadStory?.logo_img ? (
          <ImageWithFallBack
            src={leadStory.logo_img}
            alt=""
            width={32}
            height={32}
            className="h-5 w-5 shrink-0 rounded-full border border-[#ded8cd] bg-[#f5f0e6] object-cover lg:h-8 lg:w-8"
          />
        ) : (
          <span aria-hidden="true" className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#e6ddce] text-[9px] font-semibold text-[#5d4932] lg:h-8 lg:w-8 lg:text-xs">
            {businessName.slice(0, 1).toLocaleUpperCase()}
          </span>
        )}
        <span className="min-w-0 truncate text-[10px] font-semibold leading-tight text-[#28251f] lg:text-sm">
          {businessName}
        </span>
      </div>
    </button>
  );
};
