"use client";

import Image from "next/image";
import { Swiper, SwiperSlide } from "swiper/react";
import { Pagination } from "swiper/modules";
import "swiper/css";
import "swiper/css/pagination";
import type { LandingPayload } from "@/types/landing";
import { SectionTitle } from "./SectionTitle";

type PortfolioSectionProps = {
  portfolio: LandingPayload["portfolio"];
};

export function PortfolioSection({ portfolio }: PortfolioSectionProps) {
  return (
    <section className="section-summer section-portfolio" id="portfolio">
      <div className="container">
        <SectionTitle title="Portfolio" subtitle="Grid, slider, video preview, modal-ready." />
        <Swiper modules={[Pagination]} pagination={{ clickable: true }} spaceBetween={16} slidesPerView={1.1} breakpoints={{ 768: { slidesPerView: 2.2 } }}>
          {portfolio.map((item, index) => (
            <SwiperSlide key={`${item.title}-${index}`}>
              <div className="card border-0 shadow-sm overflow-hidden" data-aos="fade-up">
                <Image
                  src={item.thumbnail_url || item.media_url || "https://images.unsplash.com/photo-1485846234645-a62644f84728?auto=format&fit=crop&w=1200&q=80"}
                  alt={item.title || "Portfolio"}
                  width={1200}
                  height={700}
                  className="w-100 object-fit-cover"
                  style={{ height: 220 }}
                />
                <div className="p-3">
                  <div className="small text-secondary">{item.category || "General"}</div>
                  <h6 className="fw-bold mb-0">{item.title || "Untitled"}</h6>
                </div>
              </div>
            </SwiperSlide>
          ))}
        </Swiper>
      </div>
    </section>
  );
}
