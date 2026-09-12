import StitchHtml from "@/components/StitchHtml";
import CollaborationPathSection from "@/components/CollaborationPathSection";
import InvestorPartnerSection from "@/components/InvestorPartnerSection";
import { pageHtml } from "./content";

export default function HomePage() {
  return (
    <>
      <StitchHtml html={pageHtml} />
      <CollaborationPathSection />
      <InvestorPartnerSection />
    </>
  );
}
