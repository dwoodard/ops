<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObjectiveActivityLog extends Model
{
    use HasFactory;

    protected $table = 'objective_activity_logs';

    protected $guarded = [];

    protected $casts = [
        'details' => 'json',
        'timestamp' => 'datetime',
    ];

    public function objective()
    {
        return $this->belongsTo(Objective::class);
    }
}
