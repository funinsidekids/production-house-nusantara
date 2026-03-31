"use client";

import { FormEvent, useState } from "react";
import type { LandingPayload } from "@/types/landing";

type ContactSectionProps = {
  contact: LandingPayload["contact"];
};

export function ContactSection({ contact }: ContactSectionProps) {
  const [status, setStatus] = useState("");
  const [saving, setSaving] = useState(false);
  const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || "http://127.0.0.1:8000";
  const bearer = process.env.NEXT_PUBLIC_GATEWAY_BEARER_TOKEN;

  const submit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    const form = new FormData(event.currentTarget);
    setSaving(true);
    setStatus("Mengirim...");
    try {
      const response = await fetch(`${apiBase}/contact`, {
        method: "POST",
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
          "X-App-Client": "nextjs-landing",
          ...(bearer ? { Authorization: `Bearer ${bearer}` } : {}),
        },
        body: form,
      });
      const payload = await response.json();
      if (!response.ok) {
        throw new Error(payload?.message || "Gagal kirim");
      }
      event.currentTarget.reset();
      setStatus(payload.message || "Pesan terkirim");
    } catch (error) {
      setStatus(error instanceof Error ? error.message : "Terjadi kesalahan");
    } finally {
      setSaving(false);
    }
  };

  return (
    <section className="section-summer section-contact" id="contact" data-aos="fade-up">
      <div className="container">
        <h2 className="fw-bold mb-2" style={{ color: "var(--phn-heading)" }}>
          {contact.title}
        </h2>
        <p className="text-secondary">{contact.text}</p>
        <div className="row g-3">
          <div className="col-12 col-lg-6">
            <form className="card border-0 shadow-sm d-grid gap-2 contact-form" onSubmit={submit}>
              <input className="form-control" name="name" placeholder="Nama" required />
              <input className="form-control" name="email" type="email" placeholder="Email" required />
              <input className="form-control" name="phone" placeholder="Phone" />
              <input className="form-control" name="service" placeholder="Service" required />
              <textarea className="form-control" name="message" rows={4} placeholder="Message" required />
              <button className="btn btn-cta-primary" type="submit" disabled={saving}>
                {saving ? "Sending..." : "Send"}
              </button>
              <small className="text-secondary">{status}</small>
            </form>
          </div>
          <div className="col-12 col-lg-6">
            <div className="card border-0 shadow-sm overflow-hidden">
              <iframe title="Office Map" src={`https://www.google.com/maps?q=${encodeURIComponent(contact.mapQuery)}&output=embed`} style={{ border: 0, minHeight: 340, width: "100%" }} loading="lazy" />
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
