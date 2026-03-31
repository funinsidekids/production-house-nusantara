import type { LandingPayload } from "@/types/landing";
import { SectionTitle } from "./SectionTitle";

type WorkflowSectionProps = {
  workflow: LandingPayload["workflow"];
};

export function WorkflowSection({ workflow }: WorkflowSectionProps) {
  return (
    <section className="section-summer section-workflow" id="workflow">
      <div className="container">
        <SectionTitle title="Timeline / Workflow" subtitle="Pre production, production, post production." />
        <div className="row g-3">
          {workflow.map((step, index) => (
            <div className="col-12 col-md-4" key={`${step.title}-${index}`} data-aos="fade-up">
              <div className="card border-0 shadow-sm p-3 h-100">
                <div className="fs-3">{step.icon || "✅"}</div>
                <h6 className="fw-bold mt-2">{step.title || "-"}</h6>
                <p className="text-secondary mb-0">{step.description || "-"}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
