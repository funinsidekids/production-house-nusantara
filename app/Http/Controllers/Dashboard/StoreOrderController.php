<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LandingSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreOrderController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'all');
        $orders = collect($this->ordersPayload())
            ->filter(fn ($item): bool => is_array($item))
            ->when($status !== 'all', fn ($items) => $items->filter(fn (array $order): bool => (string) ($order['status'] ?? 'pending') === $status))
            ->values()
            ->all();

        return view('content.dashboard.store-order', [
            'orders' => $orders,
            'status' => $status,
        ]);
    }

    public function updateStatus(Request $request, string $orderCode): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:awaiting_payment,pending,paid,fulfilled,cancelled,failed,refunded'],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $orders = $this->ordersPayload();
        $updated = false;
        foreach ($orders as $index => $order) {
            if (! is_array($order)) {
                continue;
            }
            if ((string) ($order['order_code'] ?? '') !== $orderCode) {
                continue;
            }
            $orders[$index]['status'] = $data['status'];
            $orders[$index]['admin_note'] = (string) ($data['admin_note'] ?? '');
            $orders[$index]['updated_at'] = now()->toDateTimeString();
            $updated = true;
            break;
        }
        if (! $updated) {
            return redirect()->route('dashboard-store-order')->with('error', 'Order tidak ditemukan.');
        }

        LandingSetting::query()->updateOrCreate(
            ['key' => 'cms_store_orders_payload'],
            ['value' => json_encode($orders, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
        );

        return redirect()->route('dashboard-store-order')->with('success', 'Status order berhasil diperbarui.');
    }

    private function ordersPayload(): array
    {
        $raw = (string) (LandingSetting::query()->where('key', 'cms_store_orders_payload')->value('value') ?? '');
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
