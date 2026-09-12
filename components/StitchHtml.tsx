import parse, {
  domToReact,
  Element,
  HTMLReactParserOptions,
  attributesToProps,
  DOMNode,
} from "html-react-parser";
import Link from "next/link";
import ContactForm from "./ContactForm";
import ProductFilterSection from "./ProductFilterSection";
import TechSpecsModal from "./TechSpecsModal";
import BlogList from "./BlogList";
import FaqAccordion from "./FaqAccordion";
import ProductOffsetCalculator from "./ProductOffsetCalculator";
import SocialIcon, { SocialLabel } from "./SocialIcon";
import { ROUTE_MAP } from "@/lib/routes";

const SOCIAL_LABELS = new Set<SocialLabel>(["LinkedIn", "X", "Instagram", "YouTube"]);

const options: HTMLReactParserOptions = {
  replace: (domNode) => {
    if (!(domNode instanceof Element)) return;

    for (const attribute of Object.keys(domNode.attribs ?? {})) {
      if (attribute.toLowerCase().startsWith("on")) {
        delete domNode.attribs[attribute];
      }
    }

    // Drop any leftover <script> tags from the Stitch export (e.g. the fake
    // click-navigation shim) — React won't execute them anyway, and real
    // Next.js navigation already replaces what they were doing.
    if (domNode.name === "script") {
      return <></>;
    }

    // The contact page's raw <form> was pulled out during extraction and
    // replaced with this placeholder div — swap in the real, interactive form.
    if (domNode.attribs?.id === "contact-form-slot") {
      return <ContactForm />;
    }

    // Products page: filter buttons + product grid, now real React state
    // instead of onclick="filterProducts(...)".
    if (domNode.attribs?.id === "product-filter-slot") {
      return <ProductFilterSection />;
    }

    // Products page: the "Technical Spec Sheet" button + modal, now real
    // React state instead of onclick="classList.toggle('hidden')".
    if (domNode.attribs?.id === "tech-specs-slot") {
      return <TechSpecsModal />;
    }

    if (domNode.attribs?.id === "blog-list-slot") {
      return <BlogList />;
    }

    if (domNode.attribs?.id === "faq-slot") {
      return <FaqAccordion />;
    }

    if (domNode.attribs?.id === "offset-calculator-slot") {
      return <ProductOffsetCalculator />;
    }

    const socialLabel = domNode.attribs?.["aria-label"];
    if (domNode.name === "a" && socialLabel && SOCIAL_LABELS.has(socialLabel as SocialLabel)) {
      const props = attributesToProps(domNode.attribs) as Record<string, unknown>;
      if (socialLabel === "LinkedIn") {
        props.href = "https://www.linkedin.com/company/139313981";
      }

      return (
        <a {...props}>
          <SocialIcon label={socialLabel as SocialLabel} />
        </a>
      );
    }

    // Stitch prototypes fake navigation with data-path + href="#".
    // Turn those into real Next.js links to the matching route.
    if (domNode.name === "a" && domNode.attribs?.["data-path"]) {
      const routeKey = domNode.attribs["data-path"];
      const href = ROUTE_MAP[routeKey] ?? "/";
      const props = attributesToProps(domNode.attribs) as Record<string, unknown>;
      delete props["data-path"];
      delete props.href;

      return (
        <Link href={href} {...props}>
          {domToReact(domNode.children as DOMNode[], options)}
        </Link>
      );
    }
  },
};

export default function StitchHtml({ html }: { html: string }) {
  return <>{parse(html, options)}</>;
}
