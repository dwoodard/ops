<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignalSource extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'auth_required' => 'boolean',
        'category' => 'array',
        'frequency_options' => 'array',
    ];

    /**
     * Scope to get only active sources.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->whereJsonContains('category', $category);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get sources by priority.
     */
    public function scopeOrderByPriority($query, $direction = 'desc')
    {
        return $query->orderBy('priority', $direction);
    }

    /**
     * Check if this source requires authentication.
     */
    public function requiresAuth(): bool
    {
        return $this->auth_required;
    }

    /**
     * Check if this source is free.
     */
    public function isFree(): bool
    {
        return $this->cost_tier === 'free';
    }

    /**
     * Check if this source has a specific category.
     */
    public function hasCategory(string $category): bool
    {
        return in_array($category, $this->category ?? []);
    }

    /**
     * Check if this source supports a specific frequency.
     */
    public function supportsFrequency(string $frequency): bool
    {
        return in_array($frequency, $this->frequency_options ?? []);
    }

    /**
     * Get formatted display name with slug.
     */
    public function getDisplayName(): string
    {
        return "{$this->name} ({$this->slug})";
    }
}
