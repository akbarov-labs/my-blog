<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'role',
        'company',
        'description',
        'start_date',
        'end_date',
        'sort_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'sort_order' => 'integer',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function period(): string
    {
        $start = $this->start_date?->format('Y') ?? '';
        $end = $this->end_date?->format('Y') ?? 'Present';

        return trim($start . ' – ' . $end, ' –');
    }
}
