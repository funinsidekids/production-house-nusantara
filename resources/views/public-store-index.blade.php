<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('store.index') }}">PHN Store</a>
            <div class="ms-auto d-flex gap-2">
                <a class="btn btn-outline-light btn-sm" href="{{ route('home') }}">Home</a>
                <a class="btn btn-warning btn-sm" href="{{ route('store.cart') }}">Cart ({{ $cartCount }})</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0">Store Catalog</h3>
            <a class="btn btn-primary" href="{{ route('store.cart') }}">Lihat Cart</a>
        </div>

        <div class="row g-3">
            @forelse ($products as $product)
                <div class="col-md-4">
                    <div class="card h-100">
                        @if (($product['thumbnail'] ?? '') !== '')
                            <img src="{{ $product['thumbnail'] }}" class="card-img-top" style="height:180px;object-fit:cover;" alt="{{ $product['name'] }}">
                        @endif
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-secondary mb-2">{{ strtoupper($product['type']) }}</span>
                            <h5 class="card-title">{{ $product['name'] }}</h5>
                            <p class="card-text text-muted small">{{ \Illuminate\Support\Str::limit($product['description'] ?? '', 120) }}</p>
                            <div class="mt-auto">
                                @php $isFree = ((float) ($product['price'] ?? 0)) <= 0; @endphp
                                <div class="fw-bold mb-2">{{ $isFree ? 'GRATIS' : ($currency.' '.number_format((float) ($product['price'] ?? 0), 0, ',', '.')) }}</div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('store.show', ['slug' => $product['slug']]) }}" class="btn btn-outline-primary btn-sm">Detail</a>
                                    <form method="POST" action="{{ route('store.purchase', ['slug' => $product['slug']]) }}" class="w-100">
                                        @csrf
                                        <input type="email" class="form-control form-control-sm mb-2" name="customer_email" placeholder="email@kamu.com" required>
                                        <button class="btn btn-primary btn-sm w-100" type="submit">{{ $isFree ? 'Ambil Gratis' : 'Beli Sekarang' }}</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info mb-0">Belum ada product published.</div>
                </div>
            @endforelse
        </div>
    </div>
</body>
</html>
