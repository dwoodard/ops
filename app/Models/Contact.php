<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_direct' => 'boolean',
        'is_decision_maker' => 'boolean',
        'verified' => 'boolean',
    ];

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function qualityScore(): float
    {
        $score = 0;
        $score += $this->is_direct ? 0.3 : 0.1;
        $score += $this->is_decision_maker ? 0.4 : 0.2;
        $score += $this->verified ? 0.3 : 0.2;

        return $score;
    }
}
