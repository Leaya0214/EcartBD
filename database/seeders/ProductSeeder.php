<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $images = [
            'frontend/images/product1.jpg',
            'frontend/images/product2.jpg',
            'frontend/images/product3.jpg',
            'frontend/images/product4.jpg',
        ];
        $products = [];
        for ($i = 1; $i <= 200; $i++) {
            $products[] = [
                'vendor_id' => rand(1, 10),
                'cat_id' => rand(1, 10),
                'subcat_id' => rand(1, 10),
                'product_name' => 'Product ' . $i,
                'slug' => Str::slug('Product ' . $i),
                'product_code' => 10000 + $i,
                'product_price' => rand(100, 10000) / 100,
                'discount' => rand(0, 30),
                'discount_price' => rand(50, 9000) / 100,
                'short_description' => 'Short description for Product ' . $i,
                'long_description' => 'Long description for Product ' . $i,
                'thumbnails' => $images[array_rand($images)],
                'quantity' => rand(1, 100),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('products')->insert($products);
    }
}
