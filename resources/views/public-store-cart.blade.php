<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Cart</title>
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
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <h3 class="mb-3">Cart</h3>

        @if (count($items) === 0)
            <div class="alert alert-info">Cart masih kosong.</div>
            <a class="btn btn-primary" href="{{ route('store.index') }}">Belanja Sekarang</a>
        @else
            <form method="POST" action="{{ route('store.cart.update') }}">
                @csrf
                <div class="table-responsive">
                    <table class="table table-bordered align-middle bg-white">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Harga</th>
                                <th width="140">Qty</th>
                                <th>Subtotal</th>
                                <th width="160">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $currency }} {{ number_format((float) $item['price'], 0, ',', '.') }}</td>
                                    <td>
                                        <input class="form-control" type="number" min="0" max="99" name="qty[{{ $item['slug'] }}]" value="{{ $item['qty'] }}">
                                    </td>
                                    <td>{{ $currency }} {{ number_format((float) $item['line_total'], 0, ',', '.') }}</td>
                                    <td>
                                        <small class="text-muted">Isi qty 0 lalu klik Update Cart untuk hapus item.</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-start mt-3">
                    <button class="btn btn-outline-secondary" type="submit">Update Cart</button>
                    <div class="card" style="min-width:300px;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><strong>{{ $currency }} {{ number_format($subtotal, 0, ',', '.') }}</strong></div>
                            <div class="d-flex justify-content-between mb-2"><span>Tax ({{ $taxPercent }}%)</span><strong>{{ $currency }} {{ number_format($taxAmount, 0, ',', '.') }}</strong></div>
                            <hr>
                            <div class="d-flex justify-content-between"><span>Total</span><strong>{{ $currency }} {{ number_format($total, 0, ',', '.') }}</strong></div>
                            <a class="btn btn-primary w-100 mt-3" href="{{ route('store.checkout') }}">Checkout</a>
                        </div>
                    </div>
                </div>
            </form>
        @endif
    </div>
</body>
</html>
