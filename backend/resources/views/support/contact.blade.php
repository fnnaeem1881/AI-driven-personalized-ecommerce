@extends('layouts.app')
@section('title', 'Contact Us')
@section('content')
<div style="max-width:900px;margin:0 auto;padding:2rem 1rem;">
    <div style="text-align:center;margin-bottom:3rem;">
        <h1 style="font-size:2.5rem;font-weight:900;color:#F1F5F9;margin-bottom:0.75rem;">📧 Contact Us</h1>
        <p style="color:#64748B;">We're here to help — reach out anytime</p>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;">
        <div>
            <div class="glass-card" style="padding:1.75rem;margin-bottom:1.25rem;">
                <h2 style="font-size:1.1rem;font-weight:700;color:#F1F5F9;margin-bottom:1.25rem;">Send Us a Message</h2>
                <form style="display:flex;flex-direction:column;gap:1rem;" onsubmit="this.querySelector('button').textContent='✓ Sent!';event.preventDefault();">
                    <div>
                        <label class="form-label">Your Name</label>
                        <input type="text" class="form-input" placeholder="John Doe" required>
                    </div>
                    <div>
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-input" placeholder="you@example.com" required>
                    </div>
                    <div>
                        <label class="form-label">Subject</label>
                        <select class="form-input">
                            <option>Order Issue</option>
                            <option>Return Request</option>
                            <option>Product Question</option>
                            <option>Payment Problem</option>
                            <option>General Inquiry</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Message</label>
                        <textarea class="form-input" rows="4" placeholder="Describe your issue..." required></textarea>
                    </div>
                    <button type="submit" class="btn-primary justify-center">📤 Send Message</button>
                </form>
            </div>
        </div>
        <div style="display:flex;flex-direction:column;gap:1.25rem;">
            @foreach([
                ['📧','Email Support','info@technova.com','Response within 24 hours'],
                ['📞','Phone Support','+880 1700-000000','Mon-Sat, 9 AM – 6 PM'],
                ['📍','Office Address','Dhaka, Bangladesh','Visit by appointment only'],
                ['💬','Live Chat','Available on website','Mon-Sat, 10 AM – 8 PM'],
            ] as $contact)
            <div class="glass-card" style="padding:1.25rem;display:flex;gap:1rem;align-items:flex-start;">
                <div style="font-size:1.5rem;">{{ $contact[0] }}</div>
                <div>
                    <div style="font-weight:700;color:#F1F5F9;font-size:0.9rem;margin-bottom:0.25rem;">{{ $contact[1] }}</div>
                    <div style="color:#3B82F6;font-size:0.875rem;">{{ $contact[2] }}</div>
                    <div style="color:#64748B;font-size:0.75rem;margin-top:0.25rem;">{{ $contact[3] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
