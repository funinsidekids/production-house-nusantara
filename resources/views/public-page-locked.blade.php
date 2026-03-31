<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Protected Preview · {{ $cmsGeneral['site_title'] }}</title>
    <style>
@include('partials.public-base-css')
@include('partials.public-ui-primitives-css')
        body {
            background: radial-gradient(circle at 15% 0%, rgba(224,171,86,.18), transparent 40%), #060606;
            color: #f8f4eb;
        }
@include('partials.public-header-css')
        .wrap {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 8rem 1rem 2rem;
        }
        .card {
            width: min(520px, 100%);
            border: 1px solid rgba(255,255,255,.15);
            background: rgba(0,0,0,.55);
            --public-card-radius: .9rem;
            --public-card-padding: 1.15rem;
        }
        h1 {
            margin: 0 0 .45rem;
            font-size: 1.45rem;
        }
        p {
            margin: 0 0 .8rem;
            color: rgba(248,244,235,.78);
        }
        button {
            margin-top: .65rem;
        }
    </style>
</head>
<body>
@include('partials.public-header-markup', [
    'headerConfig' => $headerConfig,
    'isInnerPage' => $isInnerPage ?? true,
    'headerBrandHref' => '/',
    'headerBrandText' => $cmsGeneral['site_title'],
    'headerBrandAlt' => $cmsGeneral['site_title'],
])
<div class="wrap">
    <div class="card ui-card">
        <h1>Protected Preview</h1>
        <p>Halaman ini dikunci. Masukkan password preview dari CMS Pages.</p>
        <form method="GET">
            <input class="ui-input" type="password" name="{{ $unlockParam }}" placeholder="Preview password" required>
            <button class="ui-btn ui-btn-accent" type="submit">Unlock</button>
        </form>
    </div>
</div>
@include('partials.public-header-script')
</body>
</html>
