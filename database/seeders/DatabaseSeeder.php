<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. تغذية قاعدة البيانات بالمحلات (الزبائن) مع مطابقة أعمدة التحليلات والمناطق وأيام التأخر
        Store::create([
            'name' => 'Supérette El Baraka',
            'ar_name' => 'سوبرماركت البركة', // 👈 الاسم بالعربية للتحليلات
            'owner_name' => 'Abdelkader',
            'phone' => '0550112233',
            'debt' => 84050,
            'debt_age_days' => 2, // 👈 أيام التأخر (تندرج تحت خطر critical وفقاً للمتحكم)
            'credit_limit' => 150000,
            'address' => 'Alger Centre, Rue Didouche Mourad',
            'zone' => 'Alger Centre', // 👈 المنطقة اللوجستية للفلترة
            'status' => 'active'
        ]);

        Store::create([
            'name' => 'Carrefour Dar El Beïda',
            'ar_name' => 'كارفور دار البيضاء',
            'owner_name' => 'Yacine',
            'phone' => '0550445566',
            'debt' => 96400,
            'debt_age_days' => 5, // 👈 أيام التأخر (تندرج تحت خطر danger)
            'credit_limit' => 200000,
            'address' => 'Dar El Beïda, Cité 5 Juillet',
            'zone' => 'Dar El Beïda',
            'status' => 'active'
        ]);

        Store::create([
            'name' => 'Alimentation El Feth',
            'ar_name' => 'تموينات الفتح',
            'owner_name' => 'Mohamed',
            'phone' => '0661223344',
            'debt' => 41200,
            'debt_age_days' => 11, // 👈 أيام التأخر (تندرج تحت خطر safe أو مستقرة محاسبياً)
            'credit_limit' => 90000,
            'address' => 'Hussein Dey, Boulevard Tripoli',
            'zone' => 'Hussein Dey',
            'status' => 'active'
        ]);

        Store::create([
            'name' => 'Épicerie Es-Salam',
            'ar_name' => 'بقالة السلام',
            'owner_name' => 'Karim',
            'phone' => '0770334455',
            'debt' => 68900,
            'debt_age_days' => 1,
            'credit_limit' => 80000,
            'address' => 'Bab Ezzouar, Cité Soummam',
            'zone' => 'Bab Ezzouar',
            'status' => 'active'
        ]);

        Store::create([
            'name' => 'Marché Riadh El Feth',
            'ar_name' => 'سوق رياض الفتح',
            'owner_name' => 'Sofiane',
            'phone' => '0551778899',
            'debt' => 121750,
            'debt_age_days' => 9,
            'credit_limit' => 300000,
            'address' => 'Alger Centre, Riadh El Feth',
            'zone' => 'Alger Centre',
            'status' => 'active'
        ]);

        // 2. تغذية قاعدة البيانات بالمنتجات (الكتالوج) مع مطابقة عمود التصنيف category وحذف حقل الألوان غير الموجود
        Product::create([
            'sku' => 'COCA15',
            'name' => 'Coca-Cola 1.5L',
            'price' => 170,
            'stock' => 120,
            'category' => 'Boissons',
        ]);

        Product::create([
            'sku' => 'IFRI15',
            'name' => 'Eau Minérale Ifri 1.5L',
            'price' => 40,
            'stock' => 300,
            'category' => 'Eau',
        ]);

        Product::create([
            'sku' => 'CHOCO01',
            'name' => 'Chocolat Maxon',
            'price' => 120,
            'stock' => 85,
            'category' => 'Sucrerie',
        ]);

        // مستخدم مشرف للنظام (احتياطي)
        User::factory()->create([
            'name' => 'Ishak Admin',
            'email' => 'ishak@logiz.dz',
        ]);
    }
}