"use client";

import { useEffect } from "react";
import AOS from "aos";
import "aos/dist/aos.css";
import { gsap } from "gsap";

export function UiPolicyProvider() {
  useEffect(() => {
    document.documentElement.setAttribute("data-theme", "summer");
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    document.documentElement.classList.toggle("reduce-motion", reduceMotion);
    if (reduceMotion) {
      return;
    }
    AOS.init({ duration: 650, once: true, offset: 70, easing: "ease-out-cubic" });
    gsap.from(".animate-hero", { y: 24, opacity: 0, duration: 0.9, stagger: 0.1, ease: "power3.out" });
  }, []);

  return null;
}
