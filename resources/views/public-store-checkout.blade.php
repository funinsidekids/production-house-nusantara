<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ route('store.index') }}">PHN Store</a>
            <div class="ms-auto d-flex gap-2">
                <a class="btn btn-outline-light btn-sm" href="{{ route('store.cart') }}">Back to Cart</a>
                <span class="btn btn-warning btn-sm disabled">Cart ({{ $cartCount }})</span>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">Data Pembeli</h5>
                        <form method="POST" action="{{ route('store.checkout.place-order') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nama</label>
                                <input class="form-control" name="customer_name" value="{{ old('customer_name') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input class="form-control" type="email" name="customer_email" value="{{ old('customer_email') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">No HP</label>
                                <input class="form-control" name="customer_phone" value="{{ old('customer_phone') }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Catatan</label>
                                <textarea class="form-control" rows="3" name="customer_note">{{ old('customer_note') }}</textarea>
                            </div>
                            <div class="mb-2 text-muted small">Metode checkout aktif: {{ strtoupper($checkoutMode) }}</div>
                            @if ($checkoutMode === 'doku')
                                <div class="alert alert-info py-2 small">Setelah klik Buat Order, kamu akan diarahkan ke halaman pembayaran DOKU.</div>
                            @endif
                            <button class="btn btn-primary" type="submit">Buat Order</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">Ringkasan Belanja</h5>
                        @foreach ($items as $item)
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ $item['name'] }} x{{ $item['qty'] }}</span>
                                <span>{{ $currency }} {{ number_format((float) $item['line_total'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                        <hr>
                        <div class="d-flex justify-content-between"><span>Subtotal</span><strong>{{ $currency }} {{ number_format($subtotal, 0, ',', '.') }}</strong></div>
                        <div class="d-flex justify-content-between"><span>Tax ({{ $taxPercent }}%)</span><strong>{{ $currency }} {{ number_format($taxAmount, 0, ',', '.') }}</strong></div>
                        <hr>
                        <div class="d-flex justify-content-between"><span>Total</span><strong>{{ $currency }} {{ number_format($total, 0, ',', '.') }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
