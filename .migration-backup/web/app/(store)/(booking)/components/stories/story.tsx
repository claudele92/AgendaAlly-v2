import { Story } from "@/types/story";
import { ImageWithFallBack } from "@/components/image";
import { memo } from "react";

export const SingleStory = memo(({ data, onLoad }: { data: Story; onLoad: () => void }) => (
  <div className="relative h-full bg-[#171613]">
    <div className="pointer-events-none absolute z-[3] h-full w-full bg-gradient-to-t from-black/45 via-transparent to-black/35" />
    <div className="relative h-full w-full">
      <ImageWithFallBack
        src={data.url}
        fill
        sizes="(min-width: 640px) 512px, 100vw"
        className="object-contain"
        alt={data.model_title || data.title || "Story"}
        onLoad={() => onLoad()}
      />
    </div>
  </div>
));
