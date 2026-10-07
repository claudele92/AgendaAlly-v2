"use client";;
import { use } from "react";

import { useRouter } from "next/navigation";
import { Modal } from "@/components/modal";
import { useQuery } from "@tanstack/react-query";
import { blogService } from "@/services/blog";
import { NewsContent } from "@/app/(store)/@detail/(.)news/[id]/content";
import { NewsContentLoading } from "@/app/(store)/@detail/(.)news/[id]/content-loading";
import { useSettings } from "@/hook/use-settings";

const NewsDetailModal = (props: { params: Promise<{ id: string }> }) => {
  const params = use(props.params);
  const router = useRouter();
  const { language } = useSettings();
  const { data, isLoading } = useQuery(["news", params.id], () =>
    blogService.get(params.id, { lang: language?.locale })
  );
  return (
    <Modal withCloseButton isOpen onClose={() => router.back()}>
      {isLoading ? <NewsContentLoading /> : <NewsContent data={data?.data} />}
    </Modal>
  );
};

export default NewsDetailModal;
