<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;

class HeroSlideController extends Controller
{
    public function index() {
        $slides = HeroSlide::orderBy('sort_order')->get();
        return view('admin.slides.index', compact('slides'));
    }

    public function create() {
        return view('admin.slides.create');
    }

    public function store(Request $request) {
        $data = $request->validate([
            'badge'               => 'nullable|string|max:100',
            'badge_color'         => 'nullable|string|max:50',
            'title'               => 'required|string|max:200',
            'subtitle'            => 'nullable|string|max:200',
            'description'         => 'nullable|string|max:500',
            'image'               => 'nullable|string|max:500',
            'cta_text'            => 'nullable|string|max:100',
            'cta_link'            => 'nullable|string|max:200',
            'cta_secondary_text'  => 'nullable|string|max:100',
            'cta_secondary_link'  => 'nullable|string|max:200',
            'sort_order'          => 'nullable|integer|min:0',
            'is_active'           => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        HeroSlide::create($data);
        return redirect()->route('admin.slides.index')->with('success', 'Slide created.');
    }

    public function edit(HeroSlide $slide) {
        return view('admin.slides.edit', compact('slide'));
    }

    public function update(Request $request, HeroSlide $slide) {
        $data = $request->validate([
            'badge'               => 'nullable|string|max:100',
            'badge_color'         => 'nullable|string|max:50',
            'title'               => 'required|string|max:200',
            'subtitle'            => 'nullable|string|max:200',
            'description'         => 'nullable|string|max:500',
            'image'               => 'nullable|string|max:500',
            'cta_text'            => 'nullable|string|max:100',
            'cta_link'            => 'nullable|string|max:200',
            'cta_secondary_text'  => 'nullable|string|max:100',
            'cta_secondary_link'  => 'nullable|string|max:200',
            'sort_order'          => 'nullable|integer|min:0',
            'is_active'           => 'boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $slide->update($data);
        return redirect()->route('admin.slides.index')->with('success', 'Slide updated.');
    }

    public function destroy(HeroSlide $slide) {
        $slide->delete();
        return back()->with('success', 'Slide deleted.');
    }
}
