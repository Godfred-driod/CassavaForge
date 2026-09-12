"use client";

import { useState } from "react";

const ROWS: [string, string, string][] = [
  ["Feedstock Source", "Manihot esculenta (Cassava)", "Petroleum Naphtha"],
  ["Tensile Modulus (MPa)", "280 - 450", "200 - 400"],
  ["Elongation at Break (%)", "320 - 480%", "400 - 600%"],
  ["Industrial Compost", "< 90 days", "Non-degradable (400+ yrs)"],
];

export default function TechSpecsModal() {
  const [open, setOpen] = useState(false);

  return (
    <>
      <button
        className="inline-flex items-center justify-center bg-surface-container text-on-surface hover:bg-surface-container-highest font-label-md text-label-md px-space-lg py-space-sm rounded-full transition-all"
        onClick={() => setOpen(true)}
        type="button"
      >
        <span className="material-symbols-outlined text-[18px] mr-1.5 text-primary">science</span>
        Technical Spec Sheet
      </button>

      {open && (
        <div
          className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4"
          id="tech-specs-modal"
          onClick={() => setOpen(false)}
        >
          <div
            className="bg-surface rounded-2xl max-w-2xl w-full p-space-xl shadow-2xl relative max-h-[85vh] overflow-y-auto"
            onClick={(e) => e.stopPropagation()}
          >
            <button
              className="absolute top-4 right-4 text-on-surface-variant hover:text-on-surface"
              onClick={() => setOpen(false)}
            >
              <span className="material-symbols-outlined">close</span>
            </button>
            <div className="flex items-center gap-2 mb-4">
              <span className="material-symbols-outlined text-primary text-[28px]">biotech</span>
              <h3 className="font-headline-md text-on-surface">
                Cassava Resin Specification Summary
              </h3>
            </div>
            <p className="font-body-sm text-on-surface-variant mb-6">
              Preliminary material property comparison between CassavaForge Native Resin CF-100
              and standard low-density polyethylene (LDPE).
            </p>
            <div className="overflow-x-auto">
              <table className="w-full text-left text-body-sm">
                <thead className="bg-surface-container-high text-on-surface font-label-caps uppercase">
                  <tr>
                    <th className="p-3">Parameter</th>
                    <th className="p-3 text-primary font-bold">CassavaForge CF-100</th>
                    <th className="p-3 text-on-surface-variant">Standard LDPE</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-surface-container">
                  {ROWS.map(([param, cf, ldpe]) => (
                    <tr key={param}>
                      <td className="p-3 font-semibold">{param}</td>
                      <td className="p-3 text-primary font-medium">{cf}</td>
                      <td className="p-3 text-on-surface-variant">{ldpe}</td>
                    </tr>
                  ))}
                  <tr>
                    <td className="p-3 font-semibold">Marine Toxicity</td>
                    <td className="p-3 text-primary font-medium">Non-toxic, bio-assimilable</td>
                    <td className="p-3 text-error font-medium">Microplastic hazardous</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div className="mt-6 flex justify-end">
              <button
                className="px-5 py-2 rounded-full bg-primary text-on-primary font-label-md"
                onClick={() => setOpen(false)}
              >
                Close Window
              </button>
            </div>
          </div>
        </div>
      )}
    </>
  );
}
