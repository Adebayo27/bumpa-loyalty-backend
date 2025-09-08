<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        Badge::insert([
            [
                'name' => 'Bronze Collector',
                'criteria' => json_encode(['achievements_count' => 1]),
                'rank' => 1,
                'icon' => 'bronze.png',
            ],
            [
                'name' => 'Silver Collector',
                'criteria' => json_encode(['achievements_count' => 3]),
                'rank' => 2,
                'icon' => 'silver.png',
            ],
            [
                'name' => 'Gold Collector',
                'criteria' => json_encode(['achievements_count' => 5]),
                'rank' => 3,
                'icon' => 'gold.png',
            ],
            [
                'name' => 'Order Master',
                'criteria' => json_encode(['achievements_count' => 2]),
                'rank' => 2,
                'icon' => 'order.png',
            ],
            [
                'name' => 'Category Explorer',
                'criteria' => json_encode(['achievements_count' => 4]),
                'rank' => 2,
                'icon' => 'category.png',
            ],
        ]);
    }
}
