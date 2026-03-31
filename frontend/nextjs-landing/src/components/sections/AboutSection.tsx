import type { LandingPayload } from "@/types/landing";
import { SectionTitle } from "./SectionTitle";

type AboutSectionProps = {
  about: LandingPayload["about"];
};

export function AboutSection({ about }: AboutSectionProps) {
  return (
    <section className="section-summer section-about" id="about">
      <div className="container">
        <SectionTitle title="About" subtitle={about.company} />
        <div className="row g-3">
          <div className="col-md-6" data-aos="fade-up">
            <div className="card h-100 border-0 shadow-sm p-3">
              <h5 className="fw-bold">Vision</h5>
              <p className="text-secondary mb-0">{about.vision}</p>
            </div>
          </div>
          <div className="col-md-6" data-aos="fade-up" data-aos-delay="80">
            <div className="card h-100 border-0 shadow-sm p-3">
              <h5 className="fw-bold">Story</h5>
              <p className="text-secondary mb-0">{about.story}</p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
