@extends('layouts.auth')
@section('title', 'Create Account')

@section('content')
<div>
    <div style="margin-bottom:2rem;">
        <h1 style="font-size:2rem;font-weight:900;color:#F1F5F9;margin-bottom:0.5rem;">Create account</h1>
        <p style="color:#64748B;">Join TechNova for AI-powered shopping</p>
    </div>

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:12px;padding:1rem;margin-bottom:1.25rem;">
        @foreach($errors->all() as $err)
        <p style="color:#F87171;font-size:0.875rem;">• {{ $err }}</p>
        @endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('register.post') }}">
        @csrf
        <div style="display:flex;flex-direction:column;gap:1rem;margin-bottom:1.5rem;">
            <div>
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-input" placeholder="John Doe" value="{{ old('name') }}" required autofocus>
            </div>
            <div>
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="you@example.com" value="{{ old('email') }}" required>
            </div>
            <div>
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="Min. 8 characters" required>
            </div>
            <div>
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-input" placeholder="Repeat password" required>
            </div>
        </div>

        <p style="font-size:0.75rem;color:#64748B;margin-bottom:1.25rem;">
            By creating an account you agree to our <a href="#" style="color:#3B82F6;">Terms of Service</a> and <a href="#" style="color:#3B82F6;">Privacy Policy</a>.
        </p>

        <button type="submit" class="btn-primary" style="width:100%;justify-content:center;padding:0.875rem;font-size:1rem;">Create Account</button>
    </form>

    <div style="text-align:center;margin-top:1.5rem;">
        <p style="font-size:0.875rem;color:#64748B;">
            Already have an account?
            <a href="{{ route('login') }}" style="color:#3B82F6;text-decoration:none;font-weight:600;">Sign in</a>
        </p>
    </div>
</div>
@endsection
