<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'code', 'type', 'value',
        'min_order', 'max_discount', 'usage_limit', 'used_count',
        'is_active', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'value'       => 'decimal:2',
        'min_order'   => 'decimal:2',
        'max_discount'=> 'decimal:2',
        'is_active'   => 'boolean',
        'starts_at'   => 'datetime',
        'ends_at'     => 'datetime',
    ];
}
