<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class ModifierGroup extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'name', 'is_required', 'is_multiple', 'min_select', 'max_select',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_multiple' => 'boolean',
    ];

    public function options()
    {
        return $this->hasMany(ModifierOption::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_modifier_groups');
    }
}
