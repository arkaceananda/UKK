<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recap extends Model
{
    protected $table = 'recaps';

    protected $fillable = [
        'type',
        'period_start',
        'period_end',
        'total_revenue',
        'total_orders',
        'total_items_sold',
        'top_menus',
        'daily_breakdown',
        'is_finalized',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'total_revenue' => 'decimal:2',
            'total_orders' => 'integer',
            'total_items_sold' => 'integer',
            'top_menus' => 'array',
            'daily_breakdown' => 'array',
            'is_finalized' => 'boolean',
            'finalized_at' => 'datetime',
        ];
    }

    public function scopeDaily($query)
    {
        return $query->where('type', 'daily');
    }

    public function scopeWeekly($query)
    {
        return $query->where('type', 'weekly');
    }

    public function scopeMonthly($query)
    {
        return $query->where('type', 'monthly');
    }

    public function scopeFinalized($query)
    {
        return $query->where('is_finalized', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_finalized', false);
    }
}
