"use client";

import { useState } from "react";

// Derived from the original design's reference point (25,000 kg → 42.5 t CO2 / 18,750 kg petro):
// petro avoided = 75% of volume; CO2 saved (tons) = volume * 0.0017
const PETRO_RATIO = 0.75;
const CO2_TONS_PER_KG = 0.0017;

export default function ProductOffsetCalculator() {
  const [volume, setVolume] = useState(25000);

  const co2Saved = (volume * CO2_TONS_PER_KG).toLocaleString(undefined, {
    maximumFractionDigits: 1,
  });
  const petroAvoided = Math.round(volume * PETRO_RATIO).toLocaleString();

  return (
    <section className="max-w-[1240px] mx-auto px-layout-margin-mobile lg:px-layout-margin-desktop mb-space-lg">
      <div className="p-space-md lg:p-space-lg rounded-xl bg-surface-container-low shadow-sm">
        <div className="flex items-center justify-between mb-space-xs">
          <div className="flex items-center gap-2">
            <span className="material-symbols-outlined text-primary text-[20px]">calculate</span>
            <h3 className="font-headline-sm text-headline-sm text-on-surface">
              Plastic Offset Calculator
            </h3>
          </div>
          <span className="font-label-caps text-[10px] text-primary bg-primary/10 px-2 py-0.5 rounded-full font-bold">
            Interactive
          </span>
        </div>
        <p className="font-body-sm text-body-sm text-on-surface-variant mb-space-sm">
          Estimate your enterprise CO₂ and petro-plastic reduction by switching to CassavaForge
          starch polymers:
        </p>
        <div className="flex flex-col gap-space-xs max-w-lg">
          <div className="flex justify-between items-center">
            <label className="font-label-md text-label-md text-on-surface" htmlFor="volumeSlider">
              Annual Packaging Volume:
            </label>
            <span className="font-headline-sm text-headline-sm text-primary font-bold">
              {volume.toLocaleString()} kg
            </span>
          </div>
          <input
            className="w-full accent-primary h-2 bg-surface-container-highest rounded-lg cursor-pointer"
            id="volumeSlider"
            max={100000}
            min={5000}
            step={5000}
            type="range"
            value={volume}
            onChange={(e) => setVolume(Number(e.target.value))}
          />
          <div className="grid grid-cols-2 gap-space-xs pt-space-xs min-w-0">
            <div className="bg-surface-container-lowest p-space-xs rounded-lg text-center shadow-sm">
              <span aria-live="polite" className="font-headline-sm text-headline-sm text-secondary font-bold">
                {co2Saved} Tons
              </span>
              <span className="block font-label-caps text-[9px] uppercase text-outline mt-0.5">
                CO₂ Offset
              </span>
            </div>
            <div className="bg-surface-container-lowest p-space-xs rounded-lg text-center shadow-sm">
              <span aria-live="polite" className="font-headline-sm text-headline-sm text-tertiary font-bold">
                {petroAvoided} kg
              </span>
              <span className="block font-label-caps text-[9px] uppercase text-outline mt-0.5">
                Petro Plastic Replaced
              </span>
            </div>
          </div>
          <p className="pt-space-xs font-body-sm text-[11px] text-on-surface-variant">
            Directional planning estimate based on current internal assumptions, not a verified lifecycle assessment.
          </p>
        </div>
      </div>
    </section>
  );
}
