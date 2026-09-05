<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'year',
        'description',
        'stack',
        'link',
        'sort_order',
    ];

    protected $casts = [
        'stack' => 'array',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Project $project) {
            if (empty($project->slug)) {
                $project->slug = Str::slug($project->name);
            }
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('year');
    }
}
