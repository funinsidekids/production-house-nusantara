import type { LandingPayload } from "@/types/landing";
import { SectionTitle } from "./SectionTitle";

type ClientsSectionProps = {
  clients: LandingPayload["clients"];
};

export function ClientsSection({ clients }: ClientsSectionProps) {
  return (
    <section className="section-summer section-clients" id="clients">
      <div className="container">
        <SectionTitle title="Clients" subtitle="Logo slider / partner showcase." />
        <div className="d-flex gap-2 flex-wrap" data-aos="fade-up">
          {(clients.length ? clients : [{ name: "Brand A" }, { name: "Brand B" }, { name: "Brand C" }]).map((client, index) => (
            <span key={`${client.name}-${index}`} className="badge border px-3 py-2" style={{ background: "rgba(255,255,255,0.72)", color: "var(--phn-text)" }}>
              {client.name || "Client"}
            </span>
          ))}
        </div>
      </div>
    </section>
  );
}
