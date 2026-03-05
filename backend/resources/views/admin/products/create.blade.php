@extends('layouts.admin')

@section('title', 'Add Product')
@section('page-title', 'Add New Product')
@section('breadcrumb', 'Admin / Products / Create')

@section('header-actions')
    <a href="{{ route('admin.products.index') }}" class="btn-secondary">← Back to Products</a>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.products.store') }}" class="max-w-4xl">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main info --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Basic Information</h3>
                <div class="form-group">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="e.g. Samsung Galaxy S25 Ultra">
                </div>
                <div class="form-group">
                    <label class="form-label">Short Description *</label>
                    <input type="text" name="short_description" value="{{ old('short_description') }}" required class="form-input" placeholder="One-line product summary (max 500 chars)" maxlength="500">
                </div>
                <div class="form-group">
                    <label class="form-label">Full Description *</label>
                    <textarea name="description" required rows="5" class="form-input" placeholder="Detailed product description…">{{ old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Image URL *</label>
                    <input type="url" name="image" value="{{ old('image') }}" required class="form-input" placeholder="https://images.unsplash.com/…">
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Pricing & Inventory</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Price ($) *</label>
                        <input type="number" name="price" value="{{ old('price') }}" required step="0.01" min="0" class="form-input" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Compare Price ($)</label>
                        <input type="number" name="compare_price" value="{{ old('compare_price') }}" step="0.01" min="0" class="form-input" placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" name="stock" value="{{ old('stock', 0) }}" required min="0" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rating (0–5)</label>
                        <input type="number" name="rating" value="{{ old('rating', 0) }}" step="0.1" min="0" max="5" class="form-input">
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Organization</h3>
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category_id" required class="form-input">
                        <option value="">Select category…</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->icon }} {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Brand *</label>
                    <input type="text" name="brand" value="{{ old('brand') }}" required class="form-input" placeholder="Apple, Samsung, Sony…">
                </div>
                <div class="form-group">
                    <label class="form-label">SKU *</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" required class="form-input" placeholder="SKU-0042">
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Visibility</h3>
                <label class="flex items-center gap-3 cursor-pointer mb-3">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="w-4 h-4 accent-blue-600">
                    <span class="text-sm text-gray-700 font-medium">Active (visible on store)</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} class="w-4 h-4 accent-purple-600">
                    <span class="text-sm text-gray-700 font-medium">Featured product</span>
                </label>
            </div>

            <button type="submit" class="btn-primary w-full justify-center py-3 text-base">
                ✓ Create Product
            </button>
        </div>
    </div>
</form>
@endsection
