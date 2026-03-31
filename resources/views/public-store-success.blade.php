<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card mx-auto" style="max-width:760px;">
            <div class="card-body p-4">
                <h3 class="text-success mb-2">Order Berhasil Dibuat</h3>
                <p class="mb-3">Order kamu sudah masuk sistem.</p>
                <div class="mb-2"><strong>Order Code:</strong> {{ $order['order_code'] }}</div>
                <div class="mb-2"><strong>Status:</strong> {{ strtoupper($order['status'] ?? 'pending') }}</div>
                <div class="mb-3"><strong>Checkout Mode:</strong> {{ strtoupper($order['checkout_mode'] ?? 'manual') }}</div>
                @if (($order['checkout_mode'] ?? '') === 'doku' && ($order['status'] ?? '') === 'awaiting_payment' && ($order['payment_url'] ?? '') !== '')
                    <a class="btn btn-warning btn-sm mb-3" href="{{ $order['payment_url'] }}">Lanjutkan Pembayaran DOKU</a>
                @endif
                @if (($order['download_email_sent'] ?? false) === true)
                    <div class="alert alert-success py-2">Pembayaran sukses. Link download sudah dikirim ke email pembeli.</div>
                @endif
                <hr>
                <h6>Items</h6>
                @foreach (($order['items'] ?? []) as $item)
                    <div class="d-flex justify-content-between">
                        <span>{{ $item['name'] ?? '-' }} x{{ $item['qty'] ?? 0 }}</span>
                        <span>{{ $order['currency'] ?? 'IDR' }} {{ number_format((float) ($item['line_total'] ?? 0), 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <hr>
                <div class="d-flex justify-content-between"><span>Total</span><strong>{{ $order['currency'] ?? 'IDR' }} {{ number_format((float) ($order['total'] ?? 0), 0, ',', '.') }}</strong></div>
                <div class="d-flex gap-2 mt-4">
                    <a class="btn btn-primary" href="{{ route('store.index') }}">Kembali ke Store</a>
                    <a class="btn btn-outline-secondary" href="{{ route('home') }}">Kembali ke Home</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
