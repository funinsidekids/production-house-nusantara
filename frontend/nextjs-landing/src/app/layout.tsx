import type { Metadata } from "next";
import { Inter, Playfair_Display } from "next/font/google";
import "bootstrap/dist/css/bootstrap.min.css";
import "./globals.css";
import { UiPolicyProvider } from "@/components/landing/UiPolicyProvider";

const bodyFont = Inter({
  subsets: ["latin"],
  variable: "--font-body",
  display: "swap",
});

const headingFont = Playfair_Display({
  subsets: ["latin"],
  variable: "--font-heading",
  display: "swap",
});

export const metadata: Metadata = {
  title: "PHN Enterprise Landing",
  description: "Phase 2 modular enterprise landing powered by Laravel API",
};

export default function RootLayout({ children }: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="id">
      <body className={`${bodyFont.variable} ${headingFont.variable}`}>
        <UiPolicyProvider />
        {children}
      </body>
    </html>
  );
}
