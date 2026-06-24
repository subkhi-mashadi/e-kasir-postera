<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use BelongsToCompany, InteractsWithMedia;

    protected $fillable = [
        'company_id', 'category_id', 'name', 'sku', 'barcode',
        'description', 'unit', 'price', 'cost_price', 'tax_rate',
        'track_stock', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'cost_price'  => 'decimal:2',
        'tax_rate'    => 'decimal:2',
        'track_stock' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function modifierGroups()
    {
        return $this->belongsToMany(ModifierGroup::class, 'product_modifier_groups');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function inventory(int $branchId)
    {
        return $this->inventories()->where('branch_id', $branchId)->first();
    }
}
