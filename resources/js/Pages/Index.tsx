import { Head } from "@inertiajs/react";
import MainLayout from "@/components/MainLayout";
import HeroCarousel from "@/components/HeroCarousel";
import CTACards from "@/components/CTACards";
import VideoSection from "@/components/VideoSection";
import PackageSection from "@/components/PackageSection";
import FeaturesSection from "@/components/FeaturesSection";
import FloatingCTA from "@/components/FloatingCTA";

// Index.tsx
const Index = ({ settings, sections }: { settings: any, sections: any }) => {
  // Helper to get section content by type/slug
  const getSection = (slug: string) => {
    if (!Array.isArray(sections)) return null;
    return sections.find((s: any) => s.slug === slug)?.content;
  };

  const sectionComponents: Record<string, React.ComponentType<any>> = {
    hero: HeroCarousel,
    cta_cards: CTACards,
    video: VideoSection,
    package: PackageSection,
    features: FeaturesSection,
  };

  // Filter main sections that should be in the <main> tag
  const mainSections = Array.isArray(sections) 
    ? sections.filter((s: any) => !['topbar', 'footer'].includes(s.type) && s.is_active)
    : [];

  return (
    <>
      <Head>
        <title>{`${settings?.general?.site_name || 'IKA UNIMED'} - ${settings?.general?.site_tagline || 'Connect, Collaborate, Contribute'}`}</title>
        <meta name="description" content={settings?.general?.site_description || "Official Website Ikatan Alumni Universitas Negeri Medan (IKA UNIMED)"} />
        <meta property="og:title" content={settings?.general?.site_name || "IKA UNIMED"} />
        <meta property="og:description" content={settings?.general?.site_description || "Official Website Ikatan Alumni Universitas Negeri Medan"} />
      </Head>
      
      <MainLayout 
        variant="full" 
        settings={settings} 
        footerContent={getSection('footer')}
      > 
        <main className="flex-grow"> 
          {mainSections.map((section: any) => {
            const Component = sectionComponents[section.type];
            if (!Component) return null;
            return <Component key={section.id} content={section.content} />;
          })}
        </main>
        <FloatingCTA settings={settings} />
      </MainLayout>
    </>
  );
};

export default Index;
