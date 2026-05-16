<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'attendee_id',
        'ticket_type_id',
        'entry_code',
        'loyalty_code',
        'status',
        'checked_in_at',
        'checkout_at'
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'checkout_at' => 'datetime'
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function couponRedemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    public function couponAllocations(): HasMany
    {
        return $this->hasMany(CouponAllocation::class);
    }
}
