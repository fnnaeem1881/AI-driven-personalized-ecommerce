@extends('layouts.app')
@section('title', 'Profile Settings')

@section('content')
<div style="max-width:800px;margin:0 auto;padding:2rem 1rem;">
    <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:2rem;">
        <a href="{{ route('account.dashboard') }}" style="color:#64748B;text-decoration:none;" onmouseover="this.style.color='#3B82F6'" onmouseout="this.style.color='#64748B'">← Dashboard</a>
        <span style="color:#374151;">›</span>
        <h1 style="font-size:1.5rem;font-weight:800;color:#F1F5F9;">Profile Settings</h1>
    </div>

    @if(session('success'))
    <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;color:#34D399;font-size:0.875rem;">✓ {{ session('success') }}</div>
    @endif

    <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:20px;padding:2rem;">
        {{-- Avatar --}}
        <div style="display:flex;align-items:center;gap:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid var(--border);margin-bottom:1.75rem;">
            <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#3B82F6,#8B5CF6);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:900;color:white;">{{ strtoupper(substr($user->name,0,1)) }}</div>
            <div>
                <div style="font-size:1.1rem;font-weight:700;color:#F1F5F9;">{{ $user->name }}</div>
                <div style="font-size:0.875rem;color:#64748B;">{{ $user->email }}</div>
                <div style="font-size:0.75rem;color:#374151;margin-top:0.375rem;">Member since {{ $user->created_at->format('M Y') }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('account.profile.update') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div><label class="form-label">Full Name</label><input name="name" class="form-input" value="{{ old('name', $user->name) }}" required></div>
                <div><label class="form-label">Phone Number</label><input name="phone" class="form-input" value="{{ old('phone', $user->phone) }}" placeholder="+1 555 000 0000"></div>
                <div style="grid-column:1/-1"><label class="form-label">Street Address</label><input name="address" class="form-input" value="{{ old('address', $user->address) }}" placeholder="123 Main Street, Apt 4B"></div>
                <div><label class="form-label">City</label><input name="city" class="form-input" value="{{ old('city', $user->city) }}"></div>
                <div><label class="form-label">State / Province</label><input name="state" class="form-input" value="{{ old('state', $user->state) }}"></div>
                <div><label class="form-label">ZIP / Postal Code</label><input name="zip" class="form-input" value="{{ old('zip', $user->zip) }}"></div>
                <div><label class="form-label">Country</label>
                    <select name="country" class="form-input">
                        @foreach(['United States','United Kingdom','Canada','Australia','Germany','France','Japan','Bangladesh','India','Singapore'] as $c)
                        <option value="{{ $c }}" {{ ($user->country ?? 'United States') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($errors->any())
            <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:1rem;margin-top:1rem;">
                @foreach($errors->all() as $err)
                <p style="color:#F87171;font-size:0.875rem;">• {{ $err }}</p>
                @endforeach
            </div>
            @endif

            <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                <button type="submit" class="btn-primary">Save Changes</button>
                <a href="{{ route('account.dashboard') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
