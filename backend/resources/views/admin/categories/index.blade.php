@extends('layouts.admin')

@section('title', 'Categories')
@section('page-title', 'Categories')
@section('breadcrumb', 'Manage product categories')

@section('header-actions')
    <a href="{{ route('admin.categories.create') }}" class="btn-primary">+ Add Category</a>
@endsection

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100">
        <span class="text-sm text-gray-500">{{ $categories->count() }} categor{{ $categories->count() === 1 ? 'y' : 'ies' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <th class="px-5 py-3 text-left font-medium">Category</th>
                    <th class="px-5 py-3 text-left font-medium">Slug</th>
                    <th class="px-5 py-3 text-right font-medium">Products</th>
                    <th class="px-5 py-3 text-center font-medium">Order</th>
                    <th class="px-5 py-3 text-left font-medium">Status</th>
                    <th class="px-5 py-3 text-center font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $category)
                <tr class="table-row">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ $category->icon ?? '📁' }}</span>
                            <div>
                                <div class="font-medium text-gray-800">{{ $category->name }}</div>
                                @if($category->description)
                                    <div class="text-xs text-gray-400 truncate max-w-48">{{ $category->description }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $category->slug }}</td>
                    <td class="px-5 py-3 text-right">
                        <span class="font-semibold text-gray-800">{{ $category->products_count }}</span>
                    </td>
                    <td class="px-5 py-3 text-center text-gray-500">{{ $category->sort_order }}</td>
                    <td class="px-5 py-3">
                        <span class="badge {{ $category->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn-secondary text-xs py-1 px-3">✏️ Edit</a>
                            @if($category->products_count === 0)
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                  onsubmit="return confirm('Delete this category?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger text-xs py-1 px-3">🗑</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-gray-400">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
