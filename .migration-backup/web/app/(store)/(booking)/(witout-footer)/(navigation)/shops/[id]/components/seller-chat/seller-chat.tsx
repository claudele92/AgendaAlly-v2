"use client";

import dynamic from "next/dynamic";
import useUserStore from "@/global-store/user";
import { useState } from "react";
import { Drawer } from "@/components/drawer";
import { ChatFloatButton } from "@/components/chat/chat-float-button";

const Chat = dynamic(() => import("@/components/chat"));

interface SellerChatProps {
  receiverId: number;
}

export const SellerChat = ({ receiverId }: SellerChatProps) => {
  const user = useUserStore((state) => state.user);
  const [isChatDrawerOpen, setIsChatDrawerOpen] = useState(false);

  return (
    <>
      <Drawer
        open={isChatDrawerOpen}
        onClose={() => setIsChatDrawerOpen(false)}
        container={false}
        position="right"
      >
        <Chat recieverId={receiverId} />
      </Drawer>

      {user && (
        <div className="relative ">
          <ChatFloatButton onClick={() => setIsChatDrawerOpen(true)} />
        </div>
      )}
    </>
  );
};
