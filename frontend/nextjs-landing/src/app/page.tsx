import { AboutSection } from "@/components/sections/AboutSection";
import { ClientsSection } from "@/components/sections/ClientsSection";
import { ContactSection } from "@/components/sections/ContactSection";
import { FooterSection } from "@/components/sections/FooterSection";
import { HeroSection } from "@/components/sections/HeroSection";
import { PortfolioSection } from "@/components/sections/PortfolioSection";
import { ServicesSection } from "@/components/sections/ServicesSection";
import { TeamSection } from "@/components/sections/TeamSection";
import { TestimonialsSection } from "@/components/sections/TestimonialsSection";
import { WorkflowSection } from "@/components/sections/WorkflowSection";
import { getLandingContent } from "@/lib/landing-api";

export default async function Page() {
  const payload = await getLandingContent();

  return (
    <main>
      <HeroSection hero={payload.hero} />
      <AboutSection about={payload.about} />
      <ServicesSection services={payload.services} />
      <PortfolioSection portfolio={payload.portfolio} />
      <TeamSection team={payload.team} />
      <WorkflowSection workflow={payload.workflow} />
      <ClientsSection clients={payload.clients} />
      <TestimonialsSection testimonials={payload.testimonials} />
      <ContactSection contact={payload.contact} />
      <FooterSection footer={payload.footer} />
    </main>
  );
}
