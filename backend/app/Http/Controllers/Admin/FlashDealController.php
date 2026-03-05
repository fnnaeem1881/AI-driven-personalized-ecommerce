<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashDeal;
use App\Models\Product;
use Illuminate\Http\Request;

class FlashDealController extends Controller
{
    public function index()
    {
        $deals = FlashDeal::withCount('products')->latest()->paginate(20);
        return view('admin.flash-deals.index', compact('deals'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('admin.flash-deals.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string|max:500',
            'discount_percent' => 'required|integer|min:1|max:99',
            'starts_at'        => 'required|date',
            'ends_at'          => 'required|date|after:starts_at',
            'is_active'        => 'boolean',
            'product_ids'      => 'nullable|array',
            'product_ids.*'    => 'exists:products,id',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $deal = FlashDeal::create($data);

        if (!empty($data['product_ids'])) {
            $deal->products()->sync($data['product_ids']);
        }

        return redirect()->route('admin.flash-deals.index')
                         ->with('success', 'Flash deal created.');
    }

    public function edit(FlashDeal $flashDeal)
    {
        $products    = Product::where('is_active', true)->orderBy('name')->get();
        $selectedIds = $flashDeal->products->pluck('id')->toArray();
        return view('admin.flash-deals.edit', compact('flashDeal', 'products', 'selectedIds'));
    }

    public function update(Request $request, FlashDeal $flashDeal)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string|max:500',
            'discount_percent' => 'required|integer|min:1|max:99',
            'starts_at'        => 'required|date',
            'ends_at'          => 'required|date|after:starts_at',
            'is_active'        => 'boolean',
            'product_ids'      => 'nullable|array',
            'product_ids.*'    => 'exists:products,id',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        $flashDeal->update($data);
        $flashDeal->products()->sync($data['product_ids'] ?? []);

        return redirect()->route('admin.flash-deals.index')
                         ->with('success', 'Flash deal updated.');
    }

    public function destroy(FlashDeal $flashDeal)
    {
        $flashDeal->delete();
        return redirect()->route('admin.flash-deals.index')
                         ->with('success', 'Flash deal deleted.');
    }
}
