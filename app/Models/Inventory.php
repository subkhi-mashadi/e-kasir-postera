<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = ['product_id', 'branch_id', 'qty', 'min_qty', 'is_available'];

    protected $casts = [
        'qty'          => 'decimal:3',
        'min_qty'      => 'decimal:3',
        'is_available' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
