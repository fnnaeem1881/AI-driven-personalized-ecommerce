<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $cats = Category::pluck('id', 'slug');

        $products = [
            // Smartphones
            ['category_slug' => 'smartphones', 'name' => 'Samsung Galaxy S24 Ultra', 'brand' => 'Samsung', 'price' => 1299.99, 'compare_price' => 1499.99, 'stock' => 50, 'rating' => 4.8, 'reviews_count' => 342, 'is_featured' => true, 'image' => 'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?w=600', 'short_description' => 'The ultimate Galaxy experience with 200MP camera and S Pen.'],
            ['category_slug' => 'smartphones', 'name' => 'iPhone 15 Pro Max', 'brand' => 'Apple', 'price' => 1199.99, 'compare_price' => 1299.99, 'stock' => 35, 'rating' => 4.9, 'reviews_count' => 528, 'is_featured' => true, 'image' => 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?w=600', 'short_description' => 'Titanium design with A17 Pro chip and 48MP main camera.'],
            ['category_slug' => 'smartphones', 'name' => 'Google Pixel 8 Pro', 'brand' => 'Google', 'price' => 999.99, 'compare_price' => 1099.99, 'stock' => 40, 'rating' => 4.7, 'reviews_count' => 215, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600', 'short_description' => 'Pure Android with Google AI and the best camera in its class.'],
            ['category_slug' => 'smartphones', 'name' => 'OnePlus 12', 'brand' => 'OnePlus', 'price' => 799.99, 'compare_price' => 899.99, 'stock' => 60, 'rating' => 4.6, 'reviews_count' => 178, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=600', 'short_description' => 'Snapdragon 8 Gen 3, 100W fast charging, Hasselblad cameras.'],
            ['category_slug' => 'smartphones', 'name' => 'Xiaomi 14 Ultra', 'brand' => 'Xiaomi', 'price' => 899.99, 'compare_price' => 999.99, 'stock' => 30, 'rating' => 4.5, 'reviews_count' => 142, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1598965675045-45c5e72c7d05?w=600', 'short_description' => 'Leica optics quad-camera system with 90W wireless charging.'],

            // Laptops
            ['category_slug' => 'laptops', 'name' => 'MacBook Pro 16" M3 Max', 'brand' => 'Apple', 'price' => 2499.99, 'compare_price' => 2699.99, 'stock' => 20, 'rating' => 4.9, 'reviews_count' => 412, 'is_featured' => true, 'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=600', 'short_description' => 'M3 Max chip, 18-hour battery, Liquid Retina XDR display.'],
            ['category_slug' => 'laptops', 'name' => 'Dell XPS 15 OLED', 'brand' => 'Dell', 'price' => 1899.99, 'compare_price' => 2099.99, 'stock' => 25, 'rating' => 4.7, 'reviews_count' => 287, 'is_featured' => true, 'image' => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=600', 'short_description' => 'Intel Core i9, RTX 4070, 3.5K OLED touchscreen display.'],
            ['category_slug' => 'laptops', 'name' => 'ASUS ROG Zephyrus G14', 'brand' => 'ASUS', 'price' => 1599.99, 'compare_price' => 1799.99, 'stock' => 30, 'rating' => 4.8, 'reviews_count' => 356, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=600', 'short_description' => 'AMD Ryzen 9, RTX 4060, compact gaming powerhouse.'],
            ['category_slug' => 'laptops', 'name' => 'Lenovo ThinkPad X1 Carbon', 'brand' => 'Lenovo', 'price' => 1399.99, 'compare_price' => 1599.99, 'stock' => 45, 'rating' => 4.6, 'reviews_count' => 198, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600', 'short_description' => 'Ultra-thin business laptop with military-grade durability.'],
            ['category_slug' => 'laptops', 'name' => 'HP Spectre x360 14', 'brand' => 'HP', 'price' => 1299.99, 'compare_price' => 1499.99, 'stock' => 35, 'rating' => 4.5, 'reviews_count' => 167, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=600', 'short_description' => '2-in-1 convertible with OLED display and Intel Evo platform.'],

            // Audio
            ['category_slug' => 'audio', 'name' => 'Sony WH-1000XM5', 'brand' => 'Sony', 'price' => 349.99, 'compare_price' => 399.99, 'stock' => 80, 'rating' => 4.9, 'reviews_count' => 624, 'is_featured' => true, 'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600', 'short_description' => 'Industry-leading noise cancellation, 30-hour battery life.'],
            ['category_slug' => 'audio', 'name' => 'Apple AirPods Pro 2', 'brand' => 'Apple', 'price' => 249.99, 'compare_price' => 279.99, 'stock' => 100, 'rating' => 4.8, 'reviews_count' => 891, 'is_featured' => true, 'image' => 'https://images.unsplash.com/photo-1600294037681-c80b4cb5b434?w=600', 'short_description' => 'Adaptive Audio, Transparency mode, MagSafe charging case.'],
            ['category_slug' => 'audio', 'name' => 'Bose QuietComfort 45', 'brand' => 'Bose', 'price' => 279.99, 'compare_price' => 329.99, 'stock' => 60, 'rating' => 4.7, 'reviews_count' => 445, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=600', 'short_description' => 'World-class ANC with TriPort acoustic architecture.'],
            ['category_slug' => 'audio', 'name' => 'Sennheiser Momentum 4', 'brand' => 'Sennheiser', 'price' => 299.99, 'compare_price' => 349.99, 'stock' => 45, 'rating' => 4.7, 'reviews_count' => 312, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1583394838336-acd977736f90?w=600', 'short_description' => 'Premium sound with 60-hour battery and adaptive ANC.'],
            ['category_slug' => 'audio', 'name' => 'JBL Flip 6 Bluetooth Speaker', 'brand' => 'JBL', 'price' => 129.99, 'compare_price' => 149.99, 'stock' => 120, 'rating' => 4.6, 'reviews_count' => 534, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=600', 'short_description' => 'Waterproof portable speaker with bold JBL Pro sound.'],

            // Gaming
            ['category_slug' => 'gaming', 'name' => 'PlayStation 5 Slim', 'brand' => 'Sony', 'price' => 449.99, 'compare_price' => 499.99, 'stock' => 15, 'rating' => 4.9, 'reviews_count' => 1024, 'is_featured' => true, 'image' => 'https://images.unsplash.com/photo-1607853202273-797f1c22a38e?w=600', 'short_description' => 'Next-gen gaming with ultra-high speed SSD and DualSense.'],
            ['category_slug' => 'gaming', 'name' => 'Xbox Series X', 'brand' => 'Microsoft', 'price' => 499.99, 'compare_price' => 549.99, 'stock' => 20, 'rating' => 4.8, 'reviews_count' => 756, 'is_featured' => true, 'image' => 'https://images.unsplash.com/photo-1621259182978-fbf93132d53d?w=600', 'short_description' => 'The fastest most powerful Xbox ever with 12 teraflops.'],
            ['category_slug' => 'gaming', 'name' => 'ASUS ROG Ally X', 'brand' => 'ASUS', 'price' => 799.99, 'compare_price' => 899.99, 'stock' => 25, 'rating' => 4.7, 'reviews_count' => 289, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1593305841991-05c297ba4575?w=600', 'short_description' => 'Handheld gaming PC with AMD Ryzen Z1 Extreme.'],
            ['category_slug' => 'gaming', 'name' => 'Razer DeathAdder V3 Pro', 'brand' => 'Razer', 'price' => 149.99, 'compare_price' => 179.99, 'stock' => 75, 'rating' => 4.7, 'reviews_count' => 413, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=600', 'short_description' => 'Wireless ergonomic gaming mouse with Focus Pro 30K sensor.'],
            ['category_slug' => 'gaming', 'name' => 'SteelSeries Arctis Nova Pro', 'brand' => 'SteelSeries', 'price' => 249.99, 'compare_price' => 299.99, 'stock' => 40, 'rating' => 4.6, 'reviews_count' => 278, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1599669454699-248893623440?w=600', 'short_description' => 'Premium gaming headset with ANC and multi-system connect.'],

            // Cameras
            ['category_slug' => 'cameras', 'name' => 'Sony A7R V Mirrorless', 'brand' => 'Sony', 'price' => 3499.99, 'compare_price' => 3799.99, 'stock' => 10, 'rating' => 4.9, 'reviews_count' => 187, 'is_featured' => true, 'image' => 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=600', 'short_description' => '61MP full-frame sensor with 693-point phase detection AF.'],
            ['category_slug' => 'cameras', 'name' => 'Canon EOS R6 Mark II', 'brand' => 'Canon', 'price' => 2499.99, 'compare_price' => 2699.99, 'stock' => 15, 'rating' => 4.8, 'reviews_count' => 243, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600', 'short_description' => '40fps electronic shutter, 6K video, subject tracking AF.'],
            ['category_slug' => 'cameras', 'name' => 'GoPro Hero 12 Black', 'brand' => 'GoPro', 'price' => 399.99, 'compare_price' => 449.99, 'stock' => 50, 'rating' => 4.7, 'reviews_count' => 567, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1488590528505-98d2b5aba04b?w=600', 'short_description' => '5.3K video, HyperSmooth 6.0, waterproof to 33ft.'],
            ['category_slug' => 'cameras', 'name' => 'DJI Osmo Pocket 3', 'brand' => 'DJI', 'price' => 519.99, 'compare_price' => 569.99, 'stock' => 30, 'rating' => 4.8, 'reviews_count' => 334, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1544866092-1677b814ebbf?w=600', 'short_description' => '1-inch CMOS sensor, 4K/120fps, 3-axis gimbal stabilizer.'],

            // Smart Home
            ['category_slug' => 'smart-home', 'name' => 'Amazon Echo Show 10', 'brand' => 'Amazon', 'price' => 249.99, 'compare_price' => 279.99, 'stock' => 55, 'rating' => 4.6, 'reviews_count' => 423, 'is_featured' => true, 'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600', 'short_description' => 'Smart display with motion tracking and Alexa built-in.'],
            ['category_slug' => 'smart-home', 'name' => 'Philips Hue Starter Kit', 'brand' => 'Philips', 'price' => 179.99, 'compare_price' => 199.99, 'stock' => 70, 'rating' => 4.7, 'reviews_count' => 612, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1543513009-dbd93e0b5c2a?w=600', 'short_description' => 'Smart LED bulbs with 16 million colors, voice & app control.'],
            ['category_slug' => 'smart-home', 'name' => 'Nest Learning Thermostat', 'brand' => 'Google', 'price' => 249.99, 'compare_price' => 279.99, 'stock' => 40, 'rating' => 4.5, 'reviews_count' => 389, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1567581935884-3349723552ca?w=600', 'short_description' => 'Learns your schedule and programs itself to save energy.'],
            ['category_slug' => 'smart-home', 'name' => 'Ring Video Doorbell Pro 2', 'brand' => 'Ring', 'price' => 199.99, 'compare_price' => 249.99, 'stock' => 60, 'rating' => 4.4, 'reviews_count' => 718, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=600', 'short_description' => '3D motion detection with HDR video and Alexa compatibility.'],

            // Wearables
            ['category_slug' => 'wearables', 'name' => 'Apple Watch Ultra 2', 'brand' => 'Apple', 'price' => 799.99, 'compare_price' => 849.99, 'stock' => 25, 'rating' => 4.9, 'reviews_count' => 312, 'is_featured' => true, 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600', 'short_description' => 'Titanium case, 60-hour battery, precision GPS for athletes.'],
            ['category_slug' => 'wearables', 'name' => 'Samsung Galaxy Watch 6 Classic', 'brand' => 'Samsung', 'price' => 399.99, 'compare_price' => 449.99, 'stock' => 40, 'rating' => 4.7, 'reviews_count' => 267, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600', 'short_description' => 'Rotating bezel, body composition analysis, sleep coaching.'],
            ['category_slug' => 'wearables', 'name' => 'Fitbit Charge 6', 'brand' => 'Fitbit', 'price' => 159.99, 'compare_price' => 179.99, 'stock' => 80, 'rating' => 4.5, 'reviews_count' => 489, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1575311373937-040b8e1fd5b6?w=600', 'short_description' => 'Built-in GPS, Google Maps, heart rate & stress tracking.'],
            ['category_slug' => 'wearables', 'name' => 'Garmin Forerunner 965', 'brand' => 'Garmin', 'price' => 599.99, 'compare_price' => 649.99, 'stock' => 20, 'rating' => 4.8, 'reviews_count' => 198, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600', 'short_description' => 'AMOLED display, 31-day battery, advanced running dynamics.'],

            // Accessories
            ['category_slug' => 'accessories', 'name' => 'Anker 727 Charging Station', 'brand' => 'Anker', 'price' => 79.99, 'compare_price' => 99.99, 'stock' => 150, 'rating' => 4.7, 'reviews_count' => 892, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1625948515-b3e10b35b68c?w=600', 'short_description' => '100W 6-port desktop charging station with USB-C & USB-A.'],
            ['category_slug' => 'accessories', 'name' => 'Samsung 990 Pro 2TB SSD', 'brand' => 'Samsung', 'price' => 149.99, 'compare_price' => 189.99, 'stock' => 90, 'rating' => 4.8, 'reviews_count' => 567, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=600', 'short_description' => 'NVMe M.2 SSD with 7,450 MB/s sequential read speed.'],
            ['category_slug' => 'accessories', 'name' => 'Logitech MX Master 3S', 'brand' => 'Logitech', 'price' => 99.99, 'compare_price' => 119.99, 'stock' => 110, 'rating' => 4.9, 'reviews_count' => 1243, 'is_featured' => true, 'image' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=600', 'short_description' => 'Ultra-fast 8K DPI sensor, whisper-quiet clicks, 70-day battery.'],
            ['category_slug' => 'accessories', 'name' => 'Keychron Q1 Pro Keyboard', 'brand' => 'Keychron', 'price' => 199.99, 'compare_price' => 229.99, 'stock' => 55, 'rating' => 4.8, 'reviews_count' => 378, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1595044426077-d36d9236d54a?w=600', 'short_description' => 'QMK wireless mechanical keyboard with aluminum body.'],
            ['category_slug' => 'accessories', 'name' => 'CalDigit TS4 Thunderbolt 4 Dock', 'brand' => 'CalDigit', 'price' => 299.99, 'compare_price' => 349.99, 'stock' => 35, 'rating' => 4.7, 'reviews_count' => 289, 'is_featured' => false, 'image' => 'https://images.unsplash.com/photo-1586210579191-33b45e38fa2c?w=600', 'short_description' => '18 ports, 98W laptop charging, dual 4K display support.'],
        ];

        foreach ($products as $index => $data) {
            $slug = $data['category_slug'];
            unset($data['category_slug']);

            Product::create(array_merge($data, [
                'category_id' => $cats[$slug],
                'slug' => \Illuminate\Support\Str::slug($data['name']),
                'description' => $data['short_description'] . ' Engineered for performance and designed for modern life, this product combines cutting-edge technology with premium build quality. Perfect for professionals and enthusiasts alike who demand the very best from their devices.',
                'sku' => 'SKU-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'specs' => [
                    'Warranty' => '1 Year',
                    'In the Box' => 'Device, Cable, Documentation',
                    'Color' => ['Black', 'Silver', 'White'][array_rand(['Black', 'Silver', 'White'])],
                ],
            ]));
        }
    }
}
