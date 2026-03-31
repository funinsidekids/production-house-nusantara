"use client";

import { motion } from "framer-motion";
import type { LandingPayload } from "@/types/landing";

type HeroSectionProps = {
  hero: LandingPayload["hero"];
};

export function HeroSection({ hero }: HeroSectionProps) {
  const slide = hero.slides[0];

  return (
    <section id="home" className="hero-wrap position-relative overflow-hidden">
      {slide ? (
        <video className="hero-video" src={slide.videoUrl} autoPlay muted loop playsInline preload="metadata" />
      ) : null}
      <div className="hero-overlay" />
      <div className="container py-5 hero-content position-relative">
        <motion.h1 initial={{ y: 24, opacity: 0 }} animate={{ y: 0, opacity: 1 }} className="display-5 fw-bold text-white animate-hero hero-title">
          {hero.heading}
        </motion.h1>
        <motion.p initial={{ y: 24, opacity: 0 }} animate={{ y: 0, opacity: 1 }} transition={{ delay: 0.1 }} className="text-white-50 fs-5 animate-hero hero-tagline">
          {hero.tagline}
        </motion.p>
        <p className="text-white-50 animate-hero hero-lead">{hero.lead}</p>
        <div className="hero-cta-group animate-hero">
          <a className="btn btn-cta-primary" href={slide?.ctaUrl || "#contact"}>
            {slide?.ctaText || "Start Project"}
          </a>
          <a className="btn btn-cta-secondary" href="#portfolio">
            Lihat Portfolio
          </a>
        </div>
      </div>
    </section>
  );
}
