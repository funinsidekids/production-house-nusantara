type SectionTitleProps = {
  id?: string;
  title: string;
  subtitle?: string;
};

export function SectionTitle({ id, title, subtitle }: SectionTitleProps) {
  return (
    <div id={id} className="section-title-wrap" data-aos="fade-up">
      <h2 className="fw-bold mb-2" style={{ color: "var(--phn-heading)" }}>
        {title}
      </h2>
      {subtitle ? <p className="text-secondary mb-0">{subtitle}</p> : null}
    </div>
  );
}
