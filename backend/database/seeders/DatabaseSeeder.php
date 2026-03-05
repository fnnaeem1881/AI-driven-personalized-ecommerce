<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'demo@technova.com'],
            [
                'name'     => 'Demo Admin',
                'password' => bcrypt('password'),
                'role'     => 'admin',
            ]
        );

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        // Default settings — only inserted if key doesn't already exist
        $defaults = [
            'theme'                   => 'dark',
            'store_name'              => 'TechNova Store',
            'currency'                => 'BDT',
            'currency_symbol'         => '৳',
            'currency_position'       => 'before',
            'contact_email'           => 'info@technova.com',
            'contact_phone'           => '+880 1700-000000',
            'contact_address'         => 'Dhaka, Bangladesh',
            'social_facebook'         => '',
            'social_instagram'        => '',
            'social_twitter'          => '',
            'social_youtube'          => '',
            'meta_title'              => 'TechNova — Electronics & Gadgets',
            'meta_description'        => 'Shop the latest smartphones, laptops, gaming gear and more at TechNova.',
            'free_shipping_threshold' => '2000',
            'shipping_cost'           => '60',
            'min_order_amount'        => '100',
            'store_status'            => 'open',
            'maintenance_message'     => 'We are under maintenance. Please check back soon!',
            'hero_badge'              => '🚀 New Season Sale',
            'hero_title'              => 'Next-Gen Tech',
            'hero_subtitle'           => 'at Unbeatable Prices',
            'hero_cta'                => 'Shop Now',
        ];
        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
