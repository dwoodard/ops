# Schema & Models

## Overview
Database schema and Eloquent model planning for the application.

## Tables

### User (users)
- id (PK)
- name (string)
- email (string, unique)
- password (string, hashed)
- email_verified_at (datetime, nullable)
- two_factor_secret (text, encrypted, nullable)
- two_factor_recovery_codes (text, encrypted, nullable)
- two_factor_confirmed_at (datetime, nullable)
- remember_token (string)
- created_at (datetime)
- updated_at (datetime)

**Relationships:**
- belongsToMany: Team (via Membership/team_members)
- can_own: Team

### Team (teams)
Uses Fortify's team system. See [Fortify Team Documentation](https://laravel.com/docs/fortify#teams).
- id (PK)
- name (string)
- slug (string, unique)
- is_personal (boolean)
- created_at (datetime)
- updated_at (datetime)
- deleted_at (datetime, nullable, soft delete)

**Relationships:**
- belongsToMany: User (via Membership)
- hasMany: TeamInvitation
- hasMany: Objective

### Membership (team_members)
Represents a user's membership in a team with a role.
- id (PK)
- team_id (FK → Team, cascade delete)
- user_id (FK → User, cascade delete)
- role (string: 'owner', 'admin', 'member')
- created_at (datetime)
- updated_at (datetime)

**Relationships:**
- belongs_to: Team
- belongs_to: User

### Objective (objectives)
Scoped to a Team. The owner must be a member of the team.
- id (PK)
- team_id (FK → Team, cascade delete)
- owner_id (FK → User)
- name (string)
- goal (text)
- enriched_data (json) - AI + integrations populate this
  - target_context (industries, company_size, geography, decision_makers)
  - services_positioned (array of services)
  - strategy (array of action items)
  - search_terms (array of search configurations)
  - integrations_used (array: crm, linkedin, web, etc.)
- end_date (datetime)
- status (enum: active, paused, completed, overdue, cancelled)
- created_at (datetime)
- updated_at (datetime)

**Relationships:**
- belongs_to: Team
- belongs_to: User (owner)
- has_many: Signal
- has_many: Opportunity

**Methods:**
```php
public function actionSuccessRate(string $actionType): float {
  $total = $this->signals()
    ->get()
    ->sum(fn($s) => count($s->actions_and_results));
  
  $successful = $this->signals()
    ->get()
    ->sum(function($signal) use ($actionType) {
      return collect($signal->actions_and_results)
        ->filter(fn($ar) => $ar['action']['action_type'] === $actionType)
        ->filter(fn($ar) => in_array($ar['result']['status'] ?? null, ['replied', 'won']))
        ->count();
    });
  
  return $total > 0 ? $successful / $total : 0;
}

public function progress(): array {
  $total = $this->opportunities()->count();
  $won = $this->opportunities()
    ->whereJsonContains('overall_status', 'won')->count();
  
  return [
    'target' => 10, // from goal or enriched_data
    'current' => $won,
    'in_progress' => $total - $won,
    'percentage' => $total > 0 ? ($won / $total) * 100 : 0,
  ];
}
```

### Signal (signals)
- id (PK)
- objective_id (FK → Objective, cascade delete)
- signal_type (string: new_hire, product_launch, expansion, event, etc.)
- source (string: linkedin, crunchbase, event, news, etc.)
- company_name (string)
- description (text, nullable)
- url (string, nullable) - link to where signal was found
- detected_at (datetime)
- relevance_score (float, 0-1)
- metadata (json, nullable) - additional signal data

- actions_and_results (json array) - complete action/result history
  [{
    "action": {
      "action_type": "email|call|message|meeting|proposal",
      "sent_at": datetime,
      "recipient": string,
      "details": object
    },
    "result": {
      "status": "sent|opened|clicked|replied|meeting_scheduled|meeting_completed|won|lost|no_response|in_progress",
      "opened_at": datetime,
      "replied_at": datetime,
      "meeting_date": datetime,
      "deal_value": float,
      "contract_signed": boolean
    }
  }]

- created_at (datetime)
- updated_at (datetime)

**Relationships:**
- belongs_to: Objective

**Methods:**
```php
public function getPendingActions() {
  return collect($this->actions_and_results)
    ->filter(fn($ar) => !isset($ar['result']))
    ->values()
    ->all();
}

public function scopeWithPendingActions($query) {
  return $query->where(function($q) {
    $q->whereNull('actions_and_results->*.result')
      ->orWhere('actions_and_results', 'like', '%"result": null%');
  });
}

public function hasUnresolvedActions(): bool {
  return count($this->getPendingActions()) > 0;
}
```

### Opportunity (opportunities)
- id (PK)
- objective_id (FK → Objective, cascade delete)
- name (string) - Generic name for the target
- entity_type (enum: company, person, partnership, event, website, community, other)
- description (text, nullable) - Additional context about the opportunity
- fit_score (float, 0-1) - average relevance of clustered signals
- signal_ids (json array) - related signal IDs
- overall_status (string: detected, contacted, in_progress, won, lost) - best result from signals
- total_deal_value (float, nullable) - sum of winning signals
- last_signal_updated_at (datetime)
- created_at (datetime)
- updated_at (datetime)

**Relationships:**
- belongs_to: Objective

### Contact (contacts)
- id (PK)
- opportunity_id (FK → Opportunity, cascade delete)
- name (string)
- title (string)
- email (string)
- phone (string, nullable)
- source (string: linkedin, crunchbase, website, etc.)
- is_direct (boolean) - direct vs generic contact
- is_decision_maker (boolean)
- verified (boolean)
- created_at (datetime)
- updated_at (datetime)

**Relationships:**
- belongs_to: Opportunity

**Methods:**
```php
public function qualityScore(): float {
  $score = 0;
  $score += $this->is_direct ? 0.3 : 0.1;
  $score += $this->is_decision_maker ? 0.4 : 0.2;
  $score += $this->verified ? 0.3 : 0.2;
  return $score;
}
```

### Recommendation (recommendations)
- id (PK)
- opportunity_id (FK → Opportunity, cascade delete)
- recommendation_type (string: email, call, meeting_request, research)
- content (json) - template/talking points/subject + body
- confidence_score (float, 0-1)
- score_breakdown (json, nullable) - how score was calculated
- status (enum: pending, accepted, rejected, executed)
- created_at (datetime)
- updated_at (datetime)

**Relationships:**
- belongs_to: Opportunity

**Methods:**
```php
public function calculateConfidenceScore(): float {
  $opportunity = $this->opportunity;
  $fitScore = $opportunity->fit_score;
  $signalRecency = $this->signalRecencyScore();
  $actionRate = $this->actionSuccessRate();
  $contactQuality = $opportunity->primaryContact()?->qualityScore() ?? 0.5;
  
  return ($fitScore * 0.4) + ($signalRecency * 0.3) + 
         ($actionRate * 0.2) + ($contactQuality * 0.1);
}

private function signalRecencyScore(): float {
  $newestSignal = $this->opportunity->signals()->latest('detected_at')->first();
  $hoursSince = now()->diffInHours($newestSignal->detected_at);
  return max(0, 1 - ($hoursSince / 168)); // decays over week
}

private function actionSuccessRate(): float {
  $objective = $this->opportunity->objective;
  $total = $objective->signals()
    ->where('signal_type', $this->recommendation_type)->count();
  $successful = $objective->signals()
    ->where('signal_type', $this->recommendation_type)
    ->get()
    ->filter(fn($s) => in_array(
      $s->actions_and_results[-1]['result']['status'] ?? null,
      ['replied', 'won']
    ))->count();
  return $total > 0 ? $successful / $total : 0.5;
}
```

### ObjectiveActivityLog (objective_activity_logs)
- id (PK)
- objective_id (FK → Objective, cascade delete)
- action_type (enum: signal_detected, opportunity_created, integration_queried, learning_updated, proposal_made)
- description (text)
- details (json)
- status (enum: success, failed, pending_review)
- timestamp (datetime)
- created_at (datetime)

**Relationships:**
- belongs_to: Objective

---

## Enums

### ObjectiveStatus
- `active` - Executing, looping
- `paused` - User paused it
- `completed` - User marked it done
- `overdue` - Passed end_date without completion
- `cancelled` - User cancelled it

### Other System Tables
- password_reset_tokens (email, token, created_at)
- sessions (Laravel session handler)
- cache (cache table)
- jobs (job queue)
- passkeys (Fortify passkey support)

---

## Notes
- Add notes here as schema evolves
