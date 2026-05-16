<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'loyalty_coupon_id',
        'registration_id'
    ];

    public function loyaltyCoupon(): BelongsTo
    {
        return $this->belongsTo(LoyaltyCoupon::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }
}
