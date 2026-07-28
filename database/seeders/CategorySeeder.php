<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'code' => 'ELEC',
            ],
            [
                'name' => 'Furniture',
                'code' => 'FURN',
            ],
            [
                'name' => 'Office Supplies',
                'code' => 'OFFC',
            ],
            [
                'name' => 'Food & Beverage',
                'code' => 'FNB',
            ],
            [
                'name' => 'Clothing',
                'code' => 'CLTH',
            ],
            [
                'name' => 'Hardware & Tools',
                'code' => 'HDWR',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
