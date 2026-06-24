<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'type', 'value', 'min_order',
        'is_active', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'value'     => 'decimal:2',
        'min_order' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];
}
