<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'favicon', 'is_active', 'html_content'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($landingPage) {
            // Si la landing actual se está guardando como activa
            if ($landingPage->is_active) {
                // Desactivar todas las demás
                static::where('id', '!=', $landingPage->id)->update(['is_active' => false]);
            }
        });
    }

    public function forms()
    {
        return $this->hasMany(CustomForm::class, 'landing_page_id');
    }
}
