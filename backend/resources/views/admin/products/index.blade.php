@extends('layouts.admin')

@section('title', 'Products')
@section('page-title', 'Products')
@section('breadcrumb', 'Manage your product catalog')

@section('header-actions')
    <a href="{{ route('admin.products.create') }}" class="btn-primary">+ Add Product</a>
@endsection

@section('content')
{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-48">
        <label class="form-label">Search</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, brand, SKU…" class="form-input">
    </div>
    <div class="min-w-40">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-input">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-36">
        <label class="form-label">Status</label>
        <select name="status" class="form-input">
            <option value="">All</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
    <button type="submit" class="btn-primary">🔍 Filter</button>
    <a href="{{ route('admin.products.index') }}" class="btn-secondary">✕ Clear</a>
</form>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
        <span class="text-sm text-gray-500">{{ $products->total() }} product(s)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <th class="px-4 py-3 text-left font-medium">Product</th>
                    <th class="px-4 py-3 text-left font-medium">Category</th>
                    <th class="px-4 py-3 text-left font-medium">Brand</th>
                    <th class="px-4 py-3 text-right font-medium">Price</th>
                    <th class="px-4 py-3 text-right font-medium">Stock</th>
                    <th class="px-4 py-3 text-left font-medium">Status</th>
                    <th class="px-4 py-3 text-center font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                <tr class="table-row">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $product->image }}" alt="{{ $product->name }}"
                                 class="w-10 h-10 rounded-lg object-cover bg-gray-100" loading="lazy"
                                 onerror="this.src='https://placehold.co/40x40/e2e8f0/94a3b8?text=IMG'">
                            <div>
                                <div class="font-medium text-gray-800 max-w-48 truncate">{{ $product->name }}</div>
                                <div class="text-xs text-gray-400">{{ $product->sku }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $product->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $product->brand }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-800">${{ number_format($product->price, 2) }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="{{ $product->stock < 10 ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                            {{ $product->stock }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($product->is_active)
                            <span class="badge badge-green">Active</span>
                        @else
                            <span class="badge badge-red">Inactive</span>
                        @endif
                        @if($product->is_featured)
                            <span class="badge badge-purple ml-1">Featured</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.products.edit', $product) }}" class="btn-secondary text-xs py-1 px-3">✏️ Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                  onsubmit="return confirm('Deactivate this product?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger text-xs py-1 px-3">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No products found. <a href="{{ route('admin.products.create') }}" class="text-blue-600 hover:underline">Add one</a>.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $products->links() }}</div>
    @endif
</div>
@endsection
