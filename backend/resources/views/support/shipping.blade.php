@extends('layouts.app')
@section('title', 'Shipping Info')
@section('content')
<div style="max-width:800px;margin:0 auto;padding:2rem 1rem;">
    <div style="text-align:center;margin-bottom:3rem;">
        <h1 style="font-size:2.5rem;font-weight:900;color:#F1F5F9;margin-bottom:0.75rem;">🚚 Shipping Information</h1>
        <p style="color:#64748B;">Everything you need to know about delivery</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.25rem;margin-bottom:2rem;">
        @foreach([
            ['icon'=>'⚡','title'=>'Same-Day Dispatch','desc'=>'Orders placed before 3 PM are dispatched the same day (Mon-Sat).','color'=>'#3B82F6'],
            ['icon'=>'🆓','title'=>'Free Shipping','desc'=>'Free standard delivery on all orders over ৳2,000.','color'=>'#10B981'],
            ['icon'=>'📍','title'=>'Nationwide Delivery','desc'=>'We deliver to all 64 districts across Bangladesh.','color'=>'#8B5CF6'],
            ['icon'=>'🔒','title'=>'Safe Packaging','desc'=>'All items are carefully packaged to prevent damage in transit.','color'=>'#F59E0B'],
        ] as $item)
        <div class="glass-card" style="padding:1.25rem;text-align:center;">
            <div style="font-size:2rem;margin-bottom:0.75rem;">{{ $item['icon'] }}</div>
            <h3 style="font-size:0.9rem;font-weight:700;color:#F1F5F9;margin-bottom:0.5rem;">{{ $item['title'] }}</h3>
            <p style="font-size:0.8rem;color:#64748B;line-height:1.5;">{{ $item['desc'] }}</p>
        </div>
        @endforeach
    </div>
    <div class="glass-card" style="padding:1.75rem;margin-bottom:1.5rem;">
        <h2 style="font-size:1.1rem;font-weight:700;color:#F1F5F9;margin-bottom:1.25rem;">📋 Shipping Rates & Timelines</h2>
        <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
            <thead>
                <tr style="border-bottom:1px solid rgba(59,130,246,0.2);">
                    <th style="text-align:left;padding:0.75rem;color:#64748B;font-size:0.75rem;text-transform:uppercase;">Method</th>
                    <th style="text-align:left;padding:0.75rem;color:#64748B;font-size:0.75rem;text-transform:uppercase;">Delivery Time</th>
                    <th style="text-align:right;padding:0.75rem;color:#64748B;font-size:0.75rem;text-transform:uppercase;">Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach([
                    ['Standard Delivery','3-7 Business Days','৳60 (Free over ৳2,000)'],
                    ['Express Delivery','1-2 Business Days','৳150'],
                    ['Dhaka City Same-Day','Same Day (order by 12 PM)','৳100'],
                    ['Chittagong Express','2-3 Business Days','৳80'],
                ] as $row)
                <tr style="border-bottom:1px solid rgba(59,130,246,0.08);">
                    <td style="padding:0.875rem;color:#F1F5F9;font-weight:500;">{{ $row[0] }}</td>
                    <td style="padding:0.875rem;color:#94A3B8;">{{ $row[1] }}</td>
                    <td style="padding:0.875rem;color:#3B82F6;font-weight:600;text-align:right;">{{ $row[2] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="glass-card" style="padding:1.5rem;">
        <h3 style="font-size:1rem;font-weight:700;color:#F1F5F9;margin-bottom:1rem;">📌 Important Notes</h3>
        <ul style="list-style:none;padding:0;space-y:0.75rem;">
            @foreach([
                'Delivery times are estimates and may vary during peak seasons.',
                'Remote areas may take 1-2 extra days.',
                'A tracking number will be emailed once your order ships.',
                'We are not responsible for delays caused by natural disasters or government restrictions.',
                'Signature may be required for high-value orders.',
            ] as $note)
            <li style="display:flex;gap:0.75rem;color:#94A3B8;font-size:0.875rem;padding:0.5rem 0;border-bottom:1px solid rgba(59,130,246,0.06);">
                <span style="color:#3B82F6;flex-shrink:0;">•</span>{{ $note }}
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
