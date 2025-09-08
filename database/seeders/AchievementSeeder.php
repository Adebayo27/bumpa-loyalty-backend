<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Achievement;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        Achievement::insert([
            [
                'key' => 'spender_1000',
                'name' => 'Spend 1000',
                'description' => 'Spend a total of $1000',
                'rules' => json_encode(['type' => 'total_spend', 'target' => 1000]),
                'points' => 10,
            ],
            [
                'key' => 'spender_5000',
                'name' => 'Spend 5000',
                'description' => 'Spend a total of $500',
                'rules' => json_encode(['type' => 'total_spend', 'target' => 5000]),
                'points' => 50,
            ],
            [
                'key' => 'spender_100000',
                'name' => 'Spend 100000',
                'description' => 'Spend a total of $1000',
                'rules' => json_encode(['type' => 'total_spend', 'target' => 100000]),
                'points' => 100,
            ],
            [
                'key' => 'orders_10',
                'name' => '10 Orders',
                'description' => 'Complete 10 orders',
                'rules' => json_encode(['type' => 'orders_count', 'target' => 10]),
                'points' => 20,
            ],
            [
                'key' => 'categories_5',
                'name' => '5 Categories',
                'description' => 'Buy from 5 different categories',
                'rules' => json_encode(['type' => 'category_count', 'target' => 5]),
                'points' => 30,
            ],
        ]);
    }
}
