<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $cmsGeneral['site_title'] }} - Maintenance</title>
    <meta name="description" content="{{ $cmsGeneral['site_description'] !== '' ? $cmsGeneral['site_description'] : $cmsGeneral['tagline'] }}">
    <style>
@include('partials.public-base-css')
@include('partials.public-ui-primitives-css')
        body {
            background: radial-gradient(circle at 10% 10%, #ffe7c2, #fff8ea 45%, #fffdf7);
            color: #4a311e;
            padding: 0;
            overflow-x: clip;
        }
@include('partials.public-header-css')
        .maintenance-wrap {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 8rem 1.5rem 2rem;
        }
        .card {
            width: min(720px, 100%);
            background: #fffefb;
            border: 1px solid rgba(147, 95, 50, 0.2);
            box-shadow: 0 16px 36px rgba(129, 80, 39, 0.12);
            --public-card-radius: 1rem;
            --public-card-padding: 1.5rem;
            --public-card-bg: #fffefb;
        }
        h1 {
            margin: 0 0 .4rem;
            font-size: clamp(1.4rem, 3vw, 2rem);
        }
        p {
            margin: 0 0 .75rem;
            color: #75583f;
        }
        .muted {
            color: #8f725a;
            font-size: .95rem;
        }
        .list {
            margin: 1rem 0 0;
            display: grid;
            gap: .45rem;
        }
        .social {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
        }
        .social a {
            color: #7b4621;
            text-decoration: none;
            border: 1px solid rgba(147, 95, 50, 0.25);
            background: #fff6e9;
            font-size: .9rem;
        }
    </style>
</head>
<body>
    @include('partials.public-header-markup', [
        'headerConfig' => $headerConfig ?? [],
        'isInnerPage' => true,
        'headerBrandHref' => '/',
        'headerBrandText' => $cmsGeneral['site_title'] ?? 'Production House Nusantara',
        'headerBrandAlt' => $cmsGeneral['site_title'] ?? 'Production House Nusantara',
    ])
    <div class="maintenance-wrap">
        <div class="card ui-card">
            <h1>{{ $cmsGeneral['site_title'] }}</h1>
            <p>{{ $cmsGeneral['tagline'] }}</p>
            <p class="muted">Website sedang dalam maintenance. Kami akan kembali online secepatnya.</p>

            <div class="list">
                @if ($cmsGeneral['contact_email'] !== '')
                    <div>Email: {{ $cmsGeneral['contact_email'] }}</div>
                @endif
                @if ($cmsGeneral['contact_phone'] !== '')
                    <div>Phone/WhatsApp: {{ $cmsGeneral['contact_phone'] }}</div>
                @endif
                @if ($cmsGeneral['office_address'] !== '')
                    <div>Alamat: {{ $cmsGeneral['office_address'] }}</div>
                @endif
                @if ($cmsGeneral['operational_hours'] !== '')
                    <div>Jam Operasional: {{ $cmsGeneral['operational_hours'] }}</div>
                @endif
            </div>

            @if (!empty($cmsSocialLinks))
                <div class="social">
                    @foreach ($cmsSocialLinks as $social)
                        <a class="ui-chip" href="{{ $social['url'] }}" target="_blank">{{ $social['name'] }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    @include('partials.public-header-script')
</body>
</html>
