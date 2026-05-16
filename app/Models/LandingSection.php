<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingSection extends Model
{
    use HasFactory;

    protected $fillable = ['landing_page_id', 'type', 'title', 'content', 'image', 'order'];

    public function landingPage()
    {
        return $this->belongsTo(LandingPage::class);
    }
}
