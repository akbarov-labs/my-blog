<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'category',
        'blocks',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'blocks' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeCategory($query, ?string $category)
    {
        if (empty($category) || $category === 'All') {
            return $query;
        }

        return $query->where('category', $category);
    }

    public function meta(): string
    {
        $date = $this->published_at ?? $this->created_at;

        return ($date?->format('F j, Y') ?? '') . ' · ' . $this->category;
    }
}
