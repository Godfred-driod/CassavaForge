import Header from "@/components/Header";
import Footer from "@/components/Footer";
import BottomNav from "@/components/BottomNav";

export default function SiteLayout({ children }: { children: React.ReactNode }) {
  return (
    <>
      <Header />
      <main className="w-full pt-20 pb-16 md:pb-0 bg-surface min-h-[calc(100vh-20rem)]">
        {children}
      </main>
      <Footer />
      <BottomNav />
    </>
  );
}
