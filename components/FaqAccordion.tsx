"use client";

import { useState } from "react";

const FAQS = [
  {
    q: "Can we request trial bioplastic sample rolls?",
    a: "Yes. We provide 5kg trial pellets and 100-meter sample reels for test extrusion on standard blow-molding and heat-sealing machinery.",
  },
  {
    q: "How long does home composting take?",
    a: "Under normal backyard composting conditions (humidity >50%, soil microbes), our film decomposes within 90 to 180 days with no toxic residue.",
  },
  {
    q: "Do you export pellets globally?",
    a: "We ship directly via Tema Port to Europe, North America, and across the African Continental Free Trade Area (AfCFTA) with full Phytosanitary certification.",
  },
];

export default function FaqAccordion() {
  const [open, setOpen] = useState<number | null>(null);

  return (
    <section className="px-layout-margin-mobile lg:px-layout-margin-desktop py-space-sm max-w-[1240px] mx-auto w-full">
      <div className="w-full bg-surface-container-lowest p-space-md rounded-xl shadow-sm flex flex-col gap-space-sm">
        <div className="flex items-center gap-space-2xs">
          <span className="material-symbols-outlined text-secondary text-[20px]">help_center</span>
          <h3 className="font-headline-sm text-headline-sm text-on-surface">
            Quick Procurement Answers
          </h3>
        </div>
        {FAQS.map((faq, i) => {
          const isOpen = open === i;
          return (
            <div
              key={faq.q}
              className="bg-surface-container-low rounded-lg p-space-sm cursor-pointer"
              onClick={() => setOpen(isOpen ? null : i)}
            >
              <div className="flex items-center justify-between">
                <span className="font-label-md text-label-md text-on-surface">{faq.q}</span>
                <span
                  className={`material-symbols-outlined text-primary text-[20px] transition-transform duration-200 ${
                    isOpen ? "rotate-45" : ""
                  }`}
                >
                  add
                </span>
              </div>
              {isOpen && (
                <div className="mt-2 pt-2 text-on-surface-variant font-body-sm text-body-sm">
                  {faq.a}
                </div>
              )}
            </div>
          );
        })}
      </div>
    </section>
  );
}
