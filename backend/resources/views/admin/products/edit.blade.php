@extends('layouts.admin')

@section('title', 'Edit Product')
@section('page-title', 'Edit: ' . $product->name)
@section('breadcrumb', 'Admin / Products / Edit')

@section('header-actions')
    <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="btn-secondary">👁 View on Store</a>
    <a href="{{ route('admin.products.index') }}" class="btn-secondary">← Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.products.update', $product) }}" class="max-w-4xl">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Basic Information</h3>
                <div class="form-group">
                    <label class="form-label">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Short Description *</label>
                    <input type="text" name="short_description" value="{{ old('short_description', $product->short_description) }}" required class="form-input" maxlength="500">
                </div>
                <div class="form-group">
                    <label class="form-label">Full Description *</label>
                    <textarea name="description" required rows="5" class="form-input">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Image URL *</label>
                    <input type="url" name="image" value="{{ old('image', $product->image) }}" required class="form-input">
                    @if($product->image)
                        <img src="{{ $product->image }}" alt="Preview" class="mt-2 h-24 w-auto rounded-lg object-cover border border-gray-200"
                             onerror="this.style.display='none'">
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Pricing & Inventory</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Price ($) *</label>
                        <input type="number" name="price" value="{{ old('price', $product->price) }}" required step="0.01" min="0" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Compare Price ($)</label>
                        <input type="number" name="compare_price" value="{{ old('compare_price', $product->compare_price) }}" step="0.01" min="0" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" required min="0" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rating (0–5)</label>
                        <input type="number" name="rating" value="{{ old('rating', $product->rating) }}" step="0.1" min="0" max="5" class="form-input">
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Organization</h3>
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category_id" required class="form-input">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->icon }} {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Brand *</label>
                    <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">SKU *</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="form-input">
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Visibility</h3>
                <label class="flex items-center gap-3 cursor-pointer mb-3">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }} class="w-4 h-4 accent-blue-600">
                    <span class="text-sm text-gray-700 font-medium">Active (visible)</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }} class="w-4 h-4 accent-purple-600">
                    <span class="text-sm text-gray-700 font-medium">Featured product</span>
                </label>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">⚡ Flash Deal</h3>
                <label class="flex items-center gap-3 cursor-pointer mb-4">
                    <input type="hidden" name="is_flash_deal" value="0">
                    <input type="checkbox" name="is_flash_deal" value="1" {{ old('is_flash_deal', $product->is_flash_deal) ? 'checked' : '' }} class="w-4 h-4 accent-red-500">
                    <span class="text-sm text-gray-700 font-medium">Enable Flash Deal</span>
                </label>
                <div class="form-group">
                    <label class="form-label">Deal Ends At</label>
                    <input type="datetime-local" name="flash_deal_ends_at"
                           value="{{ old('flash_deal_ends_at', $product->flash_deal_ends_at?->format('Y-m-d\TH:i')) }}"
                           class="form-input">
                </div>
                <div class="form-group mb-0">
                    <label class="form-label">Discount % <span class="text-gray-400 font-normal">(shown on card)</span></label>
                    <input type="number" name="flash_deal_discount"
                           value="{{ old('flash_deal_discount', $product->flash_deal_discount) }}"
                           min="1" max="99" class="form-input" placeholder="e.g. 20">
                </div>
            </div>

            <button type="submit" class="btn-primary w-full justify-center py-3 text-base">
                💾 Save Changes
            </button>
        </div>
    </div>
</form>
@endsection
