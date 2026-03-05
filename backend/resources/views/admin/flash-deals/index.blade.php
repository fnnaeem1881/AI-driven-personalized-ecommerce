@extends('layouts.admin')
@section('title','Flash Deals')
@section('page-title','Flash Deals')
@section('breadcrumb','Manage time-limited deals')
@section('header-actions')
<a href="{{ route('admin.flash-deals.create') }}" class="btn-primary">+ New Deal</a>
@endsection
@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                <th class="px-5 py-3 text-left font-medium">Title</th>
                <th class="px-5 py-3 text-center font-medium">Discount</th>
                <th class="px-5 py-3 text-left font-medium">Period</th>
                <th class="px-5 py-3 text-center font-medium">Products</th>
                <th class="px-5 py-3 text-center font-medium">Status</th>
                <th class="px-5 py-3 text-center font-medium">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($deals as $deal)
            <tr class="table-row">
                <td class="px-5 py-3">
                    <div class="font-semibold text-gray-800">{{ $deal->title }}</div>
                    @if($deal->description)<div class="text-xs text-gray-400 truncate max-w-xs">{{ $deal->description }}</div>@endif
                </td>
                <td class="px-5 py-3 text-center"><span class="badge badge-red">-{{ $deal->discount_percent }}%</span></td>
                <td class="px-5 py-3 text-xs text-gray-600">
                    <div>From: {{ $deal->starts_at->format('d M Y H:i') }}</div>
                    <div>To: &nbsp;&nbsp;{{ $deal->ends_at->format('d M Y H:i') }}</div>
                </td>
                <td class="px-5 py-3 text-center"><span class="badge badge-blue">{{ $deal->products_count }} products</span></td>
                <td class="px-5 py-3 text-center">
                    @if($deal->isRunning())
                        <span class="badge badge-green">Live</span>
                    @elseif($deal->starts_at > now())
                        <span class="badge badge-yellow">Upcoming</span>
                    @else
                        <span class="badge badge-gray">Ended</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-center">
                    <div class="flex items-center justify-center gap-2">
                        <a href="{{ route('admin.flash-deals.edit', $deal) }}" class="btn-secondary text-xs py-1 px-3">Edit</a>
                        <form method="POST" action="{{ route('admin.flash-deals.destroy', $deal) }}" onsubmit="return confirm('Delete this flash deal?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-danger text-xs py-1 px-3">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">
                <div class="text-4xl mb-2">&#9889;</div>
                No flash deals yet. <a href="{{ route('admin.flash-deals.create') }}" class="text-blue-600 hover:underline">Create one</a>
            </td></tr>
            @endforelse
        </tbody>
    </table>
    @if($deals->hasPages())
    <div class="px-5 py-3 border-t border-gray-100">{{ $deals->links() }}</div>
    @endif
</div>
@endsection
