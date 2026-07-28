<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

class ItemCodeGenerator
{
    public static function generate(string $categoryId): string
    {
        return DB::transaction(function () use ($categoryId) {
            $category = Category::where('id', $categoryId)->lockForUpdate()->firstOrFail();
            $category->last_sequence += 1;
            $category->save();
            
            return $category->code . str_pad($category->last_sequence, 4, '0', STR_PAD_LEFT);
        });
    }
}
