@extends('layouts.admin')

@section('title', 'Edit Category')
@section('page-title', 'Edit: ' . $category->name)
@section('breadcrumb', 'Admin / Categories / Edit')

@section('header-actions')
    <a href="{{ route('admin.categories.index') }}" class="btn-secondary">← Back</a>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.categories.update', $category) }}" class="max-w-2xl">
    @csrf @method('PUT')
    <div class="bg-white rounded-xl border border-gray-200 p-6 space-y-5">
        <div class="grid grid-cols-2 gap-4">
            <div class="form-group col-span-2">
                <label class="form-label">Category Name *</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Icon (emoji)</label>
                <input type="text" name="icon" value="{{ old('icon', $category->icon) }}" class="form-input" maxlength="10">
            </div>
            <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0" class="form-input">
            </div>
            <div class="form-group col-span-2">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-input">{{ old('description', $category->description) }}</textarea>
            </div>
            <div class="form-group col-span-2">
                <label class="form-label">Image URL</label>
                <input type="url" name="image" value="{{ old('image', $category->image) }}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Parent Category</label>
                <select name="parent_id" class="form-input">
                    <option value="">None (top-level)</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                            {{ $parent->icon }} {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group flex items-end">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }} class="w-4 h-4 accent-blue-600">
                    <span class="text-sm text-gray-700 font-medium">Active</span>
                </label>
            </div>
        </div>
        <div class="pt-2 border-t border-gray-100">
            <button type="submit" class="btn-primary">💾 Save Changes</button>
        </div>
    </div>
</form>
@endsection
