<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::getAll();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'theme'      => 'required|in:dark,light',
            'store_name' => 'required|string|max:100',
        ]);

        Setting::set('theme', $request->theme);
        Setting::set('store_name', $request->store_name);

        return redirect()->route('admin.settings')
            ->with('success', 'Settings saved. Theme is now ' . ucfirst($request->theme) . '.');
    }
}
