<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opportunity extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'signal_ids' => 'json',
        'last_signal_updated_at' => 'datetime',
        'fit_score' => 'float',
        'total_deal_value' => 'float',
        'entity_type' => 'string',
    ];

    public function objective()
    {
        return $this->belongsTo(Objective::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }
}
