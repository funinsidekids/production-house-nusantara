<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $product['name'] }} - Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('store.index') }}">PHN Store</a>
            <div class="ms-auto d-flex gap-2">
                <a class="btn btn-outline-light btn-sm" href="{{ route('store.index') }}">Catalog</a>
                <a class="btn btn-warning btn-sm" href="{{ route('store.cart') }}">Cart ({{ $cartCount }})</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="card">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-5">
                        @if (($product['photo'] ?? '') !== '')
                            <img src="{{ $product['photo'] }}" class="img-fluid rounded border" alt="{{ $product['name'] }}">
                        @elseif (($product['thumbnail'] ?? '') !== '')
                            <img src="{{ $product['thumbnail'] }}" class="img-fluid rounded border" alt="{{ $product['name'] }}">
                        @endif
                    </div>
                    <div class="col-md-7">
                        <span class="badge bg-secondary mb-2">{{ strtoupper($product['type']) }}</span>
                        <h3>{{ $product['name'] }}</h3>
                        <div class="mb-2 text-muted">SKU: {{ $product['sku'] ?: '-' }}</div>
                        <div class="mb-2 text-muted">Category: {{ $product['category'] ?: '-' }}</div>
                        <div class="mb-2 text-muted">License: {{ $product['license_type'] ?: '-' }}</div>
                        @php $isFree = ((float) ($product['price'] ?? 0)) <= 0; @endphp
                        <div class="h4 mb-3">{{ $isFree ? 'GRATIS' : ($currency.' '.number_format((float) ($product['price'] ?? 0), 0, ',', '.')) }}</div>
                        <p>{{ $product['description'] ?: 'Tidak ada deskripsi.' }}</p>
                        <p class="small text-muted mb-3">{{ $product['function'] ?: '' }}</p>

                        <form method="POST" action="{{ route('store.purchase', ['slug' => $product['slug']]) }}" class="d-grid gap-2">
                            @csrf
                            <input class="form-control" name="customer_name" placeholder="Nama (opsional)">
                            <input class="form-control" name="customer_phone" placeholder="No HP (opsional)">
                            <input type="email" class="form-control" name="customer_email" placeholder="Email untuk link download" required>
                            <textarea class="form-control" rows="2" name="customer_note" placeholder="Catatan (opsional)"></textarea>
                            <button class="btn btn-primary" type="submit">{{ $isFree ? 'Ambil Gratis' : 'Lanjut Beli' }}</button>
                            <a class="btn btn-outline-secondary" href="{{ route('store.index') }}">Kembali ke Catalog</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
