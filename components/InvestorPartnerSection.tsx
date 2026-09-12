import Link from "next/link";

const OPPORTUNITIES = [
  {
    icon: "trending_up",
    title: "Invest in material change",
    description:
      "Support the scale-up of cassava-based biomaterials, pilot manufacturing, and the infrastructure needed for responsible growth.",
  },
  {
    icon: "handshake",
    title: "Partner for real-world pilots",
    description:
      "Bring packaging, film, or molded-product requirements to a technical partnership built around measurable performance.",
  },
  {
    icon: "hub",
    title: "Connect the value chain",
    description:
      "Join a network of growers, converters, brands, and researchers working toward practical alternatives to conventional plastics.",
  },
];

export default function InvestorPartnerSection() {
  return (
    <section className="w-full overflow-hidden bg-primary text-on-primary" aria-labelledby="investor-partner-heading">
      <div className="mx-auto max-w-[1240px] px-layout-margin-mobile py-space-3xl lg:px-layout-margin-desktop lg:py-space-4xl">
        <div className="grid grid-cols-1 items-end gap-space-2xl lg:grid-cols-12 lg:gap-layout-gutter">
          <div className="motion-reveal lg:col-span-7">
            <span className="font-label-caps text-label-caps uppercase tracking-wider text-secondary-fixed">
              Open for collaboration
            </span>
            <h2 id="investor-partner-heading" className="mt-space-xs max-w-3xl font-headline-xl text-headline-xl">
              Help build the next generation of useful, lower-impact materials.
            </h2>
            <p className="mt-space-md max-w-2xl font-body-lg text-body-lg text-on-primary-container">
              We are looking for aligned investors and hands-on partners who can help move cassava
              bioplastics from promising material science into dependable everyday applications.
            </p>
          </div>

          <div className="motion-reveal flex flex-col gap-space-sm lg:col-span-5 lg:items-end">
            <Link
              href="/contact"
              className="inline-flex w-full items-center justify-center rounded-full bg-secondary-container px-space-xl py-space-sm font-label-md text-label-md text-on-secondary-container transition-colors hover:bg-secondary-fixed lg:w-auto"
            >
              Start a conversation
              <span className="material-symbols-outlined ml-space-xs text-[18px]">arrow_forward</span>
            </Link>
            <Link
              href="/products"
              className="inline-flex w-full items-center justify-center rounded-full border border-on-primary/40 px-space-xl py-space-sm font-label-md text-label-md text-on-primary transition-colors hover:bg-on-primary/10 lg:w-auto"
            >
              Explore our material focus
            </Link>
          </div>
        </div>

        <div className="mt-space-3xl grid grid-cols-1 gap-space-md md:grid-cols-3">
          {OPPORTUNITIES.map((opportunity, index) => (
            <div
              key={opportunity.title}
              className="motion-reveal border-t border-on-primary/25 pt-space-md"
              style={{ animationDelay: `${index * 90 + 120}ms` }}
            >
              <span className="material-symbols-outlined text-secondary-fixed">{opportunity.icon}</span>
              <h3 className="mt-space-sm font-headline-sm text-headline-sm text-on-primary">
                {opportunity.title}
              </h3>
              <p className="mt-space-xs font-body-sm text-body-sm text-on-primary-container">
                {opportunity.description}
              </p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}