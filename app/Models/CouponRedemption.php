<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponRedemption extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope('brand_representative', function (\Illuminate\Database\Eloquent\Builder $builder) {
            if (auth()->check() && auth()->user()->role_id == 3) {
                $builder->whereHas('loyaltyCoupon.brand.users', function ($query) {
                    $query->where('users.id', auth()->id());
                });
            }
        });
    }

    protected $fillable = [
        'loyalty_coupon_id',
        'registration_id',
        'processed_by_user_id',
        'redeemed_at',
        'notes'
    ];

    protected $casts = [
        'redeemed_at' => 'datetime'
    ];

    public function loyaltyCoupon(): BelongsTo
    {
        return $this->belongsTo(LoyaltyCoupon::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }
}
