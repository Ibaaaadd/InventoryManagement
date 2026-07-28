<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class StockMutationApproval extends Model implements Auditable
{
    use HasUuids, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'stock_mutation_id',
        'approved_by',
        'decision',
        'approval_notes',
        'approved_at',
    ];

    protected $casts = [
        'approval_notes' => 'array',
        'approved_at' => 'datetime',
    ];

    public function stockMutation()
    {
        return $this->belongsTo(StockMutation::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
