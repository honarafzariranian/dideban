<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Andookhtiar',
                'persian_name' => 'اندوختیار',
                'slug' => 'andookhtiar',
                'description' => 'سیستم مدیریت انبار و موجودی',
                'icon' => 'warehouse',
                'color' => '#3B82F6',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Fishk',
                'persian_name' => 'فیشک',
                'slug' => 'fishk',
                'description' => 'سیستم مدیریت حقوق و دستمزد',
                'icon' => 'calculator',
                'color' => '#10B981',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Diyara',
                'persian_name' => 'دیارا',
                'slug' => 'diyara',
                'description' => 'سیستم مدیریت ارتباط با مشتری',
                'icon' => 'users',
                'color' => '#8B5CF6',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Fan-Hesab',
                'persian_name' => 'فن حساب',
                'slug' => 'fan-hesab',
                'description' => 'سیستم حسابداری و مدیریت مالی',
                'icon' => 'chart-bar',
                'color' => '#F59E0B',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Nameh-Yar',
                'persian_name' => 'نامه یار',
                'slug' => 'nameh-yar',
                'description' => 'سیستم مکاتبات اداری و اتوماسیون',
                'icon' => 'mail',
                'color' => '#EF4444',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['slug' => $product['slug']],
                $product
            );
        }
    }
}
