<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ShopDemoSeeder extends Seeder
{
    public function run(): void
    {
        $category = ProductCategory::firstOrCreate(
            ['slug' => 'merch-alumni'],
            [
                'name' => 'Merchandise Alumni',
                'description' => 'Merch resmi IKA UNIMED untuk alumni.',
                'is_active' => true,
            ]
        );

        $products = [
            [
                'name' => 'Kaos IKA UNIMED edisi Classic',
                'slug' => 'kaos-ikaunimed-classic',
                'sku' => 'TSHIRT-IKA-CLASSIC',
                'description' => 'Kaos hitam dengan logo IKA UNIMED, bahan cotton combed 30s.',
                'price' => 125000,
                'stock' => 50,
                'type' => 'physical',
            ],
            [
                'name' => 'Mug IKA UNIMED',
                'slug' => 'mug-ikaunimed',
                'sku' => 'MUG-IKA-001',
                'description' => 'Mug keramik putih dengan logo IKA UNIMED, cocok untuk souvenir.',
                'price' => 65000,
                'stock' => 100,
                'type' => 'physical',
            ],
            [
                'name' => 'E-book Panduan Karir Alumni',
                'slug' => 'ebook-panduan-karir-alumni',
                'sku' => 'EBOOK-KARIR-ALUMNI',
                'description' => 'E-book PDF berisi panduan pengembangan karir untuk alumni UNIMED.',
                'price' => 45000,
                'stock' => 9999,
                'type' => 'digital',
            ],
        ];

        foreach ($products as $data) {
            Product::firstOrCreate(
                ['slug' => $data['slug']],
                [
                    'product_category_id' => $category->id,
                    'organization_id' => null,
                    'name' => $data['name'],
                    'sku' => $data['sku'],
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'stock' => $data['stock'],
                    'type' => $data['type'],
                    'is_published' => true,
                    'published_at' => now(),
                ]
            );
        }
    }
}

