"use client";

import ChevronRightIcon from "@/assets/icons/chevron-right";
import { Faq } from "@/types/info";
import { Disclosure, Transition } from "@headlessui/react";
import clsx from "clsx";
import React from "react";

interface QaProps {
  data: Faq;
}

export const Qa = ({ data }: QaProps) => (
  <Disclosure>
    {({ open }) => (
      <div
        className={clsx(
          "w-full overflow-hidden rounded-2xl border border-[#e8e3d9] bg-[#fffefa] shadow-sm dark:border-gray-bold dark:bg-darkBgUi3",
          open && "border-[#c7aa87]"
        )}
      >
        <Disclosure.Button className="flex min-h-[64px] w-full items-center justify-between gap-4 px-5 py-5 text-start text-base font-semibold leading-relaxed text-[#26241f] hover:bg-[#fbfaf7] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-[#956a42] dark:text-white dark:hover:bg-[#252a2f] md:px-6">
          <span>{data.translation?.question}</span>
          <div
            aria-hidden="true"
            className={clsx(
              "shrink-0 text-2xl font-normal text-[#715033] transition-transform dark:text-amber-200",
              open ? "rotate-90" : "rotate-0"
            )}
          >
            <ChevronRightIcon />
          </div>
        </Disclosure.Button>
        <Transition
          show={open}
          enter="transition ease duration-500 transform"
          enterFrom="opacity-0 -translate-y-12"
          enterTo="opacity-100 translate-y-0"
          leave="transition ease duration-300 transform"
          leaveFrom="opacity-100 translate-y-0"
          leaveTo="opacity-0 -translate-y-12"
        >
          <Disclosure.Panel static>
            <div
              className="border-t border-[#c9baa4] bg-[#f5f1e9] px-5 py-5 text-base leading-relaxed text-[#302d27] dark:border-[#5e6369] dark:bg-[#25292e] dark:text-gray-100 md:px-6 [&_a]:font-medium [&_a]:text-[#715033] [&_a]:underline [&_a]:underline-offset-4 [&_li]:mb-2 [&_ol]:my-3 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:mb-3 [&_p:last-child]:mb-0 [&_ul]:my-3 [&_ul]:list-disc [&_ul]:pl-6"
              dangerouslySetInnerHTML={{ __html: data.translation?.answer || "" }}
            >
            </div>
          </Disclosure.Panel>
        </Transition>
      </div>
    )}
  </Disclosure>
);
