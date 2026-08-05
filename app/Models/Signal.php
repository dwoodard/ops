<?php

namespace App\Models;

use Database\Factories\SignalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signal extends Model
{
    /** @use HasFactory<SignalFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'json',
        'actions_and_results' => 'json',
        'detected_at' => 'datetime',
    ];

    public function objective()
    {
        return $this->belongsTo(Objective::class);
    }

    public function getPendingActions()
    {
        return collect($this->actions_and_results)
            ->filter(fn ($ar) => ! isset($ar['result']))
            ->values()
            ->all();
    }

    public function scopeWithPendingActions($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('actions_and_results->*.result')
                ->orWhere('actions_and_results', 'like', '%"result": null%');
        });
    }

    public function hasUnresolvedActions(): bool
    {
        return count($this->getPendingActions()) > 0;
    }
}
