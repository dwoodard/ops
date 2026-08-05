<?php

namespace App\Models;

use Database\Factories\ObjectiveFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Objective extends Model
{
    /** @use HasFactory<ObjectiveFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = ['enriched_data' => 'json', 'end_date' => 'datetime'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function signals()
    {
        return $this->hasMany(Signal::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ObjectiveActivityLog::class);
    }

    public function actionSuccessRate(string $actionType): float
    {
        $total = $this->signals()
            ->get()
            ->sum(fn ($s) => count($s->actions_and_results));

        $successful = $this->signals()
            ->get()
            ->sum(function ($signal) use ($actionType) {
                return collect($signal->actions_and_results)
                    ->filter(fn ($ar) => $ar['action']['action_type'] === $actionType)
                    ->filter(fn ($ar) => in_array($ar['result']['status'] ?? null, ['replied', 'won']))
                    ->count();
            });

        return $total > 0 ? $successful / $total : 0;
    }

    public function progress(): array
    {
        $total = $this->opportunities()->count();
        $won = $this->opportunities()
            ->where('overall_status', 'closed_won')->count();

        return [
            'target' => 10,
            'current' => $won,
            'in_progress' => $total - $won,
            'percentage' => $total > 0 ? ($won / $total) * 100 : 0,
        ];
    }
}
