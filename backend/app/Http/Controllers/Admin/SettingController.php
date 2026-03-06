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
            'theme'                    => 'required|in:dark,light',
            'store_name'               => 'required|string|max:100',
            'currency'                 => 'required|string|max:10',
            'currency_symbol'          => 'required|string|max:5',
            'currency_position'        => 'required|in:before,after',
            'contact_email'            => 'nullable|email|max:100',
            'contact_phone'            => 'nullable|string|max:30',
            'contact_address'          => 'nullable|string|max:255',
            'social_facebook'          => 'nullable|max:200',
            'social_instagram'         => 'nullable|max:200',
            'social_twitter'           => 'nullable|max:200',
            'social_youtube'           => 'nullable|max:200',
            'meta_title'               => 'nullable|string|max:160',
            'meta_description'         => 'nullable|string|max:300',
            'free_shipping_threshold'  => 'nullable|numeric|min:0',
            'shipping_cost'            => 'nullable|numeric|min:0',
            'min_order_amount'         => 'nullable|numeric|min:0',
            'store_status'             => 'required|in:open,maintenance',
            'maintenance_message'      => 'nullable|string|max:300',
            'logo_url'                 => 'nullable|string|max:500',
            'hero_badge'               => 'nullable|string|max:100',
            'hero_title'               => 'nullable|string|max:100',
            'hero_subtitle'            => 'nullable|string|max:100',
            'hero_cta'                 => 'nullable|string|max:50',
        ]);

        $keys = [
            'theme', 'store_name', 'logo_url',
            'currency', 'currency_symbol', 'currency_position',
            'contact_email', 'contact_phone', 'contact_address',
            'social_facebook', 'social_instagram', 'social_twitter', 'social_youtube',
            'meta_title', 'meta_description',
            'free_shipping_threshold', 'shipping_cost', 'min_order_amount',
            'store_status', 'maintenance_message',
            'hero_badge', 'hero_title', 'hero_subtitle', 'hero_cta',
        ];

        foreach ($keys as $key) {
            Setting::set($key, $request->input($key, ''));
        }

        return redirect()->route('admin.settings')
            ->with('success', 'Settings saved successfully.');
    }
}
