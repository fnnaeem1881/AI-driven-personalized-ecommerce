@extends('layouts.admin')
@section('title','Create Flash Deal')
@section('page-title','Create Flash Deal')
@section('breadcrumb','Admin / Flash Deals / Create')
@section('header-actions')
<a href="{{ route('admin.flash-deals.index') }}" class="btn-secondary">&larr; Back</a>
@endsection
@section('content')
<form method="POST" action="{{ route('admin.flash-deals.store') }}" class="max-w-3xl">
@csrf
<div class="space-y-5">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Deal Information</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group sm:col-span-2">
                <label class="form-label">Deal Title *</label>
                <input type="text" name="title" value="{{ old('title') }}" required class="form-input" placeholder="e.g. Mega Summer Sale">
            </div>
            <div class="form-group sm:col-span-2">
                <label class="form-label">Description</label>
                <textarea name="description" rows="2" class="form-input" placeholder="Short deal description...">{{ old('description') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Discount % *</label>
                <input type="number" name="discount_percent" value="{{ old('discount_percent', 10) }}" required min="1" max="99" class="form-input" placeholder="e.g. 20">
                <p class="text-xs text-gray-400 mt-1">Applied to all assigned products</p>
            </div>
            <div class="form-group flex items-center gap-3 pt-6">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" checked id="is_active" class="w-4 h-4 accent-blue-600">
                <label for="is_active" class="text-sm text-gray-700 font-medium cursor-pointer">Active</label>
            </div>
            <div class="form-group">
                <label class="form-label">Start Date &amp; Time *</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">End Date &amp; Time *</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" required class="form-input">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Assign Products</h3>
        <div class="form-group">
            <label class="form-label">Select Products</label>
            <select name="product_ids[]" multiple class="form-input" style="width:100%;height:180px;" id="product_select">
                @foreach($products as $p)
                <option value="{{ $p->id }}" {{ collect(old('product_ids'))->contains($p->id) ? 'selected' : '' }}>
                    {{ $p->name }} &mdash; {{ format_currency($p->price) }} ({{ $p->brand }})
                </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple products.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="flash-error">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <button type="submit" class="btn-primary py-3 px-8">Create Flash Deal</button>
</div>
</form>
@endsection
