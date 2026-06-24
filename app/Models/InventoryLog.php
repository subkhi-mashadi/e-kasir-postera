<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryLog extends Model
{
    protected $fillable = [
        'product_id', 'branch_id', 'user_id', 'type',
        'qty_before', 'qty_change', 'qty_after',
        'reference_type', 'reference_id', 'notes',
    ];

    protected $casts = [
        'qty_before' => 'decimal:3',
        'qty_change' => 'decimal:3',
        'qty_after'  => 'decimal:3',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function branch()  { return $this->belongsTo(Branch::class); }
    public function user()    { return $this->belongsTo(User::class); }
}
