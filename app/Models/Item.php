<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Item extends Model implements Auditable
{
    use HasUuids, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'description',
        'price',
        'stock_quantity',
        'metadata',
        'is_active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
