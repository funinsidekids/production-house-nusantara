<!DOCTYPE html>
<html lang="id">
<head>
    @php
        $metaTitle = trim((string) ($cmsGeneral['site_title'] ?? '')) !== '' ? (string) $cmsGeneral['site_title'] : 'Production House Nusantara';
        $metaDescription = trim((string) ($cmsGeneral['site_description'] ?? '')) !== ''
            ? (string) $cmsGeneral['site_description']
            : ((string) ($landingContent['tagline_text'] ?? 'Production House Nusantara - Authentic Heritage • Modern Vision • Cinematic Excellence'));
        $metaOgDescription = trim((string) ($cmsGeneral['seo_default_og'] ?? '')) !== '' ? (string) $cmsGeneral['seo_default_og'] : $metaDescription;
        $metaKeywords = trim((string) ($cmsGeneral['keywords'] ?? ''));
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaOgDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if (!empty($cmsGeneral['favicon_32_url']))
        <link rel="icon" type="image/png" sizes="32x32" href="{{ $cmsGeneral['favicon_32_url'] }}">
        <link rel="shortcut icon" href="{{ $cmsGeneral['favicon_32_url'] }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
    @if (!empty($cmsGeneral['apple_touch_icon_url']))
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $cmsGeneral['apple_touch_icon_url'] }}">
    @endif
    @if (!empty($cmsGeneral['seo_schema_markup']))
        {!! $cmsGeneral['seo_schema_markup'] !!}
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
@include('partials.public-base-css')
@include('partials.public-ui-primitives-css')
        :root {
            --bg: #000000;
            --bg-soft: #0d0d0d;
            --surface: #1a0a0a;
            --line: rgba(242, 230, 217, .22);
            --text: #f2e6d9;
            --muted: #d8c8b6;
            --accent: #f2e6d9;
            --accent-soft: #f2e6d9;
            --accent-orange: #d98a2b;
            --accent-purple: #2a1a0a;
            --accent-red: #1a0a0a;
        }
        @keyframes auroraShift {
            0% { transform: translate3d(0, 0, 0) scale(1); }
            50% { transform: translate3d(0, -1.5%, 0) scale(1.04); }
            100% { transform: translate3d(0, 1.2%, 0) scale(1.02); }
        }
        html {
            scroll-behavior: smooth;
        }
        html.snap-enabled {
            scroll-snap-type: y proximity;
            scroll-padding-top: 88px;
        }
        body {
            margin: 0;
            background:
                radial-gradient(circle at 18% 14%, rgba(26, 10, 10, .9) 0%, rgba(0, 0, 0, 0) 42%),
                radial-gradient(circle at 82% 18%, rgba(42, 26, 10, .84) 0%, rgba(0, 0, 0, 0) 46%),
                radial-gradient(circle at 50% 90%, rgba(0, 0, 0, .9) 0%, rgba(0, 0, 0, 0) 55%),
                linear-gradient(180deg, #000000 0%, #0d0d0d 100%);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            line-height: 1.65;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 18% 20%, rgba(42, 26, 10, 0.32) 0%, rgba(42, 26, 10, 0) 48%),
                radial-gradient(circle at 82% 24%, rgba(26, 10, 10, 0.28) 0%, rgba(26, 10, 10, 0) 54%),
                radial-gradient(circle at 50% 84%, rgba(0, 0, 0, 0.9) 0%, rgba(0, 0, 0, 0) 52%);
            filter: blur(18px);
            animation: auroraShift 16s ease-in-out infinite alternate;
            pointer-events: none;
            z-index: -2;
        }
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, .08) .6px, transparent .6px);
            background-size: 3px 3px;
            opacity: .09;
            pointer-events: none;
            z-index: -1;
        }
        .scroll-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0%;
            background: rgba(255, 255, 255, .5);
            box-shadow: 0 0 14px rgba(255, 255, 255, .3);
            z-index: 40;
            transition: width .12s linear;
        }
@include('partials.public-header-css')
        .section-tag {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-size: .8rem;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: var(--accent-soft);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .section-tag::before {
            content: '';
            width: 26px;
            height: 1px;
            background: var(--accent-soft);
            opacity: .8;
        }
        .hero {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
            scroll-snap-align: start;
        }
        .hero-glow {
            position: absolute;
            border-radius: 999px;
            filter: blur(45px);
            pointer-events: none;
            z-index: 2;
            transform: translate3d(0, 0, 0);
        }
        .hero-glow.one {
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(217, 138, 43, 0.32) 0%, rgba(217, 138, 43, 0) 70%);
            left: -120px;
            top: -100px;
        }
        .hero-glow.two {
            width: 360px;
            height: 360px;
            background: radial-gradient(circle, rgba(42, 26, 10, 0.55) 0%, rgba(42, 26, 10, 0) 70%);
            right: -80px;
            bottom: 6%;
        }
        .hero-video {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        .hero-slide {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transform: translate3d(0, 0, 0) scale(1.02);
            transition: opacity var(--slide-transition-ms, 1000ms) ease, transform var(--slide-transition-ms, 1000ms) ease;
        }
        .hero-slide.active {
            opacity: 1;
            transform: translate3d(0, 0, 0) scale(1);
        }
        .hero.effect-slide .hero-slide {
            transform: translate3d(8%, 0, 0);
        }
        .hero.effect-slide .hero-slide.active {
            transform: translate3d(0, 0, 0);
        }
        .hero.effect-cube .hero-slide {
            transform: translate3d(0, 0, 0) rotateY(16deg) scale(.97);
            transform-origin: center right;
        }
        .hero.effect-cube .hero-slide.active {
            transform: translate3d(0, 0, 0) rotateY(0deg) scale(1);
        }
        .hero-overlay {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(180deg, color-mix(in srgb, var(--hero-overlay-color, #000000) 40%, transparent) 0%, color-mix(in srgb, var(--hero-overlay-color, #000000) calc(var(--hero-overlay-opacity, .9) * 100%), transparent) 62%, color-mix(in srgb, var(--hero-overlay-color, #000000) 92%, transparent) 100%),
                radial-gradient(circle at 24% 0%, #1a0a0a 0%, transparent 52%),
                radial-gradient(circle at 78% 0%, #2a1a0a 0%, transparent 55%);
            z-index: 2;
        }
        .hero-content {
            position: relative;
            z-index: 3;
            min-height: 100vh;
            display: grid;
            align-content: center;
            justify-items: center;
            text-align: center;
            padding: 5.5rem 0 4rem;
            transform: translate3d(0, 0, 0);
        }
        .hero-content.pos-left {
            justify-items: start;
            text-align: left;
        }
        .hero-content.pos-right {
            justify-items: end;
            text-align: right;
        }
        .hero-nav {
            position: absolute;
            z-index: 4;
            right: clamp(1rem, 3vw, 2rem);
            bottom: clamp(1rem, 3vw, 2rem);
            display: inline-flex;
            gap: .5rem;
        }
        .hero-nav-btn {
            border: 1px solid rgba(255, 255, 255, .28);
            background: rgba(5, 6, 10, .28);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #fff;
            border-radius: 999px;
            min-width: 88px;
            padding: .5rem .86rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            font-size: .76rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            font-weight: 600;
            cursor: pointer;
            transition: transform .22s ease, border-color .22s ease, background .22s ease, opacity .22s ease;
            opacity: .88;
        }
        .hero-nav-btn:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, .62);
            background: rgba(5, 6, 10, .44);
            opacity: 1;
        }
        .hero-nav-btn:active {
            transform: translateY(0);
        }
        .hero-nav-btn svg {
            width: 12px;
            height: 12px;
            fill: currentColor;
            opacity: .88;
        }
        .hero-mobile-fallback .hero-slide {
            display: none;
        }
        h1, h2 {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            line-height: 1.15;
        }
        h1 {
            font-size: clamp(2rem, 4.8vw, 3.7rem);
            max-width: min(100%, 1100px);
            font-weight: 900;
            letter-spacing: .08em;
            white-space: normal;
            color: #ffffff;
            text-shadow: 0 8px 32px rgba(0, 0, 0, .38);
        }
        .tagline {
            margin: .85rem 0 0;
            color: var(--accent-soft);
            font-size: clamp(.9rem, 2.2vw, 1rem);
            letter-spacing: .08em;
            font-weight: 600;
            text-transform: uppercase;
        }
        .lead {
            margin-top: 1.35rem;
            max-width: 720px;
            color: var(--text);
            font-size: clamp(1rem, 2.2vw, 1.14rem);
        }
        .hero-badges {
            margin-top: 1.3rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: .55rem;
        }
        .hero-badge {
            border: 1px solid rgba(255, 255, 255, .26);
            background: rgba(11, 8, 16, .62);
            backdrop-filter: blur(6px);
            border-radius: 999px;
            padding: .42rem .78rem;
            font-size: .78rem;
            color: var(--accent-soft);
            letter-spacing: .04em;
        }
        .btn {
            border: 1px solid transparent;
            border-radius: 999px;
            padding: .82rem 1.25rem;
            text-decoration: none;
            font-weight: 600;
            font-size: .95rem;
            transition: transform .26s ease, box-shadow .26s ease, border-color .26s ease, filter .26s ease, color .26s ease;
        }
        .btn-primary {
            background: var(--accent);
            color: #000000;
        }
        .btn-primary:hover {
            filter: brightness(1.07);
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(242, 195, 53, .34);
        }
        .btn-secondary {
            border-color: rgba(242, 230, 217, .5);
            color: var(--text);
            backdrop-filter: blur(2px);
        }
        .btn-secondary:hover {
            border-color: var(--accent-soft);
            color: var(--accent-soft);
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(139, 78, 255, .2);
        }
        .section {
            --section-gap: clamp(1.25rem, 2.2vw, 2rem);
            --module-gap: clamp(1rem, 2vw, 1.35rem);
            padding: clamp(3.9rem, 8vw, 5rem) 0;
            position: relative;
            opacity: 0;
            transform: translateY(34px);
            transition: opacity .9s ease, transform .9s ease;
            margin-top: var(--section-gap);
            scroll-snap-align: start;
        }
        .section:first-of-type {
            margin-top: 0;
        }
        .section.revealed {
            opacity: 1;
            transform: translateY(0);
        }
        .section > .container {
            display: grid;
            row-gap: var(--module-gap);
        }
        .section::before {
            content: '';
            position: absolute;
            inset: -2px 0 auto 0;
            height: 120px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .035) 0%, rgba(255, 255, 255, 0) 100%);
            pointer-events: none;
        }
        .section::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: calc(var(--section-gap) * -1);
            height: var(--section-gap);
            background: linear-gradient(180deg, rgba(0, 0, 0, 0) 0%, rgba(13, 13, 13, .82) 46%, rgba(0, 0, 0, 0) 100%);
            pointer-events: none;
        }
        h2 {
            font-size: clamp(1.75rem, 4vw, 2.65rem);
            margin-bottom: 1.25rem;
        }
        .section-heading {
            margin: 0;
            letter-spacing: .01em;
            position: relative;
            display: inline-block;
        }
        .section-heading::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -.42rem;
            width: min(120px, 36%);
            height: 2px;
            background: linear-gradient(90deg, var(--accent) 0%, var(--accent-orange) 55%, transparent 100%);
            border-radius: 999px;
        }
        .section-heading-center {
            text-align: center;
            max-width: 780px;
            margin-left: auto;
            margin-right: auto;
        }
        .section-heading-center::after {
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            background: linear-gradient(90deg, transparent 0%, var(--accent) 30%, var(--accent-orange) 65%, var(--accent-purple) 100%);
        }
        .section-heading-left {
            text-align: center;
        }
        .section-heading-left::after {
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            background: linear-gradient(90deg, transparent 0%, var(--accent) 30%, var(--accent-orange) 65%, var(--accent-purple) 100%);
        }
        .about p {
            margin: 0 0 1rem;
            color: var(--muted);
            max-width: 980px;
        }
        .benefits {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
            margin-top: 1.2rem;
        }
        .benefit-card {
            background: linear-gradient(165deg, rgba(242, 195, 53, 0.18) 0%, rgba(30, 17, 34, 0.96) 60%);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 1.2rem;
        }
        .benefit-card h3 {
            margin: 0 0 .55rem;
            font-size: 1.07rem;
        }
        .benefit-card p {
            margin: 0;
            color: var(--muted);
            font-size: .95rem;
        }
        .impact-box {
            margin-top: 1rem;
            padding: 1.25rem;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: rgba(0, 0, 0, .9);
        }
        .impact-box p {
            margin: 0;
            color: var(--text);
        }
        .services-grid,
        .project-grid,
        .why-grid,
        .style-grid,
        .testimonial-grid,
        .team-grid,
        .process-grid {
            display: grid;
            gap: 1rem;
        }
        .services-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        #portfolio > .container {
            width: 100%;
            max-width: none;
            padding-inline: clamp(14px, 3.5vw, 48px);
        }
        .project-grid {
            grid-template-columns: repeat(12, minmax(0, 1fr));
            grid-auto-rows: 110px;
        }
        #portfolio[data-grid-columns="2"] .project-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            grid-auto-rows: minmax(220px, auto);
        }
        #portfolio[data-grid-columns="3"] .project-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            grid-auto-rows: minmax(210px, auto);
        }
        #portfolio[data-grid-columns="4"] .project-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            grid-auto-rows: minmax(200px, auto);
        }
        #portfolio[data-grid-columns="6"] .project-grid {
            grid-template-columns: repeat(6, minmax(0, 1fr));
            grid-auto-rows: minmax(180px, auto);
        }
        .why-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .style-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .testimonial-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .team-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .process-grid {
            grid-template-columns: repeat(5, minmax(0, 1fr));
        }
        .service-card,
        .project-card,
        .why-card,
        .style-card,
        .testimonial-card,
        .team-card,
        .process-card {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: linear-gradient(165deg, rgba(42, 26, 10, 0.5) 0%, rgba(0, 0, 0, 0.9) 62%);
            padding: 1rem 1.1rem;
            transition: transform .28s ease, border-color .28s ease, box-shadow .28s ease;
            box-shadow: 0 12px 30px rgba(0, 0, 0, .22);
            backdrop-filter: blur(4px);
            text-align: center;
        }
        .service-card {
            position: relative;
            overflow: hidden;
        }
        .service-card::before {
            content: '';
            position: absolute;
            inset: -35% -35% auto auto;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(217, 138, 43, .42) 0%, rgba(217, 138, 43, 0) 70%);
            opacity: 0;
            transform: scale(.72);
            transition: opacity .32s ease, transform .32s ease;
            pointer-events: none;
        }
        .service-card:hover::before {
            opacity: 1;
            transform: scale(1);
        }
        .service-card:hover,
        .project-card:hover,
        .why-card:hover,
        .style-card:hover,
        .testimonial-card:hover,
        .team-card:hover,
        .process-card:hover {
            transform: translateY(-4px);
            border-color: rgba(242, 230, 217, .45);
            box-shadow: 0 18px 36px rgba(0, 0, 0, .36);
        }
        .service-icon {
            font-size: 1.45rem;
        }
        .service-card h3,
        .project-card h3,
        .team-card h3,
        .process-card h3 {
            margin: .5rem 0 .45rem;
            font-size: 1.04rem;
        }
        .service-card p,
        .project-card p,
        .why-card p,
        .style-card p,
        .testimonial-card p,
        .team-card p,
        .process-card p {
            margin: 0;
            color: var(--muted);
            font-size: .92rem;
        }
        .velocity-engine-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            border: 1px solid rgba(242, 230, 217, .4);
            border-radius: 999px;
            padding: .44rem .84rem;
            font-size: .76rem;
            color: var(--text);
            background: rgba(0, 0, 0, .9);
            margin-inline: auto;
        }
        .velocity-stage {
            position: relative;
            border: 1px solid rgba(242, 230, 217, .4);
            border-radius: 18px;
            overflow: hidden;
            background: radial-gradient(circle at 15% 20%, rgba(42, 26, 10, .8) 0%, rgba(0, 0, 0, .9) 55%);
            min-height: clamp(300px, 48vw, 540px);
        }
        .velocity-particle-canvas {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 1;
            opacity: .7;
        }
        .velocity-track {
            position: relative;
            z-index: 2;
            display: flex;
            height: 100%;
            transition: transform .65s cubic-bezier(.22, .61, .36, 1);
        }
        .velocity-slide {
            min-width: 100%;
            height: clamp(300px, 48vw, 540px);
            position: relative;
            display: grid;
            place-items: center;
        }
        .velocity-media {
            position: absolute;
            inset: 0;
        }
        .velocity-media iframe,
        .velocity-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 0;
        }
        .velocity-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(0, 0, 0, .4) 0%, rgba(0, 0, 0, .9) 78%);
        }
        .velocity-caption {
            position: relative;
            z-index: 3;
            width: min(900px, 90%);
            display: grid;
            gap: .7rem;
            text-align: center;
            transform: translateY(18px);
            opacity: 0;
            transition: transform .5s ease, opacity .5s ease;
        }
        .velocity-slide.is-active .velocity-caption {
            transform: translateY(0);
            opacity: 1;
        }
        .velocity-caption h3 {
            margin: 0;
            font-size: clamp(1.15rem, 2.6vw, 1.85rem);
        }
        .velocity-caption p {
            margin: 0;
            color: var(--text);
        }
        .velocity-tags {
            display: flex;
            justify-content: center;
            gap: .45rem;
            flex-wrap: wrap;
        }
        .velocity-tag {
            border: 1px solid rgba(242, 230, 217, .4);
            border-radius: 999px;
            padding: .28rem .62rem;
            font-size: .72rem;
            background: rgba(0, 0, 0, .55);
            color: var(--text);
        }
        .velocity-controls {
            position: absolute;
            z-index: 4;
            right: .8rem;
            bottom: .8rem;
            display: flex;
            gap: .4rem;
        }
        .velocity-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, .3);
            background: rgba(255, 255, 255, .3);
            cursor: pointer;
            transition: transform .2s ease, background .2s ease;
        }
        .velocity-dot.is-active {
            transform: scale(1.25);
            background: var(--accent-soft);
            border-color: rgba(242, 230, 217, .5);
        }
        .velocity-dot:hover {
            background: rgba(255, 255, 255, .5);
            border-color: rgba(255, 255, 255, .5);
        }
        .project-filter {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: .55rem;
        }
        .project-filter.media-filter {
            margin-top: .2rem;
        }
        .filter-btn {
            border: 1px solid rgba(242, 230, 217, .4);
            background: rgba(0, 0, 0, .9);
            color: var(--text);
            border-radius: 999px;
            padding: .45rem .9rem;
            font-size: .84rem;
            cursor: pointer;
            transition: .22s ease;
        }
        .filter-btn.active,
        .filter-btn:hover {
            border-color: var(--accent-soft);
            color: var(--accent-soft);
        }
        .project-thumb {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 16px;
            border: 1px solid var(--line);
            display: block;
            transition: transform .5s ease, filter .45s ease;
        }
        .project-card {
            grid-column: span 4;
            grid-row: span 2;
            padding: .72rem;
            position: relative;
            overflow: hidden;
            --card-glow: rgba(224, 171, 86, 0.22);
            transform-style: preserve-3d;
            will-change: transform;
        }
        .project-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 85% 12%, var(--card-glow) 0%, rgba(7, 9, 15, 0) 48%);
            opacity: .55;
            transition: opacity .35s ease;
            pointer-events: none;
        }
        .project-card:hover::after,
        .project-card.is-active::after {
            opacity: 1;
        }
        .project-card:hover .project-thumb,
        .project-card.is-active .project-thumb {
            transform: scale(1.08);
            filter: saturate(1.08);
        }
        .project-card.is-active {
            border-color: rgba(224, 171, 86, .86);
            box-shadow: 0 18px 42px rgba(0, 0, 0, .46), 0 0 0 1px rgba(224, 171, 86, .48) inset;
        }
        .project-card:nth-child(1) { --card-glow: rgba(242, 195, 53, .36); }
        .project-card:nth-child(2) { --card-glow: rgba(239, 68, 68, .34); }
        .project-card:nth-child(3) { --card-glow: rgba(139, 78, 255, .34); }
        .project-card:nth-child(4) { --card-glow: rgba(255, 137, 46, .34); }
        .project-card:nth-child(5) { --card-glow: rgba(176, 91, 255, .34); }
        .project-card:nth-child(6) { --card-glow: rgba(255, 92, 54, .34); }
        .project-card.bento-large {
            grid-column: span 8;
            grid-row: span 3;
        }
        .project-card.bento-wide {
            grid-column: span 8;
            grid-row: span 2;
        }
        .project-card.bento-tall {
            grid-column: span 4;
            grid-row: span 3;
        }
        #portfolio.portfolio-mode-basic .project-card,
        #portfolio.portfolio-mode-card .project-card,
        #portfolio.portfolio-mode-hover .project-card,
        #portfolio.portfolio-mode-filterable .project-card,
        #portfolio.portfolio-mode-video .project-card,
        #portfolio.portfolio-mode-responsive .project-card {
            grid-column: auto;
            grid-row: auto;
        }
        #portfolio.portfolio-mode-card .project-card {
            padding: .88rem;
            text-align: left;
        }
        #portfolio.portfolio-mode-card .project-content {
            position: static;
            background: transparent;
            padding: .7rem 0 .1rem;
            border-radius: 0;
        }
        #portfolio.portfolio-mode-hover.overlay .project-content {
            opacity: 0;
            transform: translateY(8px);
            transition: opacity .25s ease, transform .25s ease;
        }
        #portfolio.portfolio-mode-hover.overlay .project-card:hover .project-content {
            opacity: 1;
            transform: translateY(0);
        }
        #portfolio.portfolio-mode-hover.zoom .project-card:hover .project-thumb {
            transform: scale(1.13);
        }
        #portfolio.portfolio-mode-hover.lift .project-card:hover {
            transform: translateY(-8px);
        }
        #portfolio.portfolio-mode-cinematic .project-card {
            border-color: rgba(255, 208, 145, .34);
        }
        #portfolio.portfolio-mode-cinematic .project-meta h3 {
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        #portfolio.portfolio-mode-video .project-card[data-media="photo"] {
            display: none !important;
        }
        #portfolio.portfolio-mode-responsive .project-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            grid-auto-rows: minmax(200px, auto);
        }
        .portfolio-load-more-wrap {
            display: none;
            justify-content: center;
            margin-top: 1rem;
        }
        .portfolio-pagination {
            display: none;
            justify-content: center;
            gap: .45rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        .portfolio-pagination button {
            border: 1px solid rgba(242, 230, 217, .5);
            background: rgba(0,0,0,.85);
            color: var(--text);
            border-radius: 999px;
            padding: .35rem .75rem;
            cursor: pointer;
        }
        .portfolio-pagination button.active {
            border-color: var(--accent-soft);
            color: var(--accent-soft);
        }
        .project-content {
            position: absolute;
            left: .95rem;
            right: .95rem;
            bottom: .95rem;
            border-radius: 12px;
            background: linear-gradient(180deg, rgba(7, 9, 15, 0.2) 0%, rgba(7, 9, 15, 0.92) 76%);
            padding: .75rem .8rem;
            backdrop-filter: blur(2px);
        }
        .project-card.filtered-out {
            opacity: 0;
            transform: scale(.94);
            pointer-events: none;
            transition: opacity .28s ease, transform .28s ease;
        }
        .project-card.filtered-in {
            opacity: 1;
            transform: scale(1);
            transition: opacity .32s ease, transform .32s ease;
        }
        .project-meta {
            margin-top: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .65rem;
        }
        .project-meta span {
            color: var(--accent-soft);
            font-size: .8rem;
            letter-spacing: .05em;
            text-transform: uppercase;
        }
        .project-card p {
            margin-top: .35rem;
            font-size: .86rem;
            line-height: 1.55;
            color: var(--muted);
        }
        .project-links {
            margin-top: .55rem;
            display: flex;
            gap: .5rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        .project-type {
            margin: 0;
            color: #fff1d2;
            font-size: .76rem;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .project-link {
            border: 1px solid rgba(242, 230, 217, .5);
            border-radius: 999px;
            padding: .36rem .75rem;
            text-decoration: none;
            color: var(--text);
            font-size: .82rem;
        }
        .project-link:hover {
            border-color: var(--accent-soft);
            color: var(--accent-soft);
        }
        .counter-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }
        .counter-box {
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
            background: rgba(0, 0, 0, .9);
        }
        .counter-box strong {
            display: block;
            font-size: 1.8rem;
            color: var(--accent-soft);
            line-height: 1.2;
        }
        .counter-box span {
            color: var(--muted);
            font-size: .88rem;
        }
        .moodboard {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .85rem;
        }
        .moodboard img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 14px;
            border: 1px solid var(--line);
        }
        .quote {
            margin: 0;
            color: #e6edf7;
            font-size: .95rem;
            line-height: 1.7;
        }
        .client-name {
            margin-top: .8rem;
            color: var(--accent-soft);
            font-weight: 600;
            font-size: .94rem;
        }
        .team-photo {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 12px;
            filter: grayscale(1) contrast(1.06);
            border: 1px solid var(--line);
        }
        .team-role {
            color: var(--accent-soft);
            font-size: .87rem;
            margin-top: .25rem;
        }
        .team-caption {
            margin-top: .5rem;
            font-size: .86rem;
            color: #d2dbe7;
            line-height: 1.55;
        }
        .process-icon {
            font-size: 1.38rem;
        }
        .cinematic-cta {
            --section-gap: clamp(1.25rem, 2.2vw, 2rem);
            --module-gap: clamp(1rem, 2vw, 1.35rem);
            text-align: center;
            padding: clamp(3.9rem, 8vw, 4.6rem) 0;
            background: linear-gradient(180deg, rgba(239, 68, 68, 0.08) 0%, rgba(7, 9, 15, 0) 46%), linear-gradient(180deg, rgba(242, 195, 53, 0.09) 0%, rgba(7, 9, 15, 0) 100%);
            position: relative;
            opacity: 0;
            transform: translateY(34px);
            transition: opacity .9s ease, transform .9s ease;
            margin-top: var(--section-gap);
            scroll-snap-align: start;
        }
        .cinematic-cta > .container {
            display: grid;
            row-gap: var(--module-gap);
        }
        .cinematic-cta.revealed {
            opacity: 1;
            transform: translateY(0);
        }
        .cinematic-cta::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: calc(var(--section-gap) * -1);
            height: var(--section-gap);
            background: linear-gradient(180deg, rgba(7, 9, 15, 0) 0%, rgba(36, 18, 33, .52) 46%, rgba(7, 9, 15, 0) 100%);
            pointer-events: none;
        }
        .cta-actions {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: .7rem;
        }
        .contact-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .contact-form {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(255, 255, 255, .05), rgba(255, 255, 255, .02));
            padding: 1rem;
            display: grid;
            gap: .62rem;
        }
        .contact-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .62rem;
        }
        .contact-form input,
        .contact-form select,
        .contact-form textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: rgba(0, 0, 0, .2);
            color: var(--text);
            padding: .62rem .74rem;
            font: inherit;
        }
        .contact-form-status {
            min-height: 1.3rem;
            font-size: .82rem;
            color: var(--muted);
        }
        .contact-map {
            border: 1px solid var(--line);
            border-radius: 18px;
            overflow: hidden;
            min-height: 100%;
            background: rgba(0, 0, 0, .22);
        }
        .contact-map iframe {
            width: 100%;
            height: 100%;
            min-height: 320px;
            border: 0;
            display: block;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: 1rem;
            text-align: center;
        }
        .footer-title {
            color: var(--text);
            margin: 0 0 .45rem;
            font-weight: 700;
        }
        .footer-text,
        .footer-link {
            margin: 0;
            color: var(--muted);
            font-size: .92rem;
            text-decoration: none;
        }
        .footer-link:hover {
            color: var(--accent-soft);
        }
        .footer-social-grid {
            margin-top: .3rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: .6rem;
        }
        .footer-social-link {
            --brand-start: rgba(143, 100, 255, .34);
            --brand-end: rgba(255, 143, 61, .28);
            --brand-accent: #f3dfb8;
            display: inline-flex;
            align-items: center;
            gap: .42rem;
            border: 1px solid rgba(255, 255, 255, .2);
            border-radius: 999px;
            padding: .45rem .72rem;
            color: #edf3fb;
            text-decoration: none;
            background: linear-gradient(140deg, rgba(143, 100, 255, .24) 0%, rgba(255, 143, 61, .2) 100%);
            transform-style: preserve-3d;
            transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease, background .28s ease, color .28s ease;
        }
        .footer-social-link:hover {
            transform: translateY(-3px) rotateX(10deg);
            border-color: color-mix(in srgb, var(--brand-accent) 70%, #fff 30%);
            color: #ffffff;
            background: linear-gradient(140deg, var(--brand-start) 0%, var(--brand-end) 100%);
            box-shadow: 0 12px 28px color-mix(in srgb, var(--brand-accent) 30%, rgba(0, 0, 0, .45) 70%);
        }
        .social-icon {
            width: 26px;
            height: 26px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(7, 9, 15, .62);
            border: 1px solid rgba(255, 255, 255, .14);
            font-size: .82rem;
        }
        .footer-social-link:hover .social-icon {
            background: color-mix(in srgb, var(--brand-accent) 26%, rgba(7, 9, 15, .74) 74%);
            border-color: color-mix(in srgb, var(--brand-accent) 62%, rgba(255, 255, 255, .2) 38%);
        }
        .social-icon svg {
            width: 14px;
            height: 14px;
            fill: currentColor;
            display: block;
        }
        .footer-social-link[data-platform='instagram'] { --brand-start: rgba(131, 58, 180, .58); --brand-end: rgba(253, 29, 29, .48); --brand-accent: #f77737; }
        .footer-social-link[data-platform='youtube'] { --brand-start: rgba(255, 0, 0, .54); --brand-end: rgba(179, 0, 0, .42); --brand-accent: #ff0000; }
        .footer-social-link[data-platform='vimeo'] { --brand-start: rgba(26, 183, 234, .52); --brand-end: rgba(14, 116, 179, .42); --brand-accent: #1ab7ea; }
        .footer-social-link[data-platform='tiktok'] { --brand-start: rgba(0, 242, 234, .44); --brand-end: rgba(255, 0, 80, .46); --brand-accent: #25f4ee; }
        .footer-social-link[data-platform='facebook'] { --brand-start: rgba(24, 119, 242, .54); --brand-end: rgba(15, 78, 160, .42); --brand-accent: #1877f2; }
        .footer-social-link[data-platform='linkedin'] { --brand-start: rgba(10, 102, 194, .54); --brand-end: rgba(6, 71, 136, .42); --brand-accent: #0a66c2; }
        .footer-social-link[data-platform='reddit'] { --brand-start: rgba(255, 69, 0, .56); --brand-end: rgba(255, 138, 0, .42); --brand-accent: #ff4500; }
        .footer-social-link[data-platform='twitter'] { --brand-start: rgba(29, 161, 242, .52); --brand-end: rgba(15, 91, 164, .42); --brand-accent: #1d9bf0; }
        .footer-social-link[data-platform='threads'] { --brand-start: rgba(30, 30, 30, .56); --brand-end: rgba(90, 90, 90, .4); --brand-accent: #ffffff; }
        .footer-social-link[data-platform='whatsapp'] { --brand-start: rgba(37, 211, 102, .54); --brand-end: rgba(18, 120, 58, .44); --brand-accent: #25d366; }
        .footer-social-link[data-platform='telegram'] { --brand-start: rgba(0, 136, 204, .56); --brand-end: rgba(0, 97, 153, .42); --brand-accent: #0088cc; }
        .footer-social-link[data-platform='discord'] { --brand-start: rgba(88, 101, 242, .56); --brand-end: rgba(68, 76, 187, .42); --brand-accent: #5865f2; }
        .footer-social-link[data-platform='pinterest'] { --brand-start: rgba(230, 0, 35, .58); --brand-end: rgba(156, 0, 24, .44); --brand-accent: #e60023; }
        .footer-social-link[data-platform='snapchat'] { --brand-start: rgba(255, 252, 0, .58); --brand-end: rgba(255, 200, 0, .44); --brand-accent: #fffc00; }
        .footer-social-link[data-platform='github'] { --brand-start: rgba(36, 41, 46, .58); --brand-end: rgba(88, 96, 105, .44); --brand-accent: #c9d1d9; }
        .footer-social-link[data-platform='website'] { --brand-start: rgba(224, 171, 86, .55); --brand-end: rgba(143, 100, 255, .44); --brand-accent: #e0ab56; }
        .video-lightbox {
            position: fixed;
            inset: 0;
            z-index: 80;
            background: rgba(4, 8, 14, .84);
            backdrop-filter: blur(5px);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .video-lightbox.open {
            display: flex;
        }
        .video-lightbox-panel {
            width: min(100%, 1020px);
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .2);
            background: #07090f;
            box-shadow: 0 18px 60px rgba(0, 0, 0, .6);
        }
        .video-lightbox-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .7rem;
            padding: .7rem .95rem;
            border-bottom: 1px solid rgba(255, 255, 255, .1);
        }
        .video-lightbox-title {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
        }
        .video-lightbox-close {
            border: 1px solid rgba(242, 230, 217, .5);
            background: rgba(0, 0, 0, .9);
            color: var(--text);
            border-radius: 999px;
            width: 34px;
            height: 34px;
            cursor: pointer;
        }
        .video-lightbox-frame {
            width: 100%;
            min-height: 560px;
            border: 0;
            display: block;
            background: #000;
        }
        .reveal-item {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity .7s ease, transform .7s ease;
            transition-delay: var(--reveal-delay, 0ms);
            will-change: transform, opacity;
        }
        .reveal-item.revealed {
            opacity: 1;
            transform: translateY(0);
        }
        footer {
            color: var(--muted);
            padding: 2rem 0 2.6rem;
        }
        @media (max-width: 980px) {
            .contact-layout {
                grid-template-columns: 1fr;
            }
            .contact-form-row {
                grid-template-columns: 1fr;
            }
            h1 {
                white-space: normal;
                letter-spacing: .05em;
            }
            .hero-nav {
                right: .9rem;
                bottom: .9rem;
            }
            .hero-nav-btn {
                min-width: 74px;
                padding: .44rem .7rem;
                font-size: .72rem;
            }
            .section-heading-left {
                text-align: center;
                max-width: 780px;
                margin-left: auto;
                margin-right: auto;
            }
            .services-grid,
            .why-grid,
            .style-grid,
            .testimonial-grid,
            .team-grid,
            .process-grid,
            .counter-grid,
            .moodboard,
            .footer-grid {
                grid-template-columns: 1fr;
            }
            .project-grid {
                grid-template-columns: 1fr;
                grid-auto-rows: auto;
            }
            .project-card {
                grid-column: auto !important;
                grid-row: auto !important;
                min-height: 360px;
            }
            #portfolio.portfolio-mode-responsive .project-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 580px) {
            #portfolio.portfolio-mode-responsive .project-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .section,
            .cinematic-cta,
            .reveal-item,
            .hero-slide {
                transition: none;
            }
            body::before {
                animation: none;
            }
            html.snap-enabled {
                scroll-snap-type: none;
            }
        }
    </style>
</head>
<body>
    <div class="scroll-progress" id="scrollProgress"></div>
    @include('partials.public-header-markup', [
        'headerConfig' => $headerConfig,
        'isInnerPage' => (bool) ($isInnerPage ?? false),
        'headerBrandHref' => '#home',
        'headerBrandText' => $landingSections['footer_brand_name'] ?? ($cmsGeneral['site_title'] ?? 'Production House Nusantara'),
        'headerBrandAlt' => $metaTitle,
    ])
    <header
        id="home"
        class="hero effect-{{ $sliderConfig['transition_effect'] ?? 'fade' }}"
        style="--slide-transition-ms: {{ $sliderConfig['transition_ms'] }}ms;"
        data-autoplay-ms="{{ $sliderConfig['autoplay_ms'] }}"
        data-auto-play="{{ $sliderConfig['auto_play'] ? '1' : '0' }}"
        data-loop="{{ ($sliderConfig['loop'] ?? true) ? '1' : '0' }}"
        data-transition-effect="{{ $sliderConfig['transition_effect'] ?? 'fade' }}"
        data-mobile-disable-video="{{ ($sliderConfig['mobile_disable_video'] ?? true) ? '1' : '0' }}"
        data-parallax-strength="{{ $sliderConfig['parallax_strength'] }}"
        data-glow-intensity="{{ max(0.2, min(1.7, $sliderConfig['glow_intensity'])) }}"
        data-engine-mode="{{ $sliderConfig['engine_mode'] ?? 'sr7' }}"
    >
        <div class="hero-video">
            @foreach ($heroSlides as $slide)
                <video
                    class="hero-slide @if ($loop->first) active @endif"
                    autoplay
                    @if ($slide->mute_default ?? true) muted @endif
                    @if ($sliderConfig['loop'] ?? true) loop @endif
                    playsinline
                    preload="{{ ($sliderConfig['preload_critical'] ?? true) && $loop->first ? 'auto' : (($sliderConfig['lazy_load'] ?? true) ? 'metadata' : 'auto') }}"
                    @if (!empty($slide->poster_image)) poster="{{ $slide->poster_image }}" @endif
                    data-cta-text="{{ $slide->cta_text ?? '' }}"
                    data-cta-url="{{ $slide->cta_url ?? '' }}"
                    data-duration="{{ $slide->duration_seconds ?? 0 }}"
                    data-overlay="{{ $slide->overlay_opacity ?? 0.78 }}"
                    data-overlay-color="{{ $slide->overlay_color ?? '#000000' }}"
                    data-text-position="{{ $slide->text_position ?? 'center' }}"
                    data-text-animation="{{ $slide->text_animation ?? 'fade-up' }}"
                    data-mobile-fallback="{{ $slide->mobile_fallback_image ?? '' }}"
                    data-tablet-video="{{ $slide->tablet_video ?? '' }}"
                >
                    <source src="{{ $slide->video_url }}">
                </video>
            @endforeach
        </div>
        <div class="hero-overlay"></div>
        <div class="hero-glow one" data-parallax="0.16"></div>
        <div class="hero-glow two" data-parallax="-0.12"></div>
        <div class="container hero-content">
            <h1>{{ $landingContent['heading_text'] }}</h1>
            <p class="tagline">{{ $landingContent['tagline_text'] }}</p>
            @if (! empty($landingContent['lead_text']))
                <p class="lead">{{ $landingContent['lead_text'] }}</p>
            @endif
            <div class="hero-badges">
                <span class="hero-badge">Creative Direction</span>
                <span class="hero-badge">Premium Production</span>
                <span class="hero-badge">Cinematic Storytelling</span>
            </div>
        </div>
        @if (count($heroSlides) > 1)
            <div class="hero-nav" aria-label="Hero navigation">
                <button type="button" class="hero-nav-btn" id="heroPrevBtn" aria-label="Previous slide">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
                    <span>Prev</span>
                </button>
                <button type="button" class="hero-nav-btn" id="heroNextBtn" aria-label="Next slide">
                    <span>Next</span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.59 16.59 13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
                </button>
            </div>
        @endif
    </header>

    @if (($sectionVisibility['services'] ?? true) === true)
    <section id="services" class="section">
        <div class="container">
            <h2 class="section-heading section-heading-center">{{ $landingSections['services_title'] }}</h2>
            <div class="services-grid">
                @foreach ($landingSections['services_items'] as $service)
                    <article class="service-card">
                        <div class="service-icon">{{ $service['icon'] ?? '✨' }}</div>
                        <h3>{{ $service['title'] ?? '' }}</h3>
                        <p>{{ $service['description'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if (($sectionVisibility['portfolio'] ?? true) === true)
    <section
        id="portfolio"
        class="section portfolio-mode-{{ $portfolioDisplay['grid_mode'] ?? 'cinematic' }}"
        data-grid-mode="{{ $portfolioDisplay['grid_mode'] ?? 'cinematic' }}"
        data-grid-columns="{{ $portfolioDisplay['grid_columns'] ?? 4 }}"
        data-enable-ajax-filter="{{ ($portfolioDisplay['enable_ajax_filter'] ?? true) ? '1' : '0' }}"
        data-hover-effect="{{ $portfolioDisplay['hover_effect'] ?? 'overlay' }}"
        data-load-strategy="{{ $portfolioDisplay['load_strategy'] ?? 'none' }}"
        data-items-per-page="{{ $portfolioDisplay['items_per_page'] ?? 8 }}"
    >
        <div class="container">
            <h2 class="section-heading section-heading-center">{{ $landingSections['portfolio_title'] }}</h2>
            <div class="velocity-stage" id="velocityStage">
                <canvas class="velocity-particle-canvas" id="velocityParticleCanvas"></canvas>
                <div class="velocity-track" id="velocityTrack">
                    @foreach ($landingSections['portfolio_items'] as $index => $item)
                        @php
                            $mediaType = $item['media_type'] ?? (($item['video_url'] ?? '') !== '' ? 'video' : 'photo');
                            $mediaUrl = $item['media_url'] ?? ($item['video_url'] ?? ($item['thumbnail_url'] ?? ''));
                            $thumbUrl = $item['thumbnail_url'] ?? $mediaUrl;
                        @endphp
                        <article class="velocity-slide {{ $index === 0 ? 'is-active' : '' }}" data-media="{{ $mediaType }}" data-category="{{ $item['category_slug'] ?? \Illuminate\Support\Str::slug($item['category'] ?? 'general') }}">
                            <div class="velocity-media">
                                @if ($mediaType === 'video')
                                    <img src="{{ $thumbUrl }}" alt="{{ $item['title'] ?? 'Project' }}">
                                @else
                                    <img src="{{ $mediaUrl }}" alt="{{ $item['title'] ?? 'Project' }}">
                                @endif
                            </div>
                            <div class="velocity-overlay"></div>
                            <div class="velocity-caption">
                                <div class="velocity-tags">
                                    <span class="velocity-tag">{{ strtoupper($mediaType) }}</span>
                                    <span class="velocity-tag">{{ $item['category'] ?? 'General' }}</span>
                                </div>
                                <h3>{{ $item['title'] ?? '' }}</h3>
                                <p>{{ $item['description'] ?? '' }}</p>
                                <div class="project-links">
                                    @if ($mediaType === 'video')
                                        <a href="#" class="project-link js-open-video" data-video-url="{{ $mediaUrl }}" data-video-title="{{ $item['title'] ?? 'Project' }}">▶ Play Video</a>
                                    @else
                                        <a href="{{ $mediaUrl }}" class="project-link" target="_blank">▶ Open Photo</a>
                                    @endif
                                    <a href="{{ $item['detail_url'] ?? '#contact' }}" class="project-link">▶ Detail Project</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="velocity-controls" id="velocityControls"></div>
            </div>
            <div class="project-filter media-filter" id="mediaFilter">
                <button class="filter-btn active" data-media-filter="all" type="button">All Media</button>
                <button class="filter-btn" data-media-filter="video" type="button">Video</button>
                <button class="filter-btn" data-media-filter="photo" type="button">Photo</button>
            </div>
            <div class="project-filter category-filter" id="categoryFilter">
                <button class="filter-btn active" data-filter="all" type="button">All Category</button>
                @foreach ($portfolioCategories as $category)
                    <button class="filter-btn" data-filter="{{ \Illuminate\Support\Str::slug($category) }}" type="button">{{ $category }}</button>
                @endforeach
            </div>
            <div class="project-grid" id="projectGrid">
                @foreach ($landingSections['portfolio_items'] as $item)
                    @php
                        $sizeClass = match ($item['size'] ?? 'normal') {
                            'large' => 'bento-large',
                            'wide' => 'bento-wide',
                            'tall' => 'bento-tall',
                            default => '',
                        };
                        $mediaType = $item['media_type'] ?? (($item['video_url'] ?? '') !== '' ? 'video' : 'photo');
                        $mediaUrl = $item['media_url'] ?? ($item['video_url'] ?? ($item['thumbnail_url'] ?? ''));
                        $thumbUrl = $item['thumbnail_url'] ?? $mediaUrl;
                    @endphp
                    <article class="project-card {{ $sizeClass }}" data-category="{{ $item['category_slug'] ?? \Illuminate\Support\Str::slug($item['category'] ?? 'general') }}" data-media="{{ $mediaType }}">
                        <img class="project-thumb" src="{{ $thumbUrl }}" alt="{{ $item['title'] ?? 'Project' }}">
                        <div class="project-content">
                            <div class="project-meta"><h3>{{ $item['title'] ?? '' }}</h3><span>{{ $item['category'] ?? 'General' }}</span></div>
                            <p class="project-type">{{ strtoupper($mediaType) }}</p>
                            <p>{{ $item['description'] ?? '' }}</p>
                            <div class="project-links">
                                @if ($mediaType === 'video')
                                    <a href="#" class="project-link js-open-video" data-video-url="{{ $mediaUrl }}" data-video-title="{{ $item['title'] ?? 'Project' }}">▶ Play Video</a>
                                @else
                                    <a href="{{ $mediaUrl }}" class="project-link" target="_blank">▶ Open Photo</a>
                                @endif
                                <a href="{{ $item['detail_url'] ?? '#contact' }}" class="project-link">▶ Detail Project</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if (($sectionVisibility['why_us'] ?? true) === true)
    <section id="why-us" class="section">
        <div class="container">
            <h2 class="section-heading section-heading-center">{{ $landingSections['why_title'] }}</h2>
            <div class="why-grid">
                @foreach ($landingSections['why_items'] as $whyItem)
                    <article class="why-card"><p>{{ $whyItem['text'] ?? '' }}</p></article>
                @endforeach
            </div>
            <div class="counter-grid">
                <div class="counter-box"><strong class="counter-number" data-count="{{ $landingSections['counter_projects'] }}">0+</strong><span>Project</span></div>
                <div class="counter-box"><strong class="counter-number" data-count="{{ $landingSections['counter_clients'] }}">0+</strong><span>Client</span></div>
                <div class="counter-box"><strong class="counter-number" data-count="{{ $landingSections['counter_years'] }}">0+</strong><span>Tahun Pengalaman</span></div>
            </div>
        </div>
    </section>
    @endif

    @if (($sectionVisibility['testimonials'] ?? true) === true)
    <section id="testimonials" class="section">
        <div class="container">
            <h2 class="section-heading section-heading-center">{{ $landingSections['testimonial_title'] }}</h2>
            <div class="testimonial-grid">
                @foreach ($landingSections['testimonial_items'] as $testimonial)
                    <article class="testimonial-card">
                        <h3>{{ $testimonial['name'] ?? 'Client' }}</h3>
                        @if (!empty($testimonial['role']))
                            <p class="team-role">{{ $testimonial['role'] }}</p>
                        @endif
                        <p>{{ $testimonial['quote'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
            <div class="portfolio-pagination" id="portfolioPagination"></div>
            <div class="portfolio-load-more-wrap" id="portfolioLoadMoreWrap">
                <button type="button" class="btn btn-secondary" id="portfolioLoadMoreBtn">Load More</button>
            </div>
        </div>
    </section>
    @endif

    @if (($sectionVisibility['process'] ?? true) === true)
    <section id="process" class="section">
        <div class="container">
            <h2 class="section-heading section-heading-center">{{ $landingSections['process_title'] }}</h2>
            <div class="process-grid">
                @foreach ($landingSections['process_steps'] as $step)
                    <article class="process-card">
                        <div class="process-icon">{{ $step['icon'] ?? '✨' }}</div>
                        <h3>{{ $step['title'] ?? '' }}</h3>
                        <p>{{ $step['description'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if (($sectionVisibility['team'] ?? true) === true)
    <section id="team" class="section">
        <div class="container">
            <h2 class="section-heading section-heading-left">{{ $landingSections['team_title'] }}</h2>
            <div class="team-grid">
                @foreach ($landingSections['team_members'] as $member)
                    <article class="team-card">
                        <img class="team-photo" src="{{ $member['photo_url'] ?? '' }}" alt="{{ $member['role'] ?? 'Team' }}">
                        <h3>{{ $member['name'] ?? '' }}</h3>
                        <p class="team-role">{{ $member['role'] ?? '' }}</p>
                        <p class="team-caption">{{ $member['caption'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @if (($sectionVisibility['contact'] ?? true) === true)
    <section id="contact" class="cinematic-cta">
        <div class="container">
            <h2 class="section-heading section-heading-center">{{ $landingSections['contact_title'] }}</h2>
            <p>{{ $landingSections['contact_text'] }}</p>
            <div class="cta-actions">
                <a href="{{ $landingSections['contact_button_primary_url'] }}" target="_blank" class="btn btn-primary" data-magnetic>{{ $landingSections['contact_button_primary_text'] }}</a>
                <a href="{{ $landingSections['contact_button_whatsapp_url'] }}" target="_blank" class="btn btn-secondary" data-magnetic>{{ $landingSections['contact_button_whatsapp_text'] }}</a>
                <a href="{{ $landingSections['contact_button_project_url'] }}" class="btn btn-secondary" data-magnetic>{{ $landingSections['contact_button_project_text'] }}</a>
            </div>
            <div class="contact-layout">
                <form id="contactForm" class="contact-form">
                    <div class="contact-form-row">
                        <input type="text" name="name" placeholder="Nama" required>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="contact-form-row">
                        <input type="text" name="phone" placeholder="No WhatsApp">
                        <select name="service" required>
                            <option value="">Pilih Service</option>
                            <option value="Film Production">Film Production</option>
                            <option value="Video Production">Video Production</option>
                            <option value="Documentary">Documentary</option>
                            <option value="Commercial">Commercial</option>
                            <option value="Editing">Editing</option>
                            <option value="Color Grading">Color Grading</option>
                        </select>
                    </div>
                    <textarea name="message" rows="4" placeholder="Cerita singkat kebutuhan project..." required></textarea>
                    <button class="btn btn-primary" type="submit">Kirim via Ajax</button>
                    <div class="contact-form-status" id="contactFormStatus"></div>
                </form>
                <div class="contact-map">
                    <iframe
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q={{ urlencode($landingSections['contact_map_query']) }}&output=embed"
                        title="Production House Nusantara Location"
                    ></iframe>
                </div>
            </div>
        </div>
    </section>
    @endif

    <footer>
        <div class="container footer-grid">
            <div>
                @if (!empty($landingSections['footer_logo_url']))
                    <img src="{{ $landingSections['footer_logo_url'] }}" alt="{{ $landingSections['footer_brand_name'] }}" style="height:40px;width:auto;object-fit:contain;margin-bottom:.6rem;">
                @endif
                <p class="footer-title">{{ $landingSections['footer_brand_name'] }}</p>
                <p class="footer-text">{{ $landingSections['footer_address'] }}</p>
                <p class="footer-text">{{ $landingSections['footer_copyright'] }}</p>
            </div>
            <div>
                <p class="footer-title">Kontak</p>
                <p class="footer-text">WhatsApp: {{ $landingSections['footer_whatsapp'] }}</p>
                <p class="footer-text">Email: {{ $landingSections['footer_email'] }}</p>
                @if (!empty($cmsGeneral['operational_hours']))
                    <p class="footer-text">Jam Operasional: {{ $cmsGeneral['operational_hours'] }}</p>
                @endif
            </div>
            <div>
                <p class="footer-title">Sosial Media</p>
                <div class="footer-social-grid">
                    @foreach ($landingSections['footer_social_links'] as $social)
                        @php
                            $iconMap = [
                                'instagram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5m10 2H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3m-5 3.5A5.5 5.5 0 1 1 6.5 13 5.5 5.5 0 0 1 12 7.5m0 2A3.5 3.5 0 1 0 15.5 13 3.5 3.5 0 0 0 12 9.5M18.25 6a1.25 1.25 0 1 1-1.25 1.25A1.25 1.25 0 0 1 18.25 6"/></svg>',
                                'youtube' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23 12s0-3.2-.4-4.8a3.1 3.1 0 0 0-2.2-2.2C18.8 4.6 12 4.6 12 4.6s-6.8 0-8.4.4a3.1 3.1 0 0 0-2.2 2.2C1 8.8 1 12 1 12s0 3.2.4 4.8a3.1 3.1 0 0 0 2.2 2.2c1.6.4 8.4.4 8.4.4s6.8 0 8.4-.4a3.1 3.1 0 0 0 2.2-2.2C23 15.2 23 12 23 12m-13.7 3.9V8.1l6.5 3.9z"/></svg>',
                                'vimeo' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22.2 7.2c-.1 2.9-2.2 6.8-6.1 11.8-4.1 5.2-7.5 7.8-10.3 7.8-1.7 0-3.2-1.6-4.4-4.8l-2.4-8.8c-.9-3.2-1.9-4.8-2.8-4.8-.2 0-1 .5-2.2 1.5L-7 8.2c2.1-1.8 4.2-3.7 6.2-5.5 2.8-2.4 4.9-3.7 6.3-3.8 1.7-.2 2.8 1 3.2 3.7.5 2.9.8 4.7 1 5.5.6 2.4 1.3 3.6 2.1 3.6.6 0 1.5-.9 2.7-2.8 1.2-1.9 1.8-3.3 1.9-4.2.2-1.6-.5-2.4-2.1-2.4-.8 0-1.6.2-2.4.6 1.6-5.2 4.8-7.7 9.3-7.5 3.4.1 5 2.3 4.9 6.8z"/></svg>',
                                'tiktok' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 3c.4 1.8 1.5 3 3.5 3.5V9a7.2 7.2 0 0 1-3.5-1V14a5 5 0 1 1-5-5h.5v2.6H9a2.4 2.4 0 1 0 2.4 2.4V3z"/></svg>',
                                'facebook' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 22v-8h2.7l.4-3h-3.1V9.1c0-.9.2-1.6 1.5-1.6h1.7V4.8A22.3 22.3 0 0 0 14.2 4c-2.4 0-4.1 1.5-4.1 4.2V11H7.4v3h2.7v8z"/></svg>',
                                'linkedin' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4.8 3.5A1.8 1.8 0 1 1 3 5.3a1.8 1.8 0 0 1 1.8-1.8M3.2 8h3.3v12H3.2zm5.2 0h3.2v1.6h.1a3.5 3.5 0 0 1 3.2-1.8c3.4 0 4 2.2 4 5.1V20h-3.3v-6c0-1.5 0-3.4-2.1-3.4s-2.4 1.6-2.4 3.3V20H8.4z"/></svg>',
                                'reddit' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.4 14.9a1.4 1.4 0 0 1-2.8 0h-1a2.4 2.4 0 0 0 4.8 0zM9.4 13.2a1 1 0 1 0-1-1 1 1 0 0 0 1 1m5.2 0a1 1 0 1 0-1-1 1 1 0 0 0 1 1M21 11a2 2 0 0 0-3.4-1.4A7.4 7.4 0 0 0 12.8 8l1-3.1 2.7.6a1.6 1.6 0 1 0 .3-1h-.1l-3.3-.7a.5.5 0 0 0-.6.3l-1.2 3.5a7.5 7.5 0 0 0-5.2 1.6A2 2 0 1 0 5 12.8a4.8 4.8 0 0 0 0 .8c0 2.7 3.1 4.9 7 4.9s7-2.2 7-4.9a5 5 0 0 0-.1-.9A2 2 0 0 0 21 11"/></svg>',
                                'twitter' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.2 3H21l-6.1 7 7.1 11h-5.6L12 14.7 6.6 21H3.8l6.5-7.5L3.5 3h5.6l4 5.8z"/></svg>',
                                'threads' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15.8 11.2a4.3 4.3 0 0 0-4-2.6 4.4 4.4 0 0 0-4.4 4.3 4.3 4.3 0 0 0 4.4 4.3 4 4 0 0 0 4.1-3h2a6 6 0 0 1-6.1 5 6.3 6.3 0 1 1 0-12.6 6.2 6.2 0 0 1 5.8 3.9c1.2.1 2.2.8 2.2 2.2 0 2.6-2.5 4.2-5.7 4.2v-1.8c2.4 0 3.8-.8 3.8-2.3 0-.9-.6-1.6-2.1-1.6"/></svg>',
                                'whatsapp' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5.1-1.3A10 10 0 1 0 12 2m5.7 14.3c-.2.6-1.2 1.1-1.7 1.1s-1 .2-3.4-.7a11.5 11.5 0 0 1-4.5-4c-.8-1-.8-2-.1-2.6s.6-.6.9-.6h.6c.2 0 .4 0 .6.4l.8 2c.1.3 0 .5-.1.7l-.5.6a.6.6 0 0 0-.1.6 8.4 8.4 0 0 0 3.8 3.3.5.5 0 0 0 .6-.1l.8-.9c.2-.2.4-.2.7-.1l1.9.9c.3.1.5.2.5.4a2.8 2.8 0 0 1-.3 1"/></svg>',
                                'telegram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21.5 3.6-3 16.9c-.2 1.2-.9 1.5-1.9 1l-5.3-4-2.5 2.4c-.3.3-.5.5-1 .5l.4-5.5 10-9c.5-.4-.1-.7-.7-.3L5 13 0 11.4c-1.1-.3-1.1-1 .2-1.5L20 2.2c.9-.4 1.7.2 1.5 1.4"/></svg>',
                                'discord' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 5.6A16 16 0 0 0 16 4l-.2.4a11 11 0 0 1 3.2 1.6 12 12 0 0 0-14 0A11 11 0 0 1 8.2 4.4L8 4a16 16 0 0 0-4 1.6C1.4 9.4.6 13.1 1 16.8a16 16 0 0 0 4.9 2.5l1-1.6c-.6-.2-1.2-.5-1.7-.9l.4-.3a11.3 11.3 0 0 0 12.8 0l.4.3c-.5.4-1 .7-1.7.9l1 1.6A16 16 0 0 0 23 16.8c.5-4.3-.7-8-3-11.2M9.6 14.5c-.9 0-1.7-.9-1.7-1.9s.7-1.9 1.7-1.9 1.8.9 1.7 1.9-.7 1.9-1.7 1.9m4.8 0c-.9 0-1.7-.9-1.7-1.9s.7-1.9 1.7-1.9 1.8.9 1.7 1.9-.7 1.9-1.7 1.9"/></svg>',
                                'pinterest' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 0 0-3.6 19.3l1.6-6.1c-.4-.8-.7-2-.7-3.2 0-2.9 2-5.2 4.8-5.2 2.5 0 4.2 1.7 4.2 4.1 0 3.4-1.5 6.3-4.1 6.3a2.6 2.6 0 0 1-2.2-1.1l-.8 3.1a10 10 0 1 0 .8-19.2"/></svg>',
                                'snapchat' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3a4.7 4.7 0 0 0-4.8 4.7v2c0 .6-.4 1.2-1 1.5l-.8.4c-.5.3-.5 1 0 1.3l1 .5a2 2 0 0 1 1 1.6 3.6 3.6 0 0 0 2.4 3.2l.7 1.5a.7.7 0 0 0 1.3 0l.7-1.5a3.6 3.6 0 0 0 2.4-3.2 2 2 0 0 1 1-1.6l1-.5c.5-.3.5-1 0-1.3l-.8-.4a1.7 1.7 0 0 1-1-1.6v-2A4.7 4.7 0 0 0 12 3"/></svg>',
                                'github' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 .6A12 12 0 0 0 8.2 24c.6.1.8-.2.8-.6v-2.2c-3.3.7-4-1.6-4-1.6a3 3 0 0 0-1.3-1.8c-1-.6 0-.6 0-.6a2.3 2.3 0 0 1 1.7 1.2 2.4 2.4 0 0 0 3.3.9 2.3 2.3 0 0 1 .7-1.5c-2.7-.3-5.5-1.3-5.5-6a4.7 4.7 0 0 1 1.2-3.2 4.4 4.4 0 0 1 .1-3.1s1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0c2.3-1.5 3.3-1.2 3.3-1.2a4.4 4.4 0 0 1 .1 3.1 4.7 4.7 0 0 1 1.2 3.2c0 4.7-2.8 5.7-5.5 6a2.6 2.6 0 0 1 .8 2v3c0 .4.2.7.8.6A12 12 0 0 0 12 .6"/></svg>',
                                'website' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2m7.7 9h-3.1a15 15 0 0 0-1.2-5A8 8 0 0 1 19.7 11M12 4.1c.9 1.1 1.8 3 2.3 5h-4.6c.5-2 1.4-3.9 2.3-5M5.3 13h3.1a15 15 0 0 0 1.2 5A8 8 0 0 1 5.3 13m0-2A8 8 0 0 1 8.6 6a15 15 0 0 0-1.2 5zm6.7 8a12.4 12.4 0 0 1-2.3-5h4.6a12.4 12.4 0 0 1-2.3 5m2.8-1a15 15 0 0 0 1.2-5h3.1a8 8 0 0 1-4.3 5"/></svg>',
                                '📸' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5m10 2H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3m-5 3.5A5.5 5.5 0 1 1 6.5 13 5.5 5.5 0 0 1 12 7.5m0 2A3.5 3.5 0 1 0 15.5 13 3.5 3.5 0 0 0 12 9.5"/></svg>',
                                '▶' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23 12s0-3.2-.4-4.8a3.1 3.1 0 0 0-2.2-2.2C18.8 4.6 12 4.6 12 4.6s-6.8 0-8.4.4a3.1 3.1 0 0 0-2.2 2.2C1 8.8 1 12 1 12s0 3.2.4 4.8a3.1 3.1 0 0 0 2.2 2.2c1.6.4 8.4.4 8.4.4s6.8 0 8.4-.4a3.1 3.1 0 0 0 2.2-2.2C23 15.2 23 12 23 12m-13.7 3.9V8.1l6.5 3.9z"/></svg>',
                            ];
                            $iconKey = strtolower((string) ($social['icon'] ?? ''));
                            $iconSvg = $iconMap[$iconKey] ?? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 2.8 6.2 6.7.6-5 4.3 1.5 6.5L12 16.1l-6 3.5 1.5-6.5-5-4.3 6.7-.6z"/></svg>';
                            $platformKey = preg_match('/^[a-z0-9_-]+$/', $iconKey) ? $iconKey : 'website';
                        @endphp
                        <a class="footer-social-link" href="{{ $social['url'] ?? '#' }}" target="_blank" data-platform="{{ $platformKey }}">
                            <span class="social-icon">{!! $iconSvg !!}</span>
                            <span>{{ $social['name'] ?? 'Social' }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </footer>
    <div class="video-lightbox" id="videoLightbox">
        <div class="video-lightbox-panel">
            <div class="video-lightbox-head">
                <p class="video-lightbox-title" id="videoLightboxTitle">Showreel</p>
                <button type="button" class="video-lightbox-close" id="videoLightboxClose">×</button>
            </div>
            <iframe id="videoLightboxFrame" class="video-lightbox-frame" src="" title="Video Player" allowfullscreen></iframe>
        </div>
    </div>
    @if (!empty($cmsGeneral['analytics_code']))
        {!! $cmsGeneral['analytics_code'] !!}
    @endif
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/typed.js@2.1.0/dist/typed.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    @include('partials.public-header-script')
    <script>
        const heroRoot = document.getElementById('home');
        const heroSlides = document.querySelectorAll('.hero-slide');
        const heroOverlay = document.querySelector('.hero-overlay');
        const heroContent = document.querySelector('.hero-content');
        const heroGlowElements = document.querySelectorAll('.hero-glow');
        const heroPrevBtn = document.getElementById('heroPrevBtn');
        const heroNextBtn = document.getElementById('heroNextBtn');
        const autoplayDefault = Number(heroRoot?.dataset.autoplayMs || 7000) || 7000;
        const autoPlayEnabled = heroRoot?.dataset.autoPlay === '1';
        const loopEnabled = heroRoot?.dataset.loop !== '0';
        const transitionEffect = heroRoot?.dataset.transitionEffect || 'fade';
        const mobileDisableVideo = heroRoot?.dataset.mobileDisableVideo === '1';
        const parallaxStrength = Number(heroRoot?.dataset.parallaxStrength || 0.06) || 0.06;
        const glowIntensity = Number(heroRoot?.dataset.glowIntensity || 1) || 1;
        const isMobileViewport = window.matchMedia('(max-width: 767px)').matches;
        if (heroRoot) {
            heroRoot.classList.remove('effect-fade', 'effect-slide', 'effect-cube');
            heroRoot.classList.add(`effect-${transitionEffect}`);
        }
        heroGlowElements.forEach((glowElement) => {
            glowElement.style.opacity = `${glowIntensity}`;
        });
        let activeIndex = 0;
        let isPlaying = autoPlayEnabled;
        let autoplayTimer = null;

        const getSlide = (index) => heroSlides[index] || null;
        const getDuration = (index) => {
            const slide = getSlide(index);
            const configuredDuration = Number(slide?.dataset.duration || 0);
            if (configuredDuration > 0) {
                return Math.max(2000, configuredDuration * 1000);
            }
            const mediaDuration = Number((slide instanceof HTMLVideoElement ? slide.duration : 0) || 0);
            if (Number.isFinite(mediaDuration) && mediaDuration > 0) {
                return mediaDuration * 1000;
            }

            return autoplayDefault;
        };
        const applySlideVisualState = () => {
            const activeSlide = getSlide(activeIndex);
            if (!activeSlide) {
                return;
            }
            const overlay = Number(activeSlide.dataset.overlay || 0.78);
            const overlayColor = activeSlide.dataset.overlayColor || '#000000';
            const textPosition = activeSlide.dataset.textPosition || 'center';
            const fallbackImage = activeSlide.dataset.mobileFallback || '';
            if (heroOverlay) {
                heroOverlay.style.setProperty('--hero-overlay-opacity', `${overlay}`);
                heroOverlay.style.setProperty('--hero-overlay-color', overlayColor);
            }
            if (heroContent) {
                heroContent.classList.remove('pos-left', 'pos-center', 'pos-right');
                heroContent.classList.add(`pos-${textPosition}`);
            }
            if (heroRoot && mobileDisableVideo && isMobileViewport && fallbackImage !== '') {
                heroRoot.classList.add('hero-mobile-fallback');
                heroRoot.style.backgroundImage = `url('${fallbackImage}')`;
                heroRoot.style.backgroundSize = 'cover';
                heroRoot.style.backgroundPosition = 'center';
            }
        };
        const playCurrent = () => {
            const current = heroSlides[activeIndex];
            if (!current) {
                return;
            }
            if (mobileDisableVideo && isMobileViewport) {
                return;
            }
            const tabletVideo = current.dataset.tabletVideo || '';
            const sourceElement = current.querySelector('source');
            if (sourceElement instanceof HTMLSourceElement && window.matchMedia('(min-width: 768px) and (max-width: 1199px)').matches && tabletVideo !== '' && sourceElement.src !== tabletVideo) {
                sourceElement.src = tabletVideo;
                current.load();
            }
            current.play().catch(() => null);
        };

        const setSlide = (nextIndex) => {
            if (!heroSlides.length) {
                return;
            }
            heroSlides[activeIndex].classList.remove('active');
            heroSlides[activeIndex].pause();
            if (loopEnabled) {
                activeIndex = (nextIndex + heroSlides.length) % heroSlides.length;
            } else {
                activeIndex = Math.max(0, Math.min(heroSlides.length - 1, nextIndex));
            }
            heroSlides[activeIndex].classList.add('active');
            if (isPlaying) {
                playCurrent();
            }
            applySlideVisualState();
        };

        const startAutoplay = () => {
            if (heroSlides.length <= 1) {
                return;
            }
            if (!loopEnabled && activeIndex >= heroSlides.length - 1) {
                return;
            }
            clearTimeout(autoplayTimer);
            autoplayTimer = setTimeout(() => {
                setSlide(activeIndex + 1);
                startAutoplay();
            }, getDuration(activeIndex));
        };
        heroSlides.forEach((slide, slideIndex) => {
            slide.addEventListener('loadedmetadata', () => {
                const configuredDuration = Number(slide.dataset.duration || 0);
                if (!isPlaying || configuredDuration > 0 || slideIndex !== activeIndex) {
                    return;
                }
                startAutoplay();
            });
        });

        if (heroSlides.length) {
            applySlideVisualState();
            if (isPlaying) {
                playCurrent();
            }
        }

        if (heroSlides.length > 1) {
            if (isPlaying) {
                startAutoplay();
            }
        }
        const jumpSlide = (direction) => {
            if (heroSlides.length <= 1) {
                return;
            }
            setSlide(activeIndex + direction);
            if (isPlaying) {
                startAutoplay();
            }
        };
        heroPrevBtn?.addEventListener('click', () => jumpSlide(-1));
        heroNextBtn?.addEventListener('click', () => jumpSlide(1));

        const portfolioSection = document.getElementById('portfolio');
        const projectGrid = document.getElementById('projectGrid');
        const mediaFilterRoot = document.getElementById('mediaFilter');
        const categoryFilterRoot = document.getElementById('categoryFilter');
        const portfolioPagination = document.getElementById('portfolioPagination');
        const portfolioLoadMoreWrap = document.getElementById('portfolioLoadMoreWrap');
        const portfolioLoadMoreBtn = document.getElementById('portfolioLoadMoreBtn');
        const categoryFilterButtons = document.querySelectorAll('.category-filter .filter-btn');
        const mediaFilterButtons = document.querySelectorAll('.media-filter .filter-btn');
        const projectCards = projectGrid ? projectGrid.querySelectorAll('.project-card') : [];
        const openVideoButtons = document.querySelectorAll('.js-open-video');
        const lightbox = document.getElementById('videoLightbox');
        const lightboxFrame = document.getElementById('videoLightboxFrame');
        const lightboxTitle = document.getElementById('videoLightboxTitle');
        const lightboxClose = document.getElementById('videoLightboxClose');
        const velocityStage = document.getElementById('velocityStage');
        const velocityTrack = document.getElementById('velocityTrack');
        const velocitySlides = velocityTrack ? velocityTrack.querySelectorAll('.velocity-slide') : [];
        const velocityControls = document.getElementById('velocityControls');
        const velocityParticleCanvas = document.getElementById('velocityParticleCanvas');
        const counterNumbers = document.querySelectorAll('.counter-number');
        const scrollProgress = document.getElementById('scrollProgress');
        const interactiveCards = document.querySelectorAll('.service-card, .why-card, .testimonial-card, .team-card, .process-card');
        const magneticButtons = document.querySelectorAll('[data-magnetic]');
        const contactForm = document.getElementById('contactForm');
        const contactFormStatus = document.getElementById('contactFormStatus');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const finePointer = window.matchMedia('(pointer:fine)').matches;
        const portfolioGridMode = portfolioSection?.dataset.gridMode || 'cinematic';
        const portfolioHoverEffect = portfolioSection?.dataset.hoverEffect || 'overlay';
        const portfolioLoadStrategy = portfolioSection?.dataset.loadStrategy || 'none';
        const portfolioItemsPerPage = Math.max(1, Number(portfolioSection?.dataset.itemsPerPage || 8) || 8);
        const portfolioEnableAjaxFilter = (portfolioSection?.dataset.enableAjaxFilter || '1') === '1';
        let portfolioVisibleLimit = portfolioItemsPerPage;
        let portfolioPage = 1;
        const resolveFilteredCards = () => [...projectCards].filter((card) => {
            const category = card.getAttribute('data-category');
            const mediaType = card.getAttribute('data-media');
            const visibleCategory = currentCategoryFilter === 'all' || currentCategoryFilter === category;
            const visibleMedia = currentMediaFilter === 'all' || currentMediaFilter === mediaType;
            const visibleByMode = portfolioGridMode !== 'video' || mediaType === 'video';

            return visibleCategory && visibleMedia && visibleByMode;
        });

        const setActiveProjectCard = (card) => {
            projectCards.forEach((item) => item.classList.remove('is-active'));
            if (card) {
                card.classList.add('is-active');
            }
        };

        if (portfolioSection) {
            portfolioSection.classList.remove('overlay', 'lift', 'zoom');
            portfolioSection.classList.add(portfolioHoverEffect);
        }
        if (portfolioGridMode === 'masonry' || portfolioGridMode === 'cinematic') {
            setActiveProjectCard(projectGrid ? projectGrid.querySelector('.project-card.bento-large') : null);
        } else {
            setActiveProjectCard(projectGrid ? projectGrid.querySelector('.project-card') : null);
        }
        let currentCategoryFilter = 'all';
        let currentMediaFilter = 'all';
        let velocityIndex = 0;
        const velocityDots = [];
        const applyVelocityFilterVisibility = () => {
            velocitySlides.forEach((slide) => {
                const category = slide.getAttribute('data-category') || 'general';
                const mediaType = slide.getAttribute('data-media') || 'video';
                const visibleCategory = currentCategoryFilter === 'all' || currentCategoryFilter === category;
                const visibleMedia = currentMediaFilter === 'all' || currentMediaFilter === mediaType;
                slide.style.display = visibleCategory && visibleMedia ? '' : 'none';
            });
        };
        const getVisibleVelocitySlides = () => [...velocitySlides].filter((slide) => slide.style.display !== 'none');
        const setVelocitySlide = (nextIndex) => {
            if (!velocityTrack) {
                return;
            }
            const visibleSlides = getVisibleVelocitySlides();
            if (!visibleSlides.length) {
                velocityTrack.style.transform = 'translate3d(0,0,0)';
                return;
            }
            velocityIndex = (nextIndex + visibleSlides.length) % visibleSlides.length;
            const activeSlide = visibleSlides[velocityIndex];
            const activeOriginalIndex = [...velocitySlides].indexOf(activeSlide);
            const shift = activeOriginalIndex * 100;
            velocityTrack.style.transform = `translate3d(-${shift}%,0,0)`;
            velocitySlides.forEach((slide) => slide.classList.remove('is-active'));
            activeSlide.classList.add('is-active');
            velocityDots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === velocityIndex));
        };
        const rebuildVelocityControls = () => {
            if (!velocityControls) {
                return;
            }
            velocityControls.innerHTML = '';
            velocityDots.length = 0;
            const visibleSlides = getVisibleVelocitySlides();
            visibleSlides.forEach((_, index) => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.className = `velocity-dot${index === 0 ? ' is-active' : ''}`;
                dot.addEventListener('click', () => setVelocitySlide(index));
                velocityControls.appendChild(dot);
                velocityDots.push(dot);
            });
            velocityIndex = 0;
            setVelocitySlide(0);
        };
        const renderPortfolioPagination = (totalItems) => {
            if (!(portfolioPagination instanceof HTMLElement)) {
                return;
            }
            portfolioPagination.innerHTML = '';
            if (portfolioLoadStrategy !== 'pagination') {
                portfolioPagination.style.display = 'none';
                return;
            }
            const pageCount = Math.max(1, Math.ceil(totalItems / portfolioItemsPerPage));
            if (pageCount <= 1) {
                portfolioPagination.style.display = 'none';
                return;
            }
            portfolioPagination.style.display = 'flex';
            for (let i = 1; i <= pageCount; i += 1) {
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = `${i}`;
                button.classList.toggle('active', i === portfolioPage);
                button.addEventListener('click', () => {
                    portfolioPage = i;
                    applyPortfolioFilters();
                });
                portfolioPagination.appendChild(button);
            }
        };

        const applyPortfolioFilters = () => {
            const filteredCards = resolveFilteredCards();
            const totalItems = filteredCards.length;
            if (portfolioLoadStrategy === 'pagination') {
                const pageCount = Math.max(1, Math.ceil(totalItems / portfolioItemsPerPage));
                if (portfolioPage > pageCount) {
                    portfolioPage = pageCount;
                }
            }
            const startIndex = portfolioLoadStrategy === 'pagination' ? (portfolioPage - 1) * portfolioItemsPerPage : 0;
            const endIndex = portfolioLoadStrategy === 'pagination'
                ? startIndex + portfolioItemsPerPage
                : (portfolioLoadStrategy === 'none' ? totalItems : portfolioVisibleLimit);
            const visibleSet = new Set(filteredCards.slice(startIndex, endIndex));

            projectCards.forEach((card) => {
                const visible = visibleSet.has(card);
                card.classList.remove('filtered-in', 'filtered-out');
                if (visible) {
                    card.style.display = '';
                    requestAnimationFrame(() => card.classList.add('filtered-in'));
                } else {
                    card.classList.add('filtered-out');
                    setTimeout(() => {
                        if (!visibleSet.has(card)) {
                            card.style.display = 'none';
                        }
                    }, 280);
                }
            });

            const firstVisibleCard = [...visibleSet][0] || null;
            setActiveProjectCard(firstVisibleCard || null);
            applyVelocityFilterVisibility();
            rebuildVelocityControls();
            renderPortfolioPagination(totalItems);
            if (portfolioLoadMoreWrap instanceof HTMLElement) {
                const showLoadMore = (portfolioLoadStrategy === 'load_more' || portfolioLoadStrategy === 'infinite') && endIndex < totalItems;
                portfolioLoadMoreWrap.style.display = showLoadMore ? 'flex' : 'none';
            }
        };

        const openLightbox = (videoUrl, title) => {
            if (!lightbox || !lightboxFrame) {
                return;
            }
            lightboxTitle.textContent = title || 'Showreel';
            lightboxFrame.src = `${videoUrl}${videoUrl.includes('?') ? '&' : '?'}autoplay=1`;
            lightbox.classList.add('open');
            document.body.style.overflow = 'hidden';
        };

        const closeLightbox = () => {
            if (!lightbox || !lightboxFrame) {
                return;
            }
            lightbox.classList.remove('open');
            lightboxFrame.src = '';
            document.body.style.overflow = '';
        };

        openVideoButtons.forEach((button) => {
            button.addEventListener('click', (event) => {
                event.preventDefault();
                const url = button.getAttribute('data-video-url');
                const title = button.getAttribute('data-video-title') || 'Showreel';
                if (url) {
                    openLightbox(url, title);
                }
            });
        });

        lightboxClose?.addEventListener('click', closeLightbox);
        lightbox?.addEventListener('click', (event) => {
            if (event.target === lightbox) {
                closeLightbox();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeLightbox();
            }
        });

        categoryFilterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                portfolioPage = 1;
                portfolioVisibleLimit = portfolioItemsPerPage;
                currentCategoryFilter = button.dataset.filter || 'all';
                categoryFilterButtons.forEach((btn) => btn.classList.remove('active'));
                button.classList.add('active');
                applyPortfolioFilters();
            });
        });

        mediaFilterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                portfolioPage = 1;
                portfolioVisibleLimit = portfolioItemsPerPage;
                currentMediaFilter = button.dataset.mediaFilter || 'all';
                mediaFilterButtons.forEach((btn) => btn.classList.remove('active'));
                button.classList.add('active');
                applyPortfolioFilters();
            });
        });

        const shouldShowFilter = portfolioEnableAjaxFilter || portfolioGridMode === 'filterable';
        if (mediaFilterRoot instanceof HTMLElement) {
            mediaFilterRoot.style.display = shouldShowFilter ? '' : 'none';
        }
        if (categoryFilterRoot instanceof HTMLElement) {
            categoryFilterRoot.style.display = shouldShowFilter ? '' : 'none';
        }
        if (!shouldShowFilter) {
            currentCategoryFilter = 'all';
            currentMediaFilter = 'all';
            categoryFilterButtons.forEach((btn) => btn.classList.toggle('active', btn.dataset.filter === 'all'));
            mediaFilterButtons.forEach((btn) => btn.classList.toggle('active', btn.dataset.mediaFilter === 'all'));
        }
        if (portfolioGridMode !== 'cinematic' && portfolioGridMode !== 'video') {
            velocityStage?.style.setProperty('display', 'none');
        } else {
            velocityStage?.style.removeProperty('display');
        }
        portfolioLoadMoreBtn?.addEventListener('click', () => {
            portfolioVisibleLimit += portfolioItemsPerPage;
            applyPortfolioFilters();
        });
        if (portfolioLoadStrategy === 'infinite') {
            window.addEventListener('scroll', () => {
                if (!(portfolioLoadMoreWrap instanceof HTMLElement) || portfolioLoadMoreWrap.style.display === 'none') {
                    return;
                }
                const rect = portfolioLoadMoreWrap.getBoundingClientRect();
                if (rect.top <= window.innerHeight + 80) {
                    portfolioVisibleLimit += portfolioItemsPerPage;
                    applyPortfolioFilters();
                }
            });
        }

        projectCards.forEach((card) => {
            card.addEventListener('mouseenter', () => setActiveProjectCard(card));
            card.addEventListener('click', () => setActiveProjectCard(card));
        });

        applyPortfolioFilters();

        if (velocitySlides.length > 0) {
            if (portfolioGridMode === 'cinematic' || portfolioGridMode === 'video') {
                applyVelocityFilterVisibility();
                rebuildVelocityControls();
            }
            setInterval(() => {
                const visibleSlides = getVisibleVelocitySlides();
                if (visibleSlides.length > 1) {
                    setVelocitySlide(velocityIndex + 1);
                }
            }, 4200);
        }
        if (velocityStage && velocityParticleCanvas && !reduceMotion) {
            const ctx = velocityParticleCanvas.getContext('2d');
            if (ctx) {
                let frameId = 0;
                const particles = Array.from({ length: 52 }, () => ({
                    x: Math.random(),
                    y: Math.random(),
                    z: Math.random(),
                    vx: (Math.random() - 0.5) * 0.0009,
                    vy: (Math.random() - 0.5) * 0.0009,
                }));
                const resizeVelocityCanvas = () => {
                    const rect = velocityStage.getBoundingClientRect();
                    const ratio = window.devicePixelRatio || 1;
                    velocityParticleCanvas.width = Math.floor(rect.width * ratio);
                    velocityParticleCanvas.height = Math.floor(rect.height * ratio);
                    velocityParticleCanvas.style.width = `${rect.width}px`;
                    velocityParticleCanvas.style.height = `${rect.height}px`;
                    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
                };
                const drawParticles = () => {
                    const width = velocityStage.clientWidth;
                    const height = velocityStage.clientHeight;
                    ctx.clearRect(0, 0, width, height);
                    particles.forEach((particle) => {
                        particle.x += particle.vx;
                        particle.y += particle.vy;
                        if (particle.x < 0 || particle.x > 1) {
                            particle.vx *= -1;
                        }
                        if (particle.y < 0 || particle.y > 1) {
                            particle.vy *= -1;
                        }
                    });
                    for (let i = 0; i < particles.length; i += 1) {
                        const a = particles[i];
                        const ax = a.x * width;
                        const ay = a.y * height;
                        const ar = 1.1 + a.z * 1.8;
                        ctx.fillStyle = `rgba(242, 230, 217, ${0.4 + (a.z * 0.35)})`;
                        ctx.beginPath();
                        ctx.arc(ax, ay, ar, 0, Math.PI * 2);
                        ctx.fill();
                        for (let j = i + 1; j < particles.length; j += 1) {
                            const b = particles[j];
                            const bx = b.x * width;
                            const by = b.y * height;
                            const dx = ax - bx;
                            const dy = ay - by;
                            const dist = Math.hypot(dx, dy);
                            if (dist < 130) {
                                ctx.strokeStyle = `rgba(217, 138, 43, ${0.14 - dist / 950})`;
                                ctx.lineWidth = 1;
                                ctx.beginPath();
                                ctx.moveTo(ax, ay);
                                ctx.lineTo(bx, by);
                                ctx.stroke();
                            }
                        }
                    }
                    frameId = requestAnimationFrame(drawParticles);
                };
                resizeVelocityCanvas();
                drawParticles();
                window.addEventListener('resize', resizeVelocityCanvas);
                window.addEventListener('beforeunload', () => cancelAnimationFrame(frameId));
            }
        }

        const animateCounter = (node) => {
            const target = Number(node.getAttribute('data-count') || 0);
            const duration = 1600;
            const startTime = performance.now();
            const tick = (now) => {
                const progress = Math.min(1, (now - startTime) / duration);
                const value = Math.floor(progress * target);
                node.textContent = `${value}+`;
                if (progress < 1) {
                    requestAnimationFrame(tick);
                } else {
                    node.textContent = `${target}+`;
                }
            };
            requestAnimationFrame(tick);
        };

        const counterObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.4 });

        counterNumbers.forEach((counter) => counterObserver.observe(counter));

        const sections = document.querySelectorAll('.section, .cinematic-cta');
        const revealNodes = document.querySelectorAll('.section .section-tag, .section h2, .section p, .section article, .section img, .section iframe, .section button, .cinematic-cta h2, .cinematic-cta p, .cinematic-cta .btn');
        revealNodes.forEach((node, index) => {
            node.classList.add('reveal-item');
            node.style.setProperty('--reveal-delay', `${Math.min(index % 8, 7) * 70}ms`);
        });

        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    if (entry.target.classList.contains('section') || entry.target.classList.contains('cinematic-cta')) {
                        entry.target.querySelectorAll('.reveal-item').forEach((item) => item.classList.add('revealed'));
                    }
                }
            });
        }, { threshold: 0.15, rootMargin: '0px 0px -10% 0px' });

        sections.forEach((section) => revealObserver.observe(section));

        const parallaxNodes = document.querySelectorAll('[data-parallax]');
        const applySnapMode = () => {
            const shouldEnable = !reduceMotion && window.innerWidth >= 1024;
            document.documentElement.classList.toggle('snap-enabled', shouldEnable);
        };
        const updateScrollProgress = () => {
            if (!scrollProgress) {
                return;
            }
            const scrollTop = window.scrollY || 0;
            const docHeight = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
            const progress = Math.min(100, (scrollTop / docHeight) * 100);
            scrollProgress.style.width = `${progress}%`;
        };
        const parallaxTick = () => {
            const scrollY = window.scrollY || 0;
            parallaxNodes.forEach((node) => {
                const speed = Number(node.getAttribute('data-parallax')) || 0;
                node.style.transform = `translate3d(0, ${scrollY * speed}px, 0)`;
            });
            if (heroContent) {
                heroContent.style.transform = `translate3d(0, ${scrollY * parallaxStrength}px, 0)`;
            }
            updateScrollProgress();
        };

        if (!reduceMotion) {
            interactiveCards.forEach((card) => {
                card.addEventListener('pointermove', (event) => {
                    const rect = card.getBoundingClientRect();
                    const x = (event.clientX - rect.left) / rect.width;
                    const y = (event.clientY - rect.top) / rect.height;
                    const tiltX = (0.5 - y) * 5;
                    const tiltY = (x - 0.5) * 6;
                    card.style.transform = `translateY(-4px) rotateX(${tiltX}deg) rotateY(${tiltY}deg)`;
                });
                card.addEventListener('pointerleave', () => {
                    card.style.transform = '';
                });
            });
        }
        if (!reduceMotion && finePointer) {
            magneticButtons.forEach((button) => {
                button.addEventListener('pointermove', (event) => {
                    const rect = button.getBoundingClientRect();
                    const dx = event.clientX - (rect.left + rect.width / 2);
                    const dy = event.clientY - (rect.top + rect.height / 2);
                    button.style.transform = `translate3d(${dx * 0.12}px, ${dy * 0.12}px, 0)`;
                });
                button.addEventListener('pointerleave', () => {
                    button.style.transform = '';
                });
            });
        }
        contactForm?.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!(contactForm instanceof HTMLFormElement)) {
                return;
            }
            const submitButton = contactForm.querySelector('button[type="submit"]');
            const formData = new FormData(contactForm);
            if (submitButton) {
                submitButton.setAttribute('disabled', 'disabled');
            }
            if (contactFormStatus) {
                contactFormStatus.textContent = 'Mengirim pesan...';
            }
            try {
                const response = await fetch("{{ route('contact.submit') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const result = await response.json();
                if (!response.ok) {
                    throw new Error(result?.message || 'Gagal mengirim pesan.');
                }
                contactForm.reset();
                if (contactFormStatus) {
                    contactFormStatus.textContent = result.message || 'Pesan terkirim.';
                }
            } catch (error) {
                if (contactFormStatus) {
                    contactFormStatus.textContent = error instanceof Error ? error.message : 'Terjadi kesalahan.';
                }
            } finally {
                if (submitButton) {
                    submitButton.removeAttribute('disabled');
                }
            }
        });

        document.querySelectorAll('img:not(.hero-slide):not([loading])').forEach((image) => {
            image.setAttribute('loading', 'lazy');
            image.setAttribute('decoding', 'async');
        });

        if (window.Typed) {
            const taglineNode = document.querySelector('.tagline');
            if (taglineNode) {
                const base = taglineNode.textContent || '';
                taglineNode.textContent = '';
                new Typed(taglineNode, {
                    strings: [base, 'Authentic Heritage • Modern Vision • Cinematic Excellence'],
                    typeSpeed: 36,
                    backSpeed: 18,
                    backDelay: 1700,
                    loop: true,
                });
            }
        }

        if (window.gsap && !reduceMotion) {
            window.gsap.from('.hero-badges .hero-badge', {
                y: 24,
                opacity: 0,
                duration: 1,
                stagger: 0.12,
                ease: 'power3.out',
            });
        }

        if (window.AOS) {
            sections.forEach((section) => section.setAttribute('data-aos', 'fade-up'));
            window.AOS.init({
                duration: 700,
                once: true,
                offset: 80,
            });
        }

        applySnapMode();

        parallaxTick();
        window.addEventListener('scroll', parallaxTick, { passive: true });
        window.addEventListener('resize', () => {
            applySnapMode();
            parallaxTick();
        });
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                clearTimeout(autoplayTimer);
            } else if (isPlaying) {
                playCurrent();
                startAutoplay();
            }
        });
        window.addEventListener('beforeunload', () => clearTimeout(autoplayTimer));
    </script>
</body>
</html>
