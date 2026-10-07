"use client";

import { useModal } from "@/hook/use-modal";
import { IconButton } from "@/components/icon-button";
import { Drawer } from "@/components/drawer";
import dynamic from "next/dynamic";
import Menu2Icon from "@/assets/icons/menu-2";
import { ConfirmModal } from "@/components/confirm-modal";
import React, { useEffect, useState } from "react";
import { useQueryClient } from "@tanstack/react-query";
import { useAuth } from "@/hook/use-auth";
import { useTranslation } from "react-i18next";

const ProfileSidebar = dynamic(() => import("./sidebar"));

const MobileSidebar = ({ isHidden = true }: { isHidden?: boolean }) => {
  const queryClient = useQueryClient();
  const [isDrawerOpen, openDrawer, closeDrawer] = useModal();
  const isLoggingOut = Boolean(queryClient.isMutating({ mutationKey: ["logout"] }));
  const { logOut } = useAuth();
  const { t, i18n } = useTranslation();
  const [isLogoutModalOpen, setIsLogoutModalOpen] = useState(false);
  const [drawerPosition, setDrawerPosition] = useState<"left" | "right">("left");

  useEffect(() => {
    setDrawerPosition(document.documentElement.dir === "rtl" ? "right" : "left");
  }, [i18n.language]);

  const handleLogout = async () => {
    await logOut();
  };
  return (
    <div className={`${isHidden ? "lg:hidden" : ""}`}>
      <div className="relative z-[9]">
        <IconButton
          aria-label={t(isDrawerOpen ? "close.navigation" : "open.navigation", {
            defaultValue: isDrawerOpen ? "Close navigation" : "Open navigation",
          })}
          aria-expanded={isDrawerOpen}
          onClick={() => (isDrawerOpen ? closeDrawer() : openDrawer())}
          size="small"
          rounded
          className="aa-nav-menu-button"
        >
          <Menu2Icon />
        </IconButton>
      </div>
      <Drawer
        withCloseIcon={false}
        container={false}
        position={drawerPosition}
        open={isDrawerOpen}
        onClose={closeDrawer}
      >
        <ProfileSidebar
          inDrawer
          onClose={closeDrawer}
          onLogoutButtonClick={() => setIsLogoutModalOpen(true)}
        />
      </Drawer>
      <ConfirmModal
        loading={isLoggingOut}
        isOpen={isLogoutModalOpen}
        text="are.you.sure.want.to.logout"
        onCancel={() => setIsLogoutModalOpen(false)}
        onConfirm={handleLogout}
        confirmText="logout"
      />
    </div>
  );
};

export default MobileSidebar;
