<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Item;
use App\Support\ItemCodeGenerator;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $electronics = Category::where('code', 'ELEC')->first();
        $furniture = Category::where('code', 'FURN')->first();
        $office = Category::where('code', 'OFFC')->first();
        $fnb = Category::where('code', 'FNB')->first();
        $clothing = Category::where('code', 'CLTH')->first();
        $hardware = Category::where('code', 'HDWR')->first();

        $items = [
            [
                'category_id' => $electronics->id,
                'name' => 'Laptop Dell XPS 13',
                'description' => 'High-performance ultrabook with Intel i7 processor',
                'price' => 15000000,
                'stock_quantity' => 10,
                'metadata' => ['brand' => 'Dell', 'processor' => 'Intel i7', 'ram' => '16GB'],
                'is_active' => true,
            ],
            [
                'category_id' => $electronics->id,
                'name' => 'Wireless Mouse Logitech',
                'description' => 'Ergonomic wireless mouse with USB receiver',
                'price' => 250000,
                'stock_quantity' => 50,
                'metadata' => ['brand' => 'Logitech', 'connectivity' => 'Wireless', 'dpi' => '1600'],
                'is_active' => true,
            ],
            [
                'category_id' => $electronics->id,
                'name' => 'Monitor Samsung 24"',
                'description' => 'Full HD LED monitor with HDMI port',
                'price' => 2500000,
                'stock_quantity' => 15,
                'metadata' => ['brand' => 'Samsung', 'size' => '24 inch', 'resolution' => '1920x1080'],
                'is_active' => true,
            ],
            [
                'category_id' => $furniture->id,
                'name' => 'Office Chair Ergonomic',
                'description' => 'Adjustable ergonomic office chair with lumbar support',
                'price' => 1500000,
                'stock_quantity' => 25,
                'metadata' => ['material' => 'Mesh', 'adjustable' => 'Yes', 'color' => 'Black'],
                'is_active' => true,
            ],
            [
                'category_id' => $furniture->id,
                'name' => 'Standing Desk Electric',
                'description' => 'Height-adjustable electric standing desk',
                'price' => 5000000,
                'stock_quantity' => 8,
                'metadata' => ['height_range' => '70-120cm', 'surface' => 'Wood', 'motor' => 'Dual'],
                'is_active' => true,
            ],
            [
                'category_id' => $office->id,
                'name' => 'Printer HP LaserJet',
                'description' => 'Monochrome laser printer with WiFi',
                'price' => 3000000,
                'stock_quantity' => 12,
                'metadata' => ['brand' => 'HP', 'type' => 'Laser', 'connectivity' => 'WiFi'],
                'is_active' => true,
            ],
            [
                'category_id' => $office->id,
                'name' => 'Paper A4 80gsm (Ream)',
                'description' => 'High quality copy paper, 500 sheets per ream',
                'price' => 45000,
                'stock_quantity' => 200,
                'metadata' => ['weight' => '80gsm', 'sheets' => '500', 'size' => 'A4'],
                'is_active' => true,
            ],
            [
                'category_id' => $office->id,
                'name' => 'Stapler Heavy Duty',
                'description' => 'Heavy duty stapler for up to 50 sheets',
                'price' => 85000,
                'stock_quantity' => 40,
                'metadata' => ['capacity' => '50 sheets', 'color' => 'Silver'],
                'is_active' => true,
            ],
            [
                'category_id' => $fnb->id,
                'name' => 'Coffee Beans Arabica 1kg',
                'description' => 'Premium Arabica coffee beans from Aceh',
                'price' => 150000,
                'stock_quantity' => 30,
                'metadata' => ['origin' => 'Aceh', 'roast' => 'Medium', 'weight' => '1kg'],
                'is_active' => true,
            ],
            [
                'category_id' => $fnb->id,
                'name' => 'Mineral Water 600ml (Box)',
                'description' => 'Bottled mineral water, 24 bottles per box',
                'price' => 35000,
                'stock_quantity' => 100,
                'metadata' => ['volume' => '600ml', 'bottles' => '24', 'brand' => 'Aqua'],
                'is_active' => true,
            ],
            [
                'category_id' => $clothing->id,
                'name' => 'Polo Shirt Cotton Navy',
                'description' => 'Comfortable cotton polo shirt in navy blue',
                'price' => 150000,
                'stock_quantity' => 45,
                'metadata' => ['material' => 'Cotton', 'color' => 'Navy', 'size' => 'L'],
                'is_active' => true,
            ],
            [
                'category_id' => $clothing->id,
                'name' => 'Work Pants Chino Khaki',
                'description' => 'Professional chino work pants in khaki color',
                'price' => 200000,
                'stock_quantity' => 35,
                'metadata' => ['material' => 'Cotton Blend', 'color' => 'Khaki', 'size' => '32'],
                'is_active' => true,
            ],
            [
                'category_id' => $hardware->id,
                'name' => 'Screwdriver Set 12pcs',
                'description' => 'Professional screwdriver set with magnetic tips',
                'price' => 180000,
                'stock_quantity' => 22,
                'metadata' => ['pieces' => '12', 'type' => 'Magnetic', 'case' => 'Included'],
                'is_active' => true,
            ],
            [
                'category_id' => $hardware->id,
                'name' => 'Power Drill Cordless',
                'description' => '18V cordless power drill with battery',
                'price' => 1200000,
                'stock_quantity' => 15,
                'metadata' => ['voltage' => '18V', 'battery' => 'Li-ion', 'brand' => 'Bosch'],
                'is_active' => true,
            ],
        ];

        foreach ($items as $itemData) {
            $sku = ItemCodeGenerator::generate($itemData['category_id']);
            Item::create(array_merge($itemData, ['sku' => $sku]));
        }
    }
}
