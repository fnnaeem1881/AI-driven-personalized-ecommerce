@extends('layouts.app')
@section('title', 'Returns Policy')
@section('content')
<div style="max-width:800px;margin:0 auto;padding:2rem 1rem;">
    <div style="text-align:center;margin-bottom:3rem;">
        <h1 style="font-size:2.5rem;font-weight:900;color:#F1F5F9;margin-bottom:0.75rem;">🔄 Returns & Refunds</h1>
        <p style="color:#64748B;">30-day hassle-free returns on most items</p>
    </div>
    <div class="glass-card" style="padding:1.75rem;margin-bottom:1.5rem;">
        <h2 style="font-size:1.1rem;font-weight:700;color:#F1F5F9;margin-bottom:1.25rem;">How to Return an Item</h2>
        <div style="display:flex;flex-direction:column;gap:1rem;">
            @foreach([
                ['1','Initiate Return','Log in to your account → My Orders → Select order → Click "Return Item"'],
                ['2','Pack Your Item','Place the item in its original packaging with all accessories and documentation'],
                ['3','Drop Off','Use our prepaid label to drop off at any courier partner location'],
                ['4','Get Refund','Refund processed within 5-7 business days to your original payment method'],
            ] as $step)
            <div style="display:flex;gap:1rem;align-items:flex-start;">
                <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#3B82F6,#8B5CF6);display:flex;align-items:center;justify-content:center;font-weight:800;color:white;font-size:0.875rem;flex-shrink:0;">{{ $step[0] }}</div>
                <div>
                    <div style="font-weight:700;color:#F1F5F9;margin-bottom:0.25rem;">{{ $step[1] }}</div>
                    <div style="font-size:0.875rem;color:#94A3B8;">{{ $step[2] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.5rem;">
        <div class="glass-card" style="padding:1.25rem;">
            <h3 style="font-size:0.9rem;font-weight:700;color:#10B981;margin-bottom:0.875rem;">✓ Eligible for Return</h3>
            <ul style="list-style:none;padding:0;">
                @foreach(['Items in original condition','Unused and undamaged products','Items with original tags','Within 30 days of delivery','Defective or damaged items'] as $item)
                <li style="font-size:0.8rem;color:#94A3B8;padding:0.35rem 0;border-bottom:1px solid rgba(59,130,246,0.06);">✓ {{ $item }}</li>
                @endforeach
            </ul>
        </div>
        <div class="glass-card" style="padding:1.25rem;">
            <h3 style="font-size:0.9rem;font-weight:700;color:#EF4444;margin-bottom:0.875rem;">✗ Not Eligible</h3>
            <ul style="list-style:none;padding:0;">
                @foreach(['Items used or installed','Digital/software products','Custom/personalized items','Items after 30 days','Hygiene products once opened'] as $item)
                <li style="font-size:0.8rem;color:#94A3B8;padding:0.35rem 0;border-bottom:1px solid rgba(59,130,246,0.06);">✗ {{ $item }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="glass-card" style="padding:1.5rem;background:rgba(16,185,129,0.06);border-color:rgba(16,185,129,0.2);">
        <h3 style="color:#10B981;font-weight:700;margin-bottom:0.5rem;">💸 Refund Timeline</h3>
        <p style="color:#94A3B8;font-size:0.875rem;line-height:1.7;">Once we receive your return, refunds are processed within <strong style="color:#F1F5F9;">5-7 business days</strong>. The amount will be credited to your original payment method. For bKash/Nagad refunds, allow up to 3 business days after processing.</p>
    </div>
</div>
@endsection
