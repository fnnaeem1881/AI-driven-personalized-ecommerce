<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);
        $path = $request->file('image')->store('uploads/' . date('Y/m'), 'public');
        return response()->json([
            'url'     => Storage::disk('public')->url($path),
            'path'    => $path,
            'success' => true,
        ]);
    }
}
