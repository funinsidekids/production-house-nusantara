import Image from "next/image";
import type { LandingPayload } from "@/types/landing";
import { SectionTitle } from "./SectionTitle";

type TeamSectionProps = {
  team: LandingPayload["team"];
};

export function TeamSection({ team }: TeamSectionProps) {
  return (
    <section className="section-summer section-team" id="team">
      <div className="container">
        <SectionTitle title="Team" subtitle="Crew, director, editor, producer." />
        <div className="row g-3">
          {team.map((member, index) => (
            <div className="col-12 col-md-4" key={`${member.name}-${index}`} data-aos="fade-up">
              <div className="card h-100 border-0 shadow-sm p-3 text-center">
                <Image
                  src={member.photo_url || "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=900&q=80"}
                  alt={member.name || "Team"}
                  className="rounded-circle mx-auto object-fit-cover mb-3"
                  width={92}
                  height={92}
                />
                <h6 className="fw-bold mb-1">{member.name || "-"}</h6>
                <p className="text-secondary mb-0">{member.role || "-"}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
