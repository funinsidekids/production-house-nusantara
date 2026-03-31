@extends('layouts/contentNavbarLayout')

@section('title', 'Store - Orders')

@section('content')
<div class="row g-6">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-1">STORE · Orders</h4>
                <p class="mb-0">Kelola order dari checkout Store: update status, monitoring customer, dan catatan admin.</p>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success mb-4">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
                @endif

                <form method="GET" action="{{ route('dashboard-store-order') }}" class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Filter Status</label>
                        <select class="form-select" name="status" onchange="this.form.submit()">
                            <option value="all" @selected($status === 'all')>All</option>
                            <option value="awaiting_payment" @selected($status === 'awaiting_payment')>Awaiting Payment</option>
                            <option value="pending" @selected($status === 'pending')>Pending</option>
                            <option value="paid" @selected($status === 'paid')>Paid</option>
                            <option value="fulfilled" @selected($status === 'fulfilled')>Fulfilled</option>
                            <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                            <option value="failed" @selected($status === 'failed')>Failed</option>
                            <option value="refunded" @selected($status === 'refunded')>Refunded</option>
                        </select>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Items</th>
                                <th>Updated</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $order['order_code'] ?? '-' }}</div>
                                        <small class="text-muted">{{ $order['created_at'] ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <div>{{ $order['customer']['name'] ?? '-' }}</div>
                                        <small class="text-muted">{{ $order['customer']['email'] ?? '-' }}</small>
                                    </td>
                                    <td>{{ $order['currency'] ?? 'IDR' }} {{ number_format((float) ($order['total'] ?? 0), 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ strtoupper($order['status'] ?? 'pending') }}</span>
                                    </td>
                                    <td>{{ count($order['items'] ?? []) }}</td>
                                    <td>{{ $order['updated_at'] ?? '-' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('dashboard-store-order.update-status', ['orderCode' => $order['order_code'] ?? '']) }}" class="d-grid gap-2">
                                            @csrf
                                            <select class="form-select form-select-sm" name="status">
                                                <option value="awaiting_payment">Awaiting Payment</option>
                                                <option value="pending">Pending</option>
                                                <option value="paid">Paid</option>
                                                <option value="fulfilled">Fulfilled</option>
                                                <option value="cancelled">Cancelled</option>
                                                <option value="failed">Failed</option>
                                                <option value="refunded">Refunded</option>
                                            </select>
                                            <input class="form-control form-control-sm" name="admin_note" placeholder="Admin note" value="{{ $order['admin_note'] ?? '' }}">
                                            <button class="btn btn-sm btn-primary" type="submit">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
