@extends('layouts.admin')
@section('title','Edit Flash Deal')
@section('page-title','Edit: ' . $flashDeal->title)
@section('breadcrumb','Admin / Flash Deals / Edit')
@section('header-actions')
<a href="{{ route('admin.flash-deals.index') }}" class="btn-secondary">&larr; Back</a>
@endsection
@section('content')
<form method="POST" action="{{ route('admin.flash-deals.update', $flashDeal) }}" class="max-w-3xl">
@csrf @method('PUT')
<div class="space-y-5">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Deal Information</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="form-group sm:col-span-2">
                <label class="form-label">Deal Title *</label>
                <input type="text" name="title" value="{{ old('title', $flashDeal->title) }}" required class="form-input">
            </div>
            <div class="form-group sm:col-span-2">
                <label class="form-label">Description</label>
                <textarea name="description" rows="2" class="form-input">{{ old('description', $flashDeal->description) }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Discount % *</label>
                <input type="number" name="discount_percent" value="{{ old('discount_percent', $flashDeal->discount_percent) }}" required min="1" max="99" class="form-input">
                <p class="text-xs text-gray-400 mt-1">Applied to all assigned products</p>
            </div>
            <div class="form-group flex items-center gap-3 pt-6">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ $flashDeal->is_active ? 'checked' : '' }} id="is_active" class="w-4 h-4 accent-blue-600">
                <label for="is_active" class="text-sm text-gray-700 font-medium cursor-pointer">Active</label>
            </div>
            <div class="form-group">
                <label class="form-label">Start Date &amp; Time *</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $flashDeal->starts_at->format('Y-m-d\TH:i')) }}" required class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">End Date &amp; Time *</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $flashDeal->ends_at->format('Y-m-d\TH:i')) }}" required class="form-input">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Assign Products</h3>
        <div class="form-group">
            <label class="form-label">Select Products</label>
            <select name="product_ids[]" multiple class="form-input" id="product_select" data-placeholder="Search and select products...">
                @foreach($products as $p)
                <option value="{{ $p->id }}" {{ in_array($p->id, $selectedIds) ? 'selected' : '' }}>
                    {{ $p->name }} &mdash; {{ format_currency($p->price) }} ({{ $p->brand }})
                </option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Search and select multiple products.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="flash-error">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <button type="submit" class="btn-primary py-3 px-8">Update Flash Deal</button>
</div>
</form>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#product_select').select2({
        width: '100%',
        placeholder: 'Search and select products...',
        allowClear: true,
    });
});
</script>
@endpush
