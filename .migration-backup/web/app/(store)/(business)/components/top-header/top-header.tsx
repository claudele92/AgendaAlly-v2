"use client";

import { useTranslation } from "react-i18next";
import { Button } from "@/components/button";
import Link from "next/link";
import Image from "next/image";
import useUserStore from "@/global-store/user";
import styles from "../../for-business/for-business.module.css";

interface TopHeaderProps {
  title: string;
  description: string;
  buttonText: string;
  link: string;
  variant?: "default" | "stage2";
}

export const TopHeader = ({
  title,
  description,
  link,
  buttonText,
  variant = "default",
}: TopHeaderProps) => {
  const { t } = useTranslation();
  const user = useUserStore((state) => state.user);

  if (variant === "stage2") {
    return (
      <div className={styles.heroCopy}>
        <span className={styles.eyebrow}>AgendaAlly for Business</span>
        <h1>{title}</h1>
        <p>{description}</p>
        <div className={styles.heroActions}>
          <Button as={Link} href={user ? link : "/login"} className={styles.primaryAction}>
            {t(buttonText)}
            <span aria-hidden="true">→</span>
          </Button>
          <Link href="/" className={styles.secondaryAction}>
            Explore the marketplace
          </Link>
        </div>
      </div>
    );
  }

  return (
    <section className="flex items-center justify-center flex-col text-center xl:container px-4 md:pb-24 pb-16 relative pt-12 gap-4">
      <div className="absolute md:-top-1/4 top-0 left-0  w-[425px] h-[245px] scale-150 z-[-1]">
        <Image src="/img/fb_ellipse.png" alt="fb_ellipse" fill className="object-contain" />
      </div>
      <div className="absolute -top-1/2 md:right-72 right-0 md:w-[425px] w-[200px] h-[245px] scale-150 z-[-1]">
        <Image src="/img/fb_ellipse1.png" alt="fb_ellipse" fill className="object-contain" />
      </div>
      <h1 className="md:text-[65px] text-3xl font-semibold break-words">{t(title)}</h1>
      <span className="md:text-xl text-sm">{t(description)}</span>
      <Button as={Link} href={user ? link : "/login"} className="md:mt-10 mt-4">
        {t(buttonText)}
      </Button>
    </section>
  );
};
