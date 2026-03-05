@extends('layouts.admin')

@section('title', 'Order #' . $order->order_number)
@section('page-title', 'Order #' . $order->order_number)
@section('breadcrumb', 'Admin / Orders / #' . $order->order_number)

@section('header-actions')
    <a href="{{ route('admin.orders.index') }}" class="btn-secondary">← Back to Orders</a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- Items --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Order Items</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <th class="px-5 py-3 text-left font-medium">Product</th>
                        <th class="px-5 py-3 text-right font-medium">Price</th>
                        <th class="px-5 py-3 text-center font-medium">Qty</th>
                        <th class="px-5 py-3 text-right font-medium">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                @if($item->product_image)
                                    <img src="{{ $item->product_image }}" alt="{{ $item->product_name }}"
                                         class="w-10 h-10 rounded object-cover bg-gray-100"
                                         onerror="this.src='https://placehold.co/40x40/e2e8f0/94a3b8?text=IMG'">
                                @endif
                                <span class="font-medium text-gray-800">{{ $item->product_name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-right text-gray-600">{{ format_currency($item->price) }}</td>
                        <td class="px-5 py-3 text-center text-gray-600">{{ $item->quantity }}</td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-800">{{ format_currency($item->total) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-gray-200">
                    <tr class="bg-gray-50">
                        <td colspan="3" class="px-5 py-3 text-right text-gray-600">Subtotal</td>
                        <td class="px-5 py-3 text-right font-semibold">{{ format_currency($order->subtotal) }}</td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td colspan="3" class="px-5 py-3 text-right text-gray-600">Shipping</td>
                        <td class="px-5 py-3 text-right font-semibold">{{ format_currency($order->shipping) }}</td>
                    </tr>
                    <tr class="bg-gray-50">
                        <td colspan="3" class="px-5 py-3 text-right text-gray-600">Tax</td>
                        <td class="px-5 py-3 text-right font-semibold">{{ format_currency($order->tax) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="px-5 py-3 text-right font-bold text-gray-900">Total</td>
                        <td class="px-5 py-3 text-right font-bold text-blue-600 text-lg">{{ format_currency($order->total) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Shipping address --}}
        @if($order->shipping_address)
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Shipping Address</h3>
            @php $addr = is_array($order->shipping_address) ? $order->shipping_address : json_decode($order->shipping_address, true); @endphp
            <div class="text-sm text-gray-600 space-y-1">
                <div class="font-medium text-gray-800">{{ $addr['name'] ?? '' }}</div>
                <div>{{ $addr['address'] ?? '' }}</div>
                <div>{{ $addr['city'] ?? '' }}{{ isset($addr['state']) ? ', '.$addr['state'] : '' }} {{ $addr['zip'] ?? '' }}</div>
                <div>{{ $addr['country'] ?? '' }}</div>
                @if(!empty($addr['phone'])) <div>📞 {{ $addr['phone'] }}</div> @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Right sidebar --}}
    <div class="space-y-5">
        {{-- Update Status --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Update Status</h3>
            @php
                $cls = match($order->status) {
                    'pending'    => 'badge-yellow',
                    'processing' => 'badge-blue',
                    'shipped'    => 'badge-purple',
                    'delivered'  => 'badge-green',
                    'cancelled'  => 'badge-red',
                    default      => 'badge-gray',
                };
            @endphp
            <div class="mb-3">
                <span class="text-xs text-gray-500">Current:</span>
                <span class="badge {{ $cls }} ml-2">{{ ucfirst($order->status) }}</span>
            </div>
            <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                @csrf @method('PATCH')
                <select name="status" class="form-input mb-3">
                    @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)
                        <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary w-full justify-center">Update Status</button>
            </form>
        </div>

        {{-- Order info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Order Info</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Order #</dt>
                    <dd class="font-mono font-medium text-gray-800">{{ $order->order_number }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Date</dt>
                    <dd class="text-gray-700">{{ $order->created_at->format('d M Y') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Payment</dt>
                    <dd>
                        @php
                            $pCls = match($order->payment_status) {
                                'paid'     => 'badge-green',
                                'failed'   => 'badge-red',
                                'refunded' => 'badge-purple',
                                default    => 'badge-yellow',
                            };
                        @endphp
                        <span class="badge {{ $pCls }}">{{ ucfirst($order->payment_status) }}</span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Method</dt>
                    <dd class="text-gray-700 capitalize">{{ str_replace('_', ' ', $order->payment_method ?? '—') }}</dd>
                </div>
                @if($order->tracking_number)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Tracking</dt>
                    <dd class="font-mono text-xs text-gray-700">{{ $order->tracking_number }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Payment & Tracking Edit --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-4">Payment & Tracking</h3>
            <form method="POST" action="{{ route('admin.orders.payment', $order) }}" class="space-y-3">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Payment Status</label>
                    <select name="payment_status" class="form-input">
                        @foreach(['pending','paid','failed','refunded'] as $ps)
                            <option value="{{ $ps }}" {{ $order->payment_status === $ps ? 'selected' : '' }}>{{ ucfirst($ps) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Payment Method</label>
                    <input type="text" name="payment_method" value="{{ $order->payment_method }}"
                           class="form-input" placeholder="e.g. cash_on_delivery, card">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tracking Number</label>
                    <input type="text" name="tracking_number" value="{{ $order->tracking_number }}"
                           class="form-input" placeholder="e.g. TRK-123456789">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Admin Notes</label>
                    <textarea name="notes" rows="2" class="form-input resize-none" placeholder="Internal notes…">{{ $order->notes }}</textarea>
                </div>
                <button type="submit" class="btn-primary w-full justify-center">Save Payment Info</button>
            </form>
        </div>

        {{-- Customer info --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-800 mb-3">Customer</h3>
            @if($order->user)
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold">
                    {{ strtoupper(substr($order->user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-medium text-gray-800">{{ $order->user->name }}</div>
                    <div class="text-xs text-gray-400">{{ $order->user->email }}</div>
                </div>
            </div>
            <div class="text-xs text-gray-500">Total orders: {{ $order->user->orders()->count() }}</div>
            @else
                <p class="text-sm text-gray-400">Guest order</p>
            @endif
        </div>
    </div>
</div>
@endsection
