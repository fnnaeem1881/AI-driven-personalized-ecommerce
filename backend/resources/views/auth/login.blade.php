@extends('layouts.auth')
@section('title', 'Sign In')

@section('content')
<div>
    <div style="margin-bottom:2rem;">
        <h1 style="font-size:2rem;font-weight:900;color:#F1F5F9;margin-bottom:0.5rem;">Welcome back</h1>
        <p style="color:#64748B;">Sign in to your TechNova account</p>
    </div>

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:12px;padding:1rem;margin-bottom:1.25rem;">
        @foreach($errors->all() as $err)
        <p style="color:#F87171;font-size:0.875rem;">• {{ $err }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.25rem;">
            <div>
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="you@example.com" value="{{ old('email') }}" required autofocus>
            </div>
            <div>
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem;color:#64748B;">
                <input type="checkbox" name="remember" style="accent-color:#3B82F6;">
                <span>Remember me</span>
            </label>
            <a href="#" style="font-size:0.875rem;color:#3B82F6;text-decoration:none;">Forgot password?</a>
        </div>

        <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:0.875rem;font-size:1rem;">Sign In</button>
    </form>

    <div style="text-align:center;margin-top:1.5rem;">
        <p style="font-size:0.875rem;color:#64748B;">
            Don't have an account?
            <a href="{{ route('register') }}" style="color:#3B82F6;text-decoration:none;font-weight:600;">Create one free</a>
        </p>
    </div>

    {{-- Demo account hint --}}
    <div style="margin-top:1.5rem;padding:0.875rem;background:rgba(59,130,246,0.06);border:1px solid rgba(59,130,246,0.15);border-radius:10px;">
        <p style="font-size:0.75rem;color:#64748B;text-align:center;">
            Demo: <strong style="color:#3B82F6;">demo@technova.com</strong> / <strong style="color:#3B82F6;">password</strong>
        </p>
    </div>
</div>
@endsection
