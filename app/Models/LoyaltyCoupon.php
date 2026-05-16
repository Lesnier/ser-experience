<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyCoupon extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope('brand_representative', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (auth()->check() && auth()->user()->role_id == 3) {
                $builder->whereHas('brand.users', function ($query) {
                    $query->where('users.id', auth()->id());
                });
            }
        });
    }

    protected $fillable = [
        'brand_id',
        'title',
        'description',
        'discount_type',
        'discount_value',
        'global_limit',
        'usage_limit_per_attendee',
        'allocation_strategy',
        'validity_scope',
        'allow_brand_modification',
        'is_active',
        'valid_from',
        'valid_to'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'global_limit' => 'integer',
        'usage_limit_per_attendee' => 'integer',
        'allow_brand_modification' => 'boolean',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(CouponAllocation::class);
    }
}
