<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class StockMutation extends Model implements Auditable
{
    use HasUuids, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'item_id',
        'user_id',
        'type',
        'quantity',
        'transaction_date',
        'attachment_path',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'item_metadata_snapshot' => 'array',
        'item_price_snapshot' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::creating(function (StockMutation $mutation) {
            self::fillSnapshot($mutation);
        });
    }

    private static function fillSnapshot(StockMutation $mutation)
    {
        $item = Item::with('category')->find($mutation->item_id);
        
        if ($item) {
            $mutation->item_name_snapshot = $item->name;
            $mutation->item_sku_snapshot = $item->sku;
            $mutation->item_category_snapshot = $item->category->name ?? '';
            $mutation->item_price_snapshot = $item->price;
            $mutation->item_metadata_snapshot = $item->metadata;
        }
    }

    public function refreshSnapshot()
    {
        self::fillSnapshot($this);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvals()
    {
        return $this->hasMany(StockMutationApproval::class);
    }
}
