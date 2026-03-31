import type { LandingPayload } from "@/types/landing";
import { SectionTitle } from "./SectionTitle";

type ServicesSectionProps = {
  services: LandingPayload["services"];
};

export function ServicesSection({ services }: ServicesSectionProps) {
  return (
    <section className="section-summer section-services" id="services">
      <div className="container">
        <SectionTitle title="Services" subtitle="Film, video, documentary, commercial, editing, color grading." />
        <div className="row g-3">
          {services.map((item, index) => (
            <div className="col-12 col-md-6 col-xl-4" key={`${item.title}-${index}`} data-aos="fade-up">
              <div className="card h-100 border-0 shadow-sm p-3">
                <div className="fs-3 mb-2">{item.icon || "✨"}</div>
                <h5 className="fw-bold">{item.title || "Service"}</h5>
                <p className="text-secondary mb-0">{item.description || "-"}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
