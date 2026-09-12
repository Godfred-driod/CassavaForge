import Link from "next/link";

const STEPS = [
  {
    number: "01",
    title: "Bring the use case",
    description: "Share the format, performance targets, volumes, and end-of-life requirements your team is solving for.",
  },
  {
    number: "02",
    title: "Test the material fit",
    description: "Align on the right product direction, then evaluate processing, durability, barrier needs, and real-world conditions.",
  },
  {
    number: "03",
    title: "Build the pilot",
    description: "Move from promising formulation to a measured pilot with the people and equipment needed for responsible scale-up.",
  },
];

export default function CollaborationPathSection() {
  return (
    <section className="w-full bg-surface-container-low" aria-labelledby="collaboration-path-heading">
      <div className="mx-auto max-w-[1240px] px-layout-margin-mobile py-space-3xl lg:px-layout-margin-desktop lg:py-space-4xl">
        <div className="grid grid-cols-1 gap-space-xl lg:grid-cols-12 lg:items-end lg:gap-layout-gutter">
          <div className="motion-reveal lg:col-span-5">
            <span className="font-label-caps text-label-caps uppercase tracking-wider text-primary">
              From conversation to pilot
            </span>
            <h2 id="collaboration-path-heading" className="mt-space-xs font-headline-xl text-headline-xl text-on-surface">
              A practical path for ambitious material ideas.
            </h2>
          </div>
          <p className="motion-reveal max-w-2xl font-body-lg text-body-lg text-on-surface-variant lg:col-span-7">
            Good biomaterials work is collaborative. Start with the problem, test the right variables,
            and make decisions with evidence from the application that matters.
          </p>
        </div>

        <div className="mt-space-2xl grid grid-cols-1 gap-space-lg md:grid-cols-3 md:gap-space-md">
          {STEPS.map((step, index) => (
            <article
              key={step.number}
              className="motion-reveal border-t-2 border-primary pt-space-md"
              style={{ animationDelay: `${index * 100 + 100}ms` }}
            >
              <span className="font-label-caps text-label-caps text-primary">{step.number}</span>
              <h3 className="mt-space-sm font-headline-sm text-headline-sm text-on-surface">{step.title}</h3>
              <p className="mt-space-xs font-body-md text-body-md text-on-surface-variant">{step.description}</p>
            </article>
          ))}
        </div>

        <Link
          href="/contact"
          className="motion-reveal mt-space-2xl inline-flex items-center rounded-full bg-primary px-space-xl py-space-sm font-label-md text-label-md text-on-primary transition-colors hover:bg-primary-container"
        >
          Talk through your use case
          <span className="material-symbols-outlined ml-space-xs text-[18px]">arrow_forward</span>
        </Link>
      </div>
    </section>
  );
}