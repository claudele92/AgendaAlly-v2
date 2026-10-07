"use client";

import { Translate } from "@/components/translate";
import Link from "next/link";
import Image from "next/image";
import { Disclosure } from "@headlessui/react";
import AnchorDownIcon from "@/assets/icons/anchor-down";
import clsx from "clsx";
import { footerCopyright, footerDestination } from "@/utils/footer-settings";
import useAddressStore from "@/global-store/address";
import useUserStore from "@/global-store/user";
import { Stage2Brand } from "@/components/stage2";
import { useLogo } from "@/hook/use-logo";
import type { ReactNode } from "react";
import "./footer.css";

interface FooterProps {
  settings?: Record<string, string>;
}

export const Footer = ({ settings }: FooterProps) => {
  const logo = useLogo(settings);
  const appStoreUrl = footerDestination(settings?.customer_app_ios, "ios");
  const playStoreUrl = footerDestination(settings?.customer_app_android, "android");
  const socialLinks = [
    { label: "Instagram", icon: "ri-instagram-line", url: footerDestination(settings?.instagram, "instagram") },
    { label: "Facebook", icon: "ri-facebook-box-fill", url: footerDestination(settings?.facebook, "facebook") },
    { label: "X", icon: "ri-twitter-x-fill", url: footerDestination(settings?.twitter, "twitter") },
    { label: "LinkedIn", icon: "ri-linkedin-box-fill", url: footerDestination(settings?.linkedin, "linkedin") },
    { label: "TikTok", icon: "ri-tiktok-fill", url: footerDestination(settings?.tiktok, "tiktok") },
  ].filter((social): social is { label: string; icon: string; url: string } => Boolean(social.url));
  const hasCustomerAppLinks = Boolean(appStoreUrl || playStoreUrl);
  const country = useAddressStore((state) => state.country);
  const city = useAddressStore((state) => state.city);
  const user = useUserStore((state) => state.user);
  const location = [city?.translation?.title, country?.translation?.title].filter(Boolean).join(", ");

  return (
    <div className="aa-s2-native aa-s2-native-footer-region">
      <footer className="aa-s2-footer">
        <div className="aa-s2-wrap">
          <div className={clsx("aa-s2-footer-main", !socialLinks.length && "aa-s2-footer-main-no-social")}>
            <div className="aa-s2-footer-brand-column">
              <Link href="/" aria-label="AgendaAlly home">
                <Stage2Brand logo={logo} />
              </Link>
              <p className="aa-s2-footer-tagline">
                Book local expertise. Shop local businesses.
              </p>
              {settings?.description && <p>{settings.description}</p>}
              {location && <p className="aa-s2-footer-location">Your location: {location}</p>}
              {hasCustomerAppLinks && (
                <div>
                  <p>Download the AgendaAlly app</p>
                  <div className="aa-s2-footer-downloads">
                  {appStoreUrl && (
                    <Link href={appStoreUrl} target="_blank" rel="noopener noreferrer">
                      <Image src="/img/apple_store.png" alt="Download on the App Store" width={147} height={55} />
                    </Link>
                  )}
                  {playStoreUrl && (
                    <Link href={playStoreUrl} target="_blank" rel="noopener noreferrer">
                      <Image src="/img/play_market.png" alt="Get it on Google Play" width={147} height={55} />
                    </Link>
                  )}
                  </div>
                </div>
              )}
            </div>

            <FooterGroup title="Discover">
              <Link href="/services"><Translate value="services" /></Link>
              <Link href="/shops"><Translate value="shops" /></Link>
              <Link href="/products"><Translate value="products" /></Link>
              <Link href="/masters">Specialists</Link>
              <Link href="/cart"><Translate value="cart" /></Link>
              <Link href="/blogs"><Translate value="blog" /></Link>
            </FooterGroup>
            <FooterGroup title="Your Account">
              <Link href={user ? "/appointments" : "/login?redirect=%2Fappointments"}><Translate value="my.appointments" /></Link>
              <Link href={user ? "/orders" : "/login?redirect=%2Forders"}><Translate value="orders" /></Link>
              <Link href={user ? "/liked-shops" : "/login?redirect=%2Fliked-shops"}><Translate value="favorites" /></Link>
            </FooterGroup>
            <FooterGroup title="Information">
              <Link href="/for-business"><Translate value="for.business" /></Link>
              <Link href="/about"><Translate value="about.company" /></Link>
              <Link href="/contact"><Translate value="contact.us" /></Link>
              <Link href="/faq"><Translate value="faqs.short" /></Link>
              <Link href="/terms"><Translate value="terms" /></Link>
              <Link href="/privacy"><Translate value="privacy.policy" /></Link>
              <Link href="/refund-cancellation">Refund &amp; Cancellation Policy</Link>
            </FooterGroup>

            {socialLinks.length > 0 && <div className="aa-s2-footer-social">
              <h3>Follow Us</h3>
              <div className="aa-s2-footer-col aa-s2-footer-social-list">
                {socialLinks.map(({ label, icon, url }) => (
                    <a
                      href={url}
                      key={label}
                      target="_blank"
                      rel="noopener noreferrer"
                      aria-label={`Follow AgendaAlly on ${label} (opens in a new tab)`}
                      data-testid={`link-footer-social-${label.toLowerCase()}`}
                    >
                       <i className={`${icon} aa-s2-footer-social-icon`} aria-hidden="true" />
                       <span>{label}</span>
                    </a>
                ))}
              </div>
            </div>}
          </div>
          <div className="aa-s2-footer-bottom">
            <span>{footerCopyright(settings?.footer_text, new Date().getFullYear())}</span>
            <span><Link href="/terms"><Translate value="terms" /></Link>{" · "}
              <Link href="/privacy"><Translate value="privacy.policy" /></Link>{" · "}
              <Link href="/refund-cancellation">Refund &amp; Cancellation Policy</Link></span>
          </div>
        </div>
      </footer>
    </div>
  );
};

const FooterGroup = ({ title, children }: { title: string; children: ReactNode }) => (
  <div className="aa-s2-footer-group">
    <Disclosure as="div" defaultOpen>
      {({ open }) => (
        <>
          <Disclosure.Button
            as="button"
            className={clsx("aa-s2-footer-heading", !open && "is-closed")}
          >
            <span>{title}</span>
            <AnchorDownIcon
              className={clsx(open && "rotate-180 transform", "aa-s2-footer-chevron")}
            />
          </Disclosure.Button>
          <Disclosure.Panel>
            <div className="aa-s2-footer-col">{children}</div>
          </Disclosure.Panel>
        </>
      )}
    </Disclosure>
  </div>
);
