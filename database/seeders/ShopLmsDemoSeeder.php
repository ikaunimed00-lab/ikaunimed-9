<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

class ShopLmsDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('courses') || ! Schema::hasTable('products')) {
            $this->command?->warn('Tabel courses atau products belum tersedia, lewati ShopLmsDemoSeeder.');

            return;
        }

        if (! Schema::hasColumn('products', 'course_id')) {
            $this->command?->warn('Kolom course_id pada tabel products belum tersedia, lewati ShopLmsDemoSeeder.');

            return;
        }

        $creator = User::first();

        if (! $creator) {
            $this->command?->warn('Tidak ada user di database, lewati ShopLmsDemoSeeder.');

            return;
        }

        $category = CourseCategory::firstOrCreate(
            ['slug' => 'demo-shop-lms'],
            [
                'name' => 'Demo Shop ↔ LMS',
                'description' => 'Kategori demo untuk menguji integrasi E-Shop dan LMS.',
            ]
        );

        $course = Course::firstOrCreate(
            ['slug' => 'demo-course-shop-lms'],
            [
                'title' => 'Demo Course: Integrasi E-Shop ↔ LMS',
                'description' => 'Course demo untuk menguji alur pembelian produk digital hingga Enrollment LMS.',
                'thumbnail' => null,
                'instructor_name' => $creator->name,
                'level' => 'Beginner',
                'status' => 'published',
                'duration_minutes' => 30,
                'category_id' => $category->id,
                'created_by' => $creator->id,
                'is_paid' => true,
                'price' => 45000,
            ]
        );

        $productCategory = ProductCategory::firstOrCreate(
            ['slug' => 'produk-digital-lms'],
            [
                'organization_id' => null,
                'name' => 'Produk Digital LMS',
                'description' => 'Produk digital yang terhubung dengan course LMS.',
                'is_active' => true,
            ]
        );

        Product::firstOrCreate(
            ['slug' => 'demo-course-product-lms'],
            [
                'organization_id' => null,
                'product_category_id' => $productCategory->id,
                'course_id' => $course->id,
                'name' => 'Demo Course Product (LMS)',
                'sku' => 'COURSE-DEMO-LMS',
                'description' => 'Produk demo untuk menguji integrasi Shop → Tripay → LMS Enrollment.',
                'price' => 45000,
                'stock' => 9999,
                'type' => 'digital',
                'is_published' => true,
                'published_at' => now(),
            ]
        );

        $this->command?->info('Demo course dan produk digital LMS berhasil disiapkan.');
    }
}
