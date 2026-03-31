import type { LandingPayload } from "@/types/landing";
import { SectionTitle } from "./SectionTitle";

type TestimonialsSectionProps = {
  testimonials: LandingPayload["testimonials"];
};

export function TestimonialsSection({ testimonials }: TestimonialsSectionProps) {
  return (
    <section className="section-summer section-testimonials" id="testimonials">
      <div className="container">
        <SectionTitle title="Testimonials" subtitle="Client feedback carousel-ready." />
        <div className="row g-3">
          {(testimonials.length ? testimonials : [{ name: "Client", quote: "Tim sangat profesional dan hasilnya cinematic." }]).map((item, index) => (
            <div className="col-12 col-md-6" key={`${item.name}-${index}`} data-aos="fade-up">
              <div className="card border-0 shadow-sm p-3 h-100">
                <p className="mb-2">{item.quote || "-"}</p>
                <div className="small text-secondary">— {item.name || "Client"}</div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
