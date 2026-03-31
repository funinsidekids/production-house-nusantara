export type HeroSlide = {
  videoUrl: string;
  ctaText: string;
  ctaUrl: string;
  overlay: number;
};

export type LandingPayload = {
  hero: {
    heading: string;
    tagline: string;
    lead: string;
    slides: HeroSlide[];
  };
  about: {
    company: string;
    vision: string;
    story: string;
  };
  services: Array<{ icon?: string; title?: string; description?: string }>;
  portfolio: Array<{ title?: string; category?: string; media_type?: string; media_url?: string; thumbnail_url?: string }>;
  team: Array<{ name?: string; role?: string; photo_url?: string }>;
  workflow: Array<{ icon?: string; title?: string; description?: string }>;
  clients: Array<{ name?: string; logo?: string; url?: string }>;
  testimonials: Array<{ name?: string; quote?: string }>;
  contact: {
    title: string;
    text: string;
    whatsapp: string;
    email: string;
    mapQuery: string;
  };
  footer: {
    brand: string;
    address: string;
    copyright: string;
    socials: Array<{ name?: string; url?: string }>;
  };
};

export type LandingApiResponse = {
  heroSlides: Array<Record<string, unknown>>;
  landingContent: Record<string, unknown>;
  sliderConfig: Record<string, unknown>;
  landingSections: Record<string, unknown>;
  portfolioCategories: string[];
  modular: LandingPayload;
  meta: {
    apiVersion: string;
    gateway: {
      ready: boolean;
      authHeaderForwarded: boolean;
      requestedWith: string;
    };
  };
};
