@extends('layouts.app')
@section('title', 'Help Center')
@section('content')
<div style="max-width:900px;margin:0 auto;padding:2rem 1rem;">
    <div style="text-align:center;margin-bottom:3rem;">
        <h1 style="font-size:2.5rem;font-weight:900;color:#F1F5F9;margin-bottom:0.75rem;">Help Center</h1>
        <p style="color:#64748B;font-size:1rem;">Find answers to common questions about TechNova</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:1.5rem;margin-bottom:3rem;">
        @foreach([
            ['icon'=>'📦','title'=>'Orders & Shipping','desc'=>'Track orders, shipping times, and delivery info','link'=>route('support.shipping')],
            ['icon'=>'🔄','title'=>'Returns & Refunds','desc'=>'Return policy, refund process and timelines','link'=>route('support.returns')],
            ['icon'=>'🚚','title'=>'Track Your Order','desc'=>'Real-time order tracking and status updates','link'=>route('support.track')],
            ['icon'=>'💳','title'=>'Payment Issues','desc'=>'Payment methods, failed payments, billing queries','link'=>'#'],
            ['icon'=>'📱','title'=>'Product Support','desc'=>'Technical help, warranty, and product questions','link'=>'#'],
            ['icon'=>'📧','title'=>'Contact Support','desc'=>'Get in touch with our support team directly','link'=>route('support.contact')],
        ] as $item)
        <a href="{{ $item['link'] }}" class="glass-card" style="padding:1.5rem;text-decoration:none;display:block;transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
            <div style="font-size:2rem;margin-bottom:0.875rem;">{{ $item['icon'] }}</div>
            <h3 style="font-size:1rem;font-weight:700;color:#F1F5F9;margin-bottom:0.5rem;">{{ $item['title'] }}</h3>
            <p style="font-size:0.85rem;color:#64748B;line-height:1.5;">{{ $item['desc'] }}</p>
        </a>
        @endforeach
    </div>

    <div class="glass-card" style="padding:2rem;">
        <h2 style="font-size:1.25rem;font-weight:700;color:#F1F5F9;margin-bottom:1.5rem;">❓ Frequently Asked Questions</h2>
        @foreach([
            ['q'=>'How long does shipping take?','a'=>'Standard shipping takes 3-7 business days. Express shipping (1-2 days) is available for an additional fee. Free shipping on orders over ৳2,000.'],
            ['q'=>'What is your return policy?','a'=>'We offer 30-day hassle-free returns on most items. Products must be in original condition and packaging. See our Returns Policy for full details.'],
            ['q'=>'How do I track my order?','a'=>'Once your order ships, you\'ll receive a tracking number via email. You can also track orders from your account dashboard under "My Orders".'],
            ['q'=>'What payment methods do you accept?','a'=>'We accept all major credit/debit cards, mobile banking (bKash, Nagad, Rocket), and cash on delivery for eligible areas.'],
            ['q'=>'Is my personal information secure?','a'=>'Yes, we use 256-bit SSL encryption for all transactions. Your personal data is protected according to our Privacy Policy.'],
            ['q'=>'How do AI recommendations work?','a'=>'Our AI analyzes your browsing history, purchase patterns, and preferences to suggest products you\'ll love. You can manage your data preferences in account settings.'],
        ] as $faq)
        <div x-data="{open:false}" style="border-bottom:1px solid rgba(59,130,246,0.1);padding:1rem 0;">
            <button @click="open=!open" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:0.95rem;font-weight:600;color:#F1F5F9;">{{ $faq['q'] }}</span>
                <span style="color:#3B82F6;font-size:1.25rem;" x-text="open ? '−' : '+'">+</span>
            </button>
            <div x-show="open" x-transition style="color:#94A3B8;font-size:0.875rem;line-height:1.7;margin-top:0.75rem;padding-top:0.5rem;">{{ $faq['a'] }}</div>
        </div>
        @endforeach
    </div>
</div>
@endsection
