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
        'views_count' => 'integer',
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

    /**
     * Record a view for this post, at most once per visitor session so a
     * refresh (or the author previewing it) doesn't inflate the count.
     */
    public function registerView(\Illuminate\Http\Request $request): void
    {
        $seen = $request->session()->get('viewed_posts', []);

        if (in_array($this->id, $seen, true)) {
            return;
        }

        $this->increment('views_count');

        $seen[] = $this->id;
        $request->session()->put('viewed_posts', $seen);
    }

    public function viewsLabel(): string
    {
        $count = $this->views_count ?? 0;

        return $count === 1 ? '1 view' : number_format($count) . ' views';
    }
}
