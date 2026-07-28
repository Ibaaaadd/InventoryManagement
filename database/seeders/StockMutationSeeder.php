<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use App\Models\Role;
use App\Models\StockMutation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StockMutationSeeder extends Seeder
{
    public function run(): void
    {
        StockMutation::truncate();

        $categories = Category::all();
        $items = Item::all();
        $users = User::all();

        if ($categories->isEmpty() || $items->isEmpty() || $users->isEmpty()) {
            $this->command->warn('⚠ Categories, Items, or Users not found. Make sure other seeders run first.');
            return;
        }

        $itemsArray = $items->toArray();
        $usersArray = $users->toArray();
        $types = ['in', 'out'];
        $statuses = ['approved', 'approved', 'approved', 'pending', 'rejected'];

        for ($day = 1; $day <= 31; $day++) {
            $mutationsPerDay = rand(2, 5);

            for ($i = 0; $i < $mutationsPerDay; $i++) {
                $item = $itemsArray[array_rand($itemsArray)];
                $user = $usersArray[array_rand($usersArray)];
                $type = $types[array_rand($types)];
                $status = $statuses[array_rand($statuses)];

                $transactionDate = "2026-07-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                $quantity = rand(1, 20);

                StockMutation::create([
                    'item_id' => $item['id'],
                    'user_id' => $user['id'],
                    'type' => $type,
                    'quantity' => $quantity,
                    'status' => $status,
                    'transaction_date' => $transactionDate,
                    'notes' => "Transaksi $type sejumlah $quantity unit pada " . date('d/m/Y', strtotime($transactionDate)),
                    'attachment_path' => 'stock-mutation-attachments/dummy.pdf',
                ]);
            }
        }

        for ($day = 1; $day <= 15; $day++) {
            $item = $itemsArray[array_rand($itemsArray)];
            $transactionDate = "2026-06-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            
            StockMutation::create([
                'item_id' => $item['id'],
                'user_id' => $usersArray[array_rand($usersArray)]['id'],
                'type' => $types[array_rand($types)],
                'quantity' => rand(5, 25),
                'status' => 'approved',
                'transaction_date' => $transactionDate,
                'notes' => "Transaksi bulan Juni",
                'attachment_path' => 'stock-mutation-attachments/dummy.pdf',
            ]);
        }

        for ($day = 1; $day <= 10; $day++) {
            $item = $itemsArray[array_rand($itemsArray)];
            $transactionDate = "2025-12-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            
            StockMutation::create([
                'item_id' => $item['id'],
                'user_id' => $usersArray[array_rand($usersArray)]['id'],
                'type' => $types[array_rand($types)],
                'quantity' => rand(3, 15),
                'status' => 'approved',
                'transaction_date' => $transactionDate,
                'notes' => "Transaksi bulan Desember 2025",
                'attachment_path' => 'stock-mutation-attachments/dummy.pdf',
            ]);
        }

        $lowStockItems = Item::inRandomOrder()->take(5)->get();
        foreach ($lowStockItems as $item) {
            $item->update(['stock_quantity' => rand(3, 10)]);
        }

        $this->command->info('✓ StockMutationSeeder completed successfully!');
        $this->command->info('  - Stock Mutations: ' . StockMutation::count());
        $this->command->info('  - July 2026: ' . StockMutation::whereYear('transaction_date', 2026)->whereMonth('transaction_date', 7)->count());
        $this->command->info('  - June 2026: ' . StockMutation::whereYear('transaction_date', 2026)->whereMonth('transaction_date', 6)->count());
        $this->command->info('  - Dec 2025: ' . StockMutation::whereYear('transaction_date', 2025)->whereMonth('transaction_date', 12)->count());
        $this->command->info('  - Pending: ' . StockMutation::where('status', 'pending')->count());
        $this->command->info('  - Approved: ' . StockMutation::where('status', 'approved')->count());
        $this->command->info('  - Rejected: ' . StockMutation::where('status', 'rejected')->count());
    }
}
