<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'content' => 'json',
        'score_breakdown' => 'json',
        'confidence_score' => 'float',
        'executed_at' => 'datetime',
        'auto_generated' => 'boolean',
    ];

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function executedBy()
    {
        return $this->belongsTo(User::class, 'executed_by');
    }

    public function calculateConfidenceScore(): float
    {
        $opportunity = $this->opportunity;
        $fitScore = $opportunity->fit_score;
        $signalRecency = $this->signalRecencyScore();
        $actionRate = $this->actionSuccessRate();
        $contactQuality = $opportunity->contacts()->first()?->qualityScore() ?? 0.5;

        return ($fitScore * 0.4) + ($signalRecency * 0.3) +
               ($actionRate * 0.2) + ($contactQuality * 0.1);
    }

    private function signalRecencyScore(): float
    {
        $newestSignal = $this->opportunity->objective->signals()->latest('detected_at')->first();
        if (! $newestSignal) {
            return 0.5;
        }
        $hoursSince = now()->diffInHours($newestSignal->detected_at);

        return max(0, 1 - ($hoursSince / 168));
    }

    private function actionSuccessRate(): float
    {
        $objective = $this->opportunity->objective;
        $total = $objective->signals()
            ->where('signal_type', $this->recommendation_type)->count();
        $successful = $objective->signals()
            ->where('signal_type', $this->recommendation_type)
            ->get()
            ->filter(fn ($s) => in_array(
                $s->actions_and_results[-1]['result']['status'] ?? null,
                ['replied', 'won']
            ))->count();

        return $total > 0 ? $successful / $total : 0.5;
    }
}
