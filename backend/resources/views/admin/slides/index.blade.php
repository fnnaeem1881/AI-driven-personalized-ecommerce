@extends('layouts.admin')
@section('title','Hero Slides')
@section('page-title','Hero Slides')
@section('breadcrumb','Manage homepage hero slider')
@section('header-actions')
    <a href="{{ route('admin.slides.create') }}" class="btn-primary">+ Add Slide</a>
@endsection
@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex justify-between items-center">
        <span class="text-sm text-gray-500">{{ $slides->count() }} slide(s)</span>
        <span class="text-xs text-gray-400">Ordered by sort_order (low = first)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-400 uppercase bg-gray-50 border-b border-gray-100">
                    <th class="px-5 py-3 text-left font-medium">Order</th>
                    <th class="px-5 py-3 text-left font-medium">Preview</th>
                    <th class="px-5 py-3 text-left font-medium">Title</th>
                    <th class="px-5 py-3 text-left font-medium">Badge</th>
                    <th class="px-5 py-3 text-left font-medium">CTA</th>
                    <th class="px-5 py-3 text-center font-medium">Active</th>
                    <th class="px-5 py-3 text-center font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($slides as $slide)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3 text-center text-gray-500 font-mono text-sm">{{ $slide->sort_order }}</td>
                    <td class="px-5 py-3">
                        @if($slide->image)
                            <img src="{{ $slide->image }}" alt="" class="w-20 h-12 object-cover rounded-lg border border-gray-200">
                        @else
                            <div class="w-20 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white text-xl">🖼</div>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <div class="font-semibold text-gray-800 text-sm">{{ Str::limit($slide->title, 40) }}</div>
                        @if($slide->subtitle)
                            <div class="text-xs text-gray-400 mt-0.5">{{ Str::limit($slide->subtitle, 40) }}</div>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($slide->badge)
                            <span class="badge {{ $slide->badge_color ?? 'badge-blue' }} text-xs">{{ $slide->badge }}</span>
                        @else
                            <span class="text-gray-300">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-blue-600">
                        <div>{{ $slide->cta_text }}</div>
                        <div class="text-gray-400">{{ $slide->cta_link }}</div>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <form method="POST" action="{{ route('admin.slides.update', $slide) }}" class="inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="title" value="{{ $slide->title }}">
                            <input type="hidden" name="is_active" value="{{ $slide->is_active ? '0' : '1' }}">
                            <button type="submit"
                                    class="w-10 h-6 rounded-full transition-colors {{ $slide->is_active ? 'bg-blue-500' : 'bg-gray-200' }} relative"
                                    title="{{ $slide->is_active ? 'Deactivate' : 'Activate' }}">
                                <span class="absolute top-1 {{ $slide->is_active ? 'right-1' : 'left-1' }} w-4 h-4 bg-white rounded-full shadow transition-all"></span>
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('admin.slides.edit', $slide) }}" class="btn-secondary text-xs py-1 px-3">Edit</a>
                            <form method="POST" action="{{ route('admin.slides.destroy', $slide) }}"
                                  onsubmit="return confirm('Delete this slide?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-gray-300 hover:text-red-500 transition-colors text-xl leading-none">&times;</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                        <div class="text-4xl mb-2">🖼</div>
                        No slides yet. <a href="{{ route('admin.slides.create') }}" class="text-blue-600 hover:underline">Add your first slide →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
