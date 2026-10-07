import CrossIcon from "@/assets/icons/cross";
import { IconButton } from "@/components/icon-button";
import { ImageWithFallBack } from "@/components/image";
import { Story } from "@/types/story";
import dayjs from "dayjs";
import Link from "next/link";
import relativeTime from "dayjs/plugin/relativeTime";

interface StoryHeaderProps {
  story: Story;
  onClose: () => void;
}

dayjs.extend(relativeTime);

export const StoryHeader = ({ story, onClose }: StoryHeaderProps) => {
  const identity = (
    <div className="flex items-center gap-2.5">
      {story.logo_img && (
        <div className="rounded-full bg-white p-0.5">
          <ImageWithFallBack
            src={story.logo_img}
            alt=""
            width={40}
            height={40}
            className="aspect-square h-10 w-10 rounded-full object-contain"
          />
        </div>
      )}
      <div className="flex flex-col">
        <span className="text-sm font-medium text-white">{story.title}</span>
        <span className="text-xs text-gray-100">{dayjs(story.created_at).fromNow()}</span>
      </div>
    </div>
  );

  return (
    <div className="p-2 absolute top-4 left-0 z-10 w-full">
      <div className="flex items-center justify-between ">
        {story.shop_slug ? (
          <Link
            href={`/shops/${story.shop_slug}`}
            aria-label={`Visit ${story.title}`}
            className="flex min-h-11 items-center gap-2.5 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
          >
            {identity}
          </Link>
        ) : (
          <div className="flex min-h-11 items-center">{identity}</div>
        )}
        <div className="flex items-center gap-2.5">
          <IconButton
            size="small"
            className="h-11 w-11 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-white"
            aria-label="Close business update"
            onClick={onClose}
          >
            <CrossIcon />
          </IconButton>
        </div>
      </div>
    </div>
  );
};
