<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(600)->height(600)->nonQueued();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images') ?: null;
    }
}
