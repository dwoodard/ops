<?php

namespace App\Models;

use App\Enums\OpportunityEngagementStatus;
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
        'engagement_status' => OpportunityEngagementStatus::class,
    ];

    public function objective()
    {
        return $this->belongsTo(Objective::class);
    }

    // owner is the user who is responsible for this opportunity
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // contacts are the people associated with this opportunity
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function recommendations()
    {
        return $this->hasMany(Recommendation::class);
    }
}
