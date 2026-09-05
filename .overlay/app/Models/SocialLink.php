<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'value',
        'href',
        'show_on_hero',
        'show_on_contact',
        'sort_order',
    ];

    protected $casts = [
        'show_on_hero' => 'boolean',
        'show_on_contact' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeOnHero($query)
    {
        return $query->where('show_on_hero', true);
    }

    public function scopeOnContact($query)
    {
        return $query->where('show_on_contact', true);
    }
}
