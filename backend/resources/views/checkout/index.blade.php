@extends('layouts.app')
@section('title', 'Checkout')

@section('content')
<div style="max-width:1100px;margin:0 auto;padding:2rem 1rem;">

    {{-- Steps --}}
    <div style="display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:2.5rem;">
        @foreach([['1','Cart','✓'],['2','Shipping','current'],['3','Payment','']] as [$num,$label,$state])
        <div style="display:flex;align-items:center;">
            <div style="display:flex;flex-direction:column;align-items:center;gap:0.375rem;">
                <div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;
                    {{ $state === '✓' ? 'background:#10B981;color:white;' : ($state === 'current' ? 'background:linear-gradient(135deg,#3B82F6,#8B5CF6);color:white;box-shadow:0 0 16px rgba(59,130,246,0.5);' : 'background:var(--bg-elevated);border:2px solid var(--border);color:#64748B;') }}">
                    {{ $state === '✓' ? '✓' : $num }}
                </div>
                <span style="font-size:0.75rem;font-weight:600;{{ $state === 'current' ? 'color:#3B82F6;' : 'color:#64748B;' }}">{{ $label }}</span>
            </div>
            @if(!$loop->last)
            <div style="width:80px;height:2px;background:{{ $state === '✓' ? '#10B981' : 'var(--border)' }};margin:0 0.5rem;margin-bottom:1.25rem;"></div>
            @endif
        </div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:1.5rem;align-items:start;">

        {{-- Form --}}
        <form method="POST" action="{{ route('checkout.process') }}">
            @csrf

            {{-- Shipping Address --}}
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:1.75rem;margin-bottom:1.25rem;">
                <h3 style="font-size:1rem;font-weight:700;color:#F1F5F9;margin-bottom:1.25rem;">📦 Shipping Address</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div><label class="form-label">First Name</label><input name="first_name" class="form-input" required value="{{ auth()->user()->name ? explode(' ', auth()->user()->name)[0] : '' }}"></div>
                    <div><label class="form-label">Last Name</label><input name="last_name" class="form-input" required value="{{ auth()->user()->name && str_contains(auth()->user()->name,' ') ? explode(' ', auth()->user()->name, 2)[1] : '' }}"></div>
                    <div><label class="form-label">Email</label><input name="email" type="email" class="form-input" required value="{{ auth()->user()->email }}"></div>
                    <div><label class="form-label">Phone</label><input name="phone" class="form-input" required value="{{ auth()->user()->phone ?? '' }}"></div>
                    <div style="grid-column:1/-1"><label class="form-label">Street Address</label><input name="address" class="form-input" required value="{{ auth()->user()->address ?? '' }}" placeholder="123 Main St, Apt 4B"></div>
                    <div><label class="form-label">City</label><input name="city" class="form-input" required value="{{ auth()->user()->city ?? '' }}"></div>
                    <div><label class="form-label">State / Province</label><input name="state" class="form-input" required value="{{ auth()->user()->state ?? '' }}"></div>
                    <div><label class="form-label">ZIP / Postal Code</label><input name="zip" class="form-input" required value="{{ auth()->user()->zip ?? '' }}"></div>
                    <div><label class="form-label">Country</label>
                        <select name="country" class="form-input">
                            @foreach(['United States','United Kingdom','Canada','Australia','Germany','France','Japan','Bangladesh','India','Singapore'] as $c)
                            <option value="{{ $c }}" {{ (auth()->user()->country ?? 'United States') === $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Payment --}}
            <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:1.75rem;margin-bottom:1.25rem;" x-data="{ payment: 'cod' }">
                <h3 style="font-size:1rem;font-weight:700;color:#F1F5F9;margin-bottom:1.25rem;">💳 Payment Method</h3>
                <div style="display:flex;flex-direction:column;gap:0.75rem;">
                    @foreach([['cod','💰','Cash on Delivery','Pay when your order arrives'],['card','💳','Credit / Debit Card','Visa, Mastercard, Amex — coming soon']] as [$val,$icon,$title,$sub])
                    <label style="display:flex;align-items:center;gap:1rem;padding:1rem;border-radius:12px;cursor:pointer;border:2px solid;"
                        :style="payment === '{{ $val }}' ? 'border-color:#3B82F6;background:rgba(59,130,246,0.08);' : 'border-color:var(--border);background:var(--bg-elevated);'">
                        <input type="radio" name="payment_method" value="{{ $val }}" x-model="payment" style="accent-color:#3B82F6;">
                        <span style="font-size:1.5rem;">{{ $icon }}</span>
                        <div>
                            <div style="font-size:0.9rem;font-weight:700;color:#F1F5F9;">{{ $title }}</div>
                            <div style="font-size:0.75rem;color:#64748B;">{{ $sub }}</div>
                        </div>
                        <div style="margin-left:auto;" x-show="payment === '{{ $val }}'">
                            <div style="width:20px;height:20px;border-radius:50%;background:linear-gradient(135deg,#3B82F6,#8B5CF6);display:flex;align-items:center;justify-content:center;color:white;font-size:0.75rem;">✓</div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:1rem;font-size:1rem;">
                🔒 Place Order
            </button>
        </form>

        {{-- Order Summary --}}
        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:1.5rem;position:sticky;top:80px;">
            <h3 style="font-size:1rem;font-weight:700;color:#F1F5F9;margin-bottom:1.25rem;">Your Order</h3>
            @foreach($cartItems as $item)
            <div style="display:flex;gap:0.875rem;padding:0.75rem 0;border-bottom:1px solid var(--border);">
                <div style="position:relative;">
                    <img src="{{ $item->attributes->image ?? '' }}" style="width:50px;height:50px;object-fit:cover;border-radius:8px;border:1px solid var(--border);">
                    <span style="position:absolute;top:-6px;right:-6px;background:#3B82F6;color:white;font-size:0.6rem;font-weight:700;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;">{{ $item->quantity }}</span>
                </div>
                <div style="flex:1;">
                    <div style="font-size:0.8rem;font-weight:600;color:#F1F5F9;line-height:1.3;">{{ Str::limit($item->name, 35) }}</div>
                </div>
                <span style="font-size:0.875rem;font-weight:700;color:#F1F5F9;">{{ format_currency($item->price * $item->quantity) }}</span>
            </div>
            @endforeach

            <div style="padding-top:1rem;display:flex;flex-direction:column;gap:0.5rem;">
                <div style="display:flex;justify-content:space-between;font-size:0.8rem;"><span style="color:#64748B;">Subtotal</span><span style="color:#F1F5F9;">{{ format_currency($subtotal) }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:0.8rem;"><span style="color:#64748B;">Shipping</span><span style="color:{{ $shipping == 0 ? '#10B981' : '#F1F5F9' }};">{{ $shipping == 0 ? 'FREE' : format_currency($shipping) }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:0.8rem;"><span style="color:#64748B;">Tax</span><span style="color:#F1F5F9;">{{ format_currency($tax) }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:1rem;font-weight:800;padding-top:0.75rem;border-top:1px solid var(--border);">
                    <span style="color:#F1F5F9;">Total</span>
                    <span style="background:linear-gradient(135deg,#3B82F6,#8B5CF6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">{{ format_currency($total) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
