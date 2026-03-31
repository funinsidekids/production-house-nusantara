import type { LandingPayload } from "@/types/landing";

type FooterSectionProps = {
  footer: LandingPayload["footer"];
};

export function FooterSection({ footer }: FooterSectionProps) {
  return (
    <footer className="py-4 border-top">
      <div className="container d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
          <div className="fw-bold">{footer.brand}</div>
          <div className="text-secondary small">{footer.address}</div>
          <div className="text-secondary small">{footer.copyright}</div>
        </div>
        <div className="d-flex gap-2 flex-wrap">
          {footer.socials.map((social, index) => (
            <a key={`${social.name}-${index}`} href={social.url || "#"} target="_blank" rel="noreferrer" className="btn btn-sm footer-social-btn">
              {social.name || "Social"}
            </a>
          ))}
        </div>
      </div>
    </footer>
  );
}
