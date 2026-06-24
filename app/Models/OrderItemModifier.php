<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemModifier extends Model
{
    protected $fillable = [
        'order_item_id', 'modifier_option_id',
        'modifier_name', 'option_name', 'price',
    ];

    protected $casts = ['price' => 'decimal:2'];

    public function orderItem() { return $this->belongsTo(OrderItem::class); }
}
