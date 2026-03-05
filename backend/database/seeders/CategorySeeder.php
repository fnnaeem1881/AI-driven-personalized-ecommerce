<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Smartphones',  'slug' => 'smartphones',  'icon' => '📱', 'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=400'],
            ['name' => 'Laptops',      'slug' => 'laptops',      'icon' => '💻', 'image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400'],
            ['name' => 'Audio',        'slug' => 'audio',        'icon' => '🎧', 'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400'],
            ['name' => 'Gaming',       'slug' => 'gaming',       'icon' => '🎮', 'image' => 'https://images.unsplash.com/photo-1593305841991-05c297ba4575?w=400'],
            ['name' => 'Cameras',      'slug' => 'cameras',      'icon' => '📷', 'image' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=400'],
            ['name' => 'Smart Home',   'slug' => 'smart-home',   'icon' => '🏠', 'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400'],
            ['name' => 'Wearables',    'slug' => 'wearables',    'icon' => '⌚', 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400'],
            ['name' => 'Accessories',  'slug' => 'accessories',  'icon' => '🔌', 'image' => 'https://images.unsplash.com/photo-1625948515-b3e10b35b68c?w=400'],
        ];

        foreach ($categories as $i => $cat) {
            Category::create(array_merge($cat, ['sort_order' => $i + 1]));
        }
    }
}
