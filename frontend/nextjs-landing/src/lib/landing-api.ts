import axios from "axios";
import type { LandingApiResponse, LandingPayload } from "@/types/landing";

const fallback: LandingPayload = {
  hero: {
    heading: "PRODUCTION HOUSE NUSANTARA",
    tagline: "Authentic Heritage • Modern Vision • Cinematic Excellence",
    lead: "Enterprise landing migration in progress.",
    slides: [
      {
        videoUrl: "https://videos.pexels.com/video-files/853890/853890-hd_1920_1080_25fps.mp4",
        ctaText: "Start Project",
        ctaUrl: "#contact",
        overlay: 0.78,
      },
    ],
  },
  about: {
    company: "Production House Nusantara",
    vision: "Membawa cerita lokal ke level visual global.",
    story: "Tim produksi modern dengan eksekusi end-to-end.",
  },
  services: [],
  portfolio: [],
  team: [],
  workflow: [],
  clients: [],
  testimonials: [],
  contact: {
    title: "Contact",
    text: "Konsultasikan project Anda.",
    whatsapp: "https://wa.me/6281234567890",
    email: "halo@productionhousenusantara.com",
    mapQuery: "-8.112972,112.311306",
  },
  footer: {
    brand: "Production House Nusantara",
    address: "8°06'46.7\"S 112°18'40.7\"E",
    copyright: "© Production House Nusantara",
    socials: [],
  },
};

export async function getLandingContent(): Promise<LandingPayload> {
  const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || "http://127.0.0.1:8000";
  const bearer = process.env.NEXT_PUBLIC_GATEWAY_BEARER_TOKEN;
  try {
    const response = await axios.get<LandingApiResponse>(`${apiBase}/api/landing/content`, {
      headers: {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
        "X-App-Client": "nextjs-landing",
        ...(bearer ? { Authorization: `Bearer ${bearer}` } : {}),
      },
    });
    return response.data.modular;
  } catch {
    return fallback;
  }
}
