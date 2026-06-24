<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'phone', 'email',
        'points', 'credit_limit', 'credit_used', 'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_used'  => 'decimal:2',
        'is_active'    => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
