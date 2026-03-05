@extends('layouts.admin')
@section('title','Edit Slide')
@section('page-title','Edit Hero Slide')
@section('breadcrumb','Admin / Slides / Edit')
@section('header-actions')
    <a href="{{ route('admin.slides.index') }}" class="btn-secondary">← Back to Slides</a>
@endsection
@section('content')
<div class="max-w-2xl">
    <form method="POST" action="{{ route('admin.slides.update', $slide) }}" class="space-y-5">
        @csrf @method('PUT')
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">🖼 Slide Content</h3>
            @if($slide->image)
            <div class="mb-4">
                <img src="{{ $slide->image }}" alt="" class="w-full h-40 object-cover rounded-lg border border-gray-200">
            </div>
            @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Image URL</label>
                    <input type="url" name="image" value="{{ old('image',$slide->image) }}" placeholder="https://images.unsplash.com/..." class="form-input">
                    <p class="text-xs text-gray-400 mt-1">Recommended: 1200×600px landscape image</p>
                </div>
                <div>
                    <label class="form-label">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title',$slide->title) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Subtitle</label>
                    <input type="text" name="subtitle" value="{{ old('subtitle',$slide->subtitle) }}" class="form-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="2" class="form-input">{{ old('description',$slide->description) }}</textarea>
                </div>
                <div>
                    <label class="form-label">Badge Text</label>
                    <input type="text" name="badge" value="{{ old('badge',$slide->badge) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Badge Color</label>
                    <select name="badge_color" class="form-input">
                        @foreach(['badge-blue'=>'Blue','badge-purple'=>'Purple','badge-cyan'=>'Cyan','badge-red'=>'Red','badge-yellow'=>'Yellow','badge-green'=>'Green'] as $val=>$label)
                            <option value="{{ $val }}" {{ old('badge_color',$slide->badge_color) === $val ? 'selected':'' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">🔗 Call to Action</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Primary CTA Text</label>
                    <input type="text" name="cta_text" value="{{ old('cta_text',$slide->cta_text) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Primary CTA Link</label>
                    <input type="text" name="cta_link" value="{{ old('cta_link',$slide->cta_link) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Secondary CTA Text</label>
                    <input type="text" name="cta_secondary_text" value="{{ old('cta_secondary_text',$slide->cta_secondary_text) }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Secondary CTA Link</label>
                    <input type="text" name="cta_secondary_link" value="{{ old('cta_secondary_link',$slide->cta_secondary_link) }}" class="form-input">
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">⚙ Settings</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order',$slide->sort_order) }}" min="0" class="form-input">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active',$slide->is_active) ? 'checked':'' }} class="w-5 h-5 rounded accent-blue-600">
                        <span class="text-sm font-medium text-gray-700">Active (visible on homepage)</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn-primary py-3 px-8">💾 Save Changes</button>
            <a href="{{ route('admin.slides.index') }}" class="btn-secondary">Cancel</a>
            <form method="POST" action="{{ route('admin.slides.destroy', $slide) }}" class="ml-auto" onsubmit="return confirm('Delete this slide?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-secondary text-red-600 border-red-200 hover:bg-red-50">🗑 Delete</button>
            </form>
        </div>
    </form>
</div>
@endsection
