<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'name',
        'headline',
        'bio_1',
        'bio_2',
        'footer_location',
        'footer_note',
    ];

    /**
     * This app only ever has one row of site-wide profile content.
     * Always returns it, creating a blank one on first access.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate([], [
            'name' => 'Your Name',
            'headline' => 'Your headline',
            'bio_1' => 'Write a short introduction about yourself.',
            'bio_2' => 'Add a second paragraph if you like.',
            'footer_location' => '',
            'footer_note' => '',
        ]);
    }
}
