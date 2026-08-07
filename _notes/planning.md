# Implementation Roadmap

Building an autonomous prospecting engine in Laravel. This document guides the entire build.

---

## Core Flow

```
User Creates Objective
  ↓
AI Enriches (target profile, strategy)
  ↓
Define Search Terms (what/where/how to hunt)
  ↓
Automated Searches Run (on schedule)
  ↓
Signals Detected (from integrations, or search results, or manual entry)
  ↓
Opportunities Clustered (by )
  ↓
Recommendations Generated (next actions)
  ↓
User Reviews & Executes
  ↓
Track Results → Loop
```

  

---


### New/Updated Tables

**objectives** — add fields:
```php
- goal_target: int                    // "10 accounts"
- goal_type: enum                     // "new_accounts", "pipeline_value", "meetings"
- brand_voice: json                   // moved from enriched_data for easy access
- next_search_run_at: datetime (nullable)
```

**signals** — add fields:
```php
- deduplication_hash: string (unique, nullable)  // hash(source, company_name, signal_type, date_window)
- enrichment: json (nullable)                    // AI analysis of the signal
```

**opportunities** — add fields:
```php
- company_id: int (nullable)          // link to real company if you have CRM
- owner_id: int (nullable)            // team member leading this
- engagement_status: enum             // "new", "contacted", "replied", "meeting_scheduled", "won", "lost"
```

**recommendations** — add fields:
```php
- executed_at: datetime (nullable)
- executed_by: int (nullable)
- result_comment: text (nullable)
- auto_generated: boolean             // was this AI-generated or manual?
```

**New table: integrations**
```php
- id (PK)
- team_id (FK)
- provider: string                    // "linkedin", "news_api", "serper_api", "sendgrid", etc.
- is_enabled: boolean
- last_used_at: datetime (nullable)
- last_error_at: datetime (nullable)
- error_message: text (nullable)
- created_at, updated_at
```

**New table: integration_credentials** (encrypted)
```php
- id (PK)
- integration_id (FK)
- key_name: string                    // "api_key", "access_token"
- encrypted_value: text               // use Laravel's encryption
- created_at, updated_at
```

---

## Build Phases

### Phase 1: Foundation (Database + Models)

**What:** Create the database layer that everything else runs on.

#### ✅ Complete
- [x] Migrations for: teams, objectives, signals, opportunities, contacts, recommendations, activity_logs
  - All fields from planning.md integrated into create_ migrations (not separate add_fields migrations)
- [x] Models with relationships (Team → Objective → Signal/Opportunity → Contact/Recommendation)
- [x] JSON field casts (enriched_data, brand_voice, enrichment, actions_and_results, etc.)
- [x] Factories for testing all models (Objective, Signal, Opportunity, Contact, Recommendation, Team, ObjectiveActivityLog)
- [x] Model methods (actionSuccessRate, progress, qualityScore, calculateConfidenceScore, getPendingActions)
- [x] All schema fields added to create migrations:
  - objectives: goal_target, goal_type, brand_voice, next_search_run_at
  - signals: deduplication_hash (unique), enrichment
  - opportunities: company_id, owner_id, engagement_status
  - recommendations: executed_at, executed_by, result_comment, auto_generated
- [x] Model relationships updated (Opportunity.owner, Recommendation.executedBy)
- [x] All migrations ran successfully ✓

#### ⏳ Remaining
- [ ] Migrations for: integrations, integration_credentials
- [ ] Models: Integration, IntegrationCredential
- [ ] Factories: IntegrationFactory, IntegrationCredentialFactory
- [ ] Unit tests for model relationships

**Tests to write:**
- Objective can have many signals
- Signal can create opportunity with fit_score
- Opportunity can have many contacts
- Recommendation belongs to opportunity
- Contact.qualityScore() calculates correctly

**Files to create:**
- `database/migrations/2026_08_07_000005_create_integrations_table.php`
- `database/migrations/2026_08_07_000006_create_integration_credentials_table.php`
- `app/Models/Integration.php`
- `app/Models/IntegrationCredential.php`
- `database/factories/IntegrationFactory.php`
- `database/factories/IntegrationCredentialFactory.php`
- `tests/Unit/Models/ObjectiveTest.php`
- `tests/Unit/Models/SignalTest.php`
- `tests/Unit/Models/OpportunityTest.php`
- `tests/Unit/Models/RecommendationTest.php`
- `tests/Unit/Models/ContactTest.php`

---

### Phase 2: Objective Setup (User-Facing First Page)

**What:** Let users define what they're trying to find. AI fills in the blanks.

**Workflow:**
1. User navigates to create objective
2. Form asks: "What's your goal? Who's your ideal customer? What do you sell? What geographies?"
3. On submit: call `EnrichObjectiveService` (uses laravel/ai)
4. AI generates: target_context, strategy, brand_voice, search_terms with confidence thresholds
5. Review page shows what AI generated, user can edit
6. Save objective with enriched_data

**Components:**
- `ObjectiveController@create` — show wizard/form
- `ObjectiveController@store` — save objective
- `EnrichObjectiveService` — calls laravel/ai, generates enriched_data
- `resources/js/pages/Objectives/Create.vue` — Inertia form

**Key logic:**
```php
// EnrichObjectiveService
public function enrich(Objective $objective): Objective {
  $prompt = $this->buildPrompt($objective);
  $response = \Illuminate\Support\Facades\AI::client('claude')
    ->prompt($prompt)
    ->text();
  
  $enriched = json_decode($response, true);
  $objective->enriched_data = $enriched;
  $objective->save();
  
  return $objective;
}
```

**Tests:**
- Objective creation stores user input
- EnrichObjectiveService generates valid enriched_data
- Search_terms have required fields (source, query, signal_type, frequency, enabled)

**Files created:**
- `app/Http/Controllers/ObjectiveController.php`
- `app/Services/EnrichObjectiveService.php`
- `resources/js/pages/Objectives/Create.vue`
- `tests/Feature/ObjectiveCreationTest.php`

---

### Phase 3: Manual Signal Entry + Clustering

**What:** Before we automate API calls, prove signals → opportunities → contacts works.

**Manual flow:**
1. User (or admin) creates signal manually: company name, signal type, description
2. System deduplicates (checks if signal already exists)
3. System clusters by company into opportunity
4. System extracts contact info (or prompts user to add)
5. Shows in objective detail view

**Components:**
- `SignalController@store` — create signal
- `OpportunityClustering` service — when signal created, cluster into opportunity
- `ContactExtraction` service — extract contacts from signal metadata
- Deduplication logic (hash on source + company + signal_type + time window)

**Key logic:**
```php
// Signal creation triggers clustering
Signal::created(function ($signal) {
  ClusterSignalIntoOpportunityJob::dispatch($signal);
});

// ClusterSignalIntoOpportunityJob
public function handle(Signal $signal) {
  $opportunity = Opportunity::where('objective_id', $signal->objective_id)
    ->where('name', $signal->company_name)
    ->firstOrCreate([
      'name' => $signal->company_name,
      'entity_type' => 'company',
      'fit_score' => 0,
    ]);
  
  $opportunity->signal_ids = array_merge(
    $opportunity->signal_ids ?? [],
    [$signal->id]
  );
  
  $opportunity->fit_score = collect($this->relatedSignals($opportunity))
    ->average('relevance_score');
  
  $opportunity->save();
  
  // Extract contacts from signal metadata
  ExtractContactsFromSignal::dispatch($signal, $opportunity);
}
```

**Tests:**
- Signal deduplication prevents duplicates
- Signal creates/merges into opportunity
- Opportunity fit_score updates when signal added
- Contact extraction works from signal metadata

**Files created:**
- `app/Services/OpportunityClustering.php`
- `app/Services/ContactExtraction.php`
- `app/Jobs/ClusterSignalIntoOpportunityJob.php`
- `resources/js/pages/Objectives/Show.vue` (detail view)
- `tests/Feature/SignalClustering*.php`

---

### Phase 4: Integration Services (API Abstraction)

**What:** Make it easy to add/swap integrations without changing core logic.

**Architecture:**
```php
interface SignalDetectionService {
  public function search(Objective $objective): Collection;
}

class LinkedInSearchService implements SignalDetectionService {
  public function search(Objective $objective): Collection {
    // call LinkedIn API
    // return collection of raw results
  }
  
  protected function parseToSignal($result): Signal {
    // convert API result to Signal
  }
}

class NewsApiSearchService implements SignalDetectionService {
  // similar
}

class SerperApiSearchService implements SignalDetectionService {
  // similar
}
```

**Key points:**
- Each service returns uniform Signal format
- Error handling: log, skip, don't crash the entire search
- Rate limiting & retry logic built in
- Credentials stored encrypted in integration_credentials table

**Components:**
- `app/Services/Integrations/SignalDetectionService` (interface)
- `app/Services/Integrations/LinkedInSearchService`
- `app/Services/Integrations/NewsApiSearchService`
- `app/Services/Integrations/SerperApiSearchService`
- `SignalDetectionFactory` — instantiate correct service
- Error handling + logging

**Tests:**
- Each service returns correct signal format
- Invalid API response doesn't crash
- Deduplication hash is consistent

**Files created:**
- `app/Services/Integrations/SignalDetectionService.php` (interface)
- `app/Services/Integrations/LinkedInSearchService.php`
- `app/Services/Integrations/NewsApiSearchService.php`
- `app/Services/Integrations/SerperApiSearchService.php`
- `app/Services/SignalDetectionFactory.php`
- `tests/Unit/Integrations/*Test.php`

---

### Phase 5: Scheduled Search Execution

**What:** Run searches automatically on schedule defined in search_terms.

**Workflow:**
1. `DispatchObjectiveSearchesCommand` runs (e.g., every minute via cron)
2. Finds all active objectives with next_search_run_at <= now
3. For each search_term in enriched_data.search_terms:
   - If enabled && frequency allows (daily, hourly, weekly)
   - Dispatch SearchObjectiveJob
4. Job instantiates correct IntegrationService
5. Service searches, returns signals
6. For each signal: deduplicate, create or skip
7. Update next_search_run_at

**Key logic:**
```php
// DispatchObjectiveSearchesCommand (runs every minute)
foreach (Objective::active()->where('next_search_run_at', '<=', now())->get() as $objective) {
  foreach ($objective->enriched_data['search_terms'] as $searchTerm) {
    if (!$searchTerm['enabled']) continue;
    
    $lastRun = $objective->last_search_runs[$searchTerm['id']] ?? null;
    if (!$this->shouldRun($searchTerm['frequency'], $lastRun)) continue;
    
    SearchObjectiveJob::dispatch($objective, $searchTerm);
  }
}

// SearchObjectiveJob
public function handle(Objective $objective, array $searchTerm) {
  try {
    $service = SignalDetectionFactory::make($searchTerm['source']);
    $results = $service->search($objective, $searchTerm);
    
    foreach ($results as $result) {
      $signal = Signal::create([
        'objective_id' => $objective->id,
        'signal_type' => $searchTerm['signal_type'],
        'source' => $searchTerm['source'],
        'company_name' => $result['company'],
        'description' => $result['description'],
        'relevance_score' => $result['confidence'] ?? 0.5,
        'detected_at' => now(),
        'deduplication_hash' => $this->hash($result),
      ]);
      
      ClusterSignalIntoOpportunityJob::dispatch($signal);
    }
  } catch (Exception $e) {
    ActivityLog::create([
      'objective_id' => $objective->id,
      'action_type' => 'search_failed',
      'status' => 'failed',
      'details' => ['error' => $e->getMessage()],
    ]);
  }
}
```

**Tests:**
- Command finds objectives due for search
- Job respects search frequency (don't run hourly searches twice in 1 hour)
- Signals created with correct type/source
- Deduplication prevents duplicate signals

**Files created:**
- `app/Console/Commands/DispatchObjectiveSearchesCommand.php`
- `app/Jobs/SearchObjectiveJob.php`
- `tests/Feature/SearchExecution*.php`

---

### Phase 6: Recommendation Generation

**What:** AI looks at an opportunity and suggests next actions.

**Workflow:**
1. When opportunity created/updated, dispatch `GenerateRecommendationsJob`
2. Job pulls opportunity + related signals + team brand_voice
3. Calls laravel/ai with context
4. AI suggests: email, call, meeting request
5. Creates Recommendation records with confidence_score
6. Show in UI for user to accept/execute

**Key logic:**
```php
// GenerateRecommendationsJob
public function handle(Opportunity $opportunity) {
  $signals = $opportunity->signals(); // via signal_ids
  $objective = $opportunity->objective;
  $brandVoice = $objective->brand_voice;
  
  $prompt = $this->buildPrompt($opportunity, $signals, $brandVoice);
  $response = AI::client('claude')->prompt($prompt)->text();
  $recommendations = json_decode($response, true);
  
  foreach ($recommendations as $rec) {
    $recommendation = $opportunity->recommendations()->create([
      'recommendation_type' => $rec['type'], // email, call, etc.
      'content' => $rec['content'],
      'confidence_score' => $this->calculateConfidence($opportunity, $rec),
      'auto_generated' => true,
    ]);
    
    ActivityLog::create([
      'objective_id' => $objective->id,
      'action_type' => 'recommendation_generated',
      'details' => ['recommendation_id' => $recommendation->id],
    ]);
  }
}

// Confidence calculation
private function calculateConfidence(Opportunity $opp, array $rec): float {
  $fitScore = $opp->fit_score;
  $signalRecency = $this->signalRecencyScore($opp);
  $actionSuccessRate = $opp->objective->actionSuccessRate($rec['type']);
  $contactQuality = $opp->primaryContact()?->qualityScore() ?? 0.5;
  
  return ($fitScore * 0.4) + ($signalRecency * 0.3) + 
         ($actionSuccessRate * 0.2) + ($contactQuality * 0.1);
}
```

**Tests:**
- Recommendation generated when opportunity created
- Confidence score calculated correctly
- AI prompt includes brand_voice (check in mock/stub)

**Files created:**
- `app/Jobs/GenerateRecommendationsJob.php`
- `app/Services/RecommendationCalculator.php`
- `tests/Feature/Recommendation*.php`

---

### Phase 7: Email Sending + Result Tracking

**What:** Execute recommendations (send emails), track opens/replies.

**Start simple:**
- User clicks "Execute" on recommendation
- System sends email
- User manually marks result (opened, replied, no response) via UI
- Later: automate with email provider webhooks

**Components:**
- `RecommendationController@execute` — send email, create action record
- Email templates (use view + mailable)
- Result tracking form (manual input)
- Webhook handlers (future phase)

**Key logic:**
```php
// RecommendationController@execute
public function execute(Recommendation $recommendation) {
  $contact = $recommendation->opportunity->primaryContact();
  $team = $recommendation->opportunity->objective->team;
  
  $mailable = new ProspectEmailMailable($recommendation, $contact, $team);
  
  Mail::to($contact->email)
    ->queue($mailable);
  
  $recommendation->actions_and_results[] = [
    'action' => [
      'action_type' => 'email',
      'sent_at' => now(),
      'recipient' => $contact->email,
      'subject' => $recommendation->content['subject'],
      'body' => $recommendation->content['body'],
    ],
    // result is null until user marks it
  ];
  $recommendation->save();
  
  $recommendation->executed_at = now();
  $recommendation->executed_by = auth()->id();
  $recommendation->save();
  
  ActivityLog::create([
    'objective_id' => $recommendation->opportunity->objective_id,
    'action_type' => 'email_sent',
    'details' => ['recommendation_id' => $recommendation->id, 'contact' => $contact->email],
  ]);
}

// Track result (manual input for now)
public function trackResult(Request $request, Recommendation $recommendation) {
  $actions = $recommendation->actions_and_results;
  $lastAction = end($actions);
  
  $lastAction['result'] = [
    'status' => $request->status, // opened, clicked, replied, no_response
    'status_at' => now(),
    'note' => $request->note,
  ];
  
  $recommendation->actions_and_results = $actions;
  $recommendation->save();
  
  // Update opportunity status if needed
  if ($request->status === 'replied') {
    $recommendation->opportunity->engagement_status = 'replied';
    $recommendation->opportunity->save();
  }
}
```

**Tests:**
- Email sent when recommendation executed
- Action recorded in actions_and_results
- Result tracking updates opportunity status
- Activity log captures email sent

**Files created:**
- `app/Http/Controllers/RecommendationController.php`
- `app/Mail/ProspectEmailMailable.php`
- `resources/js/components/RecommendationCard.vue`
- `tests/Feature/EmailExecution*.php`

---

### Phase 8: Frontend Dashboard

**What:** User sees the entire funnel, can execute actions, track progress.

**Pages:**
- `objectives/index` — list all objectives, create new
- `objectives/{id}` — detail view: signals, opportunities, recommendations, activity log
- `opportunities/{id}` — detail view: contacts, signals, recommendations, actions taken

**Components:**
- ObjectiveHeader (name, goal_target, progress bar)
- SignalsList (recent signals with relevance scores)
- OpportunitiesList (table: company, fit_score, status, primary contact, last activity)
- RecommendationsCards (cards: suggested action, confidence, accept/execute button)
- ActivityLog (timeline: what signals found, what actions taken, results)
- TrackResultModal (form to mark email opened/replied/etc.)

**Key features:**
- Real-time filter by status (new, contacted, replied, won)
- Sort by fit_score, last_activity
- Search by company name
- Approve/execute recommendations from UI
- Manual entry of results
- Bulk actions (mark as won, dismiss)

**Files created:**
- `resources/js/pages/Objectives/Index.vue`
- `resources/js/pages/Objectives/Show.vue`
- `resources/js/pages/Opportunities/Show.vue`
- `resources/js/components/SignalsList.vue`
- `resources/js/components/OpportunitiesList.vue`
- `resources/js/components/RecommendationCard.vue`
- `resources/js/components/ActivityTimeline.vue`

---

## Schema Improvements Summary

### Objective
```php
- goal_target: int
- goal_type: enum
- brand_voice: json
- next_search_run_at: datetime
```

### Signal
```php
- deduplication_hash: string (unique, nullable)
- enrichment: json (nullable)
```

### Opportunity
```php
- company_id: int (nullable)
- owner_id: int (nullable)
- engagement_status: enum
```

### Recommendation
```php
- executed_at: datetime (nullable)
- executed_by: int (nullable)
- result_comment: text (nullable)
- auto_generated: boolean
```

### New Tables
- integrations (provider, enabled, last_used_at, errors)
- integration_credentials (encrypted API keys)

---

## Key Decisions

**Before starting Phase 4 (integrations), decide:**

1. **Email tracking approach?**
   - [ ] Manual input only (user marks opened/replied)
   - [ ] Email provider webhook (SendGrid, Mailgun)
   - [ ] Pixel tracking (inject img tag, listen for clicks)

2. **First integrations to build?**
   - [ ] News API (easiest, free tier available)
   - [ ] Serper API (web search, similar to Google)
   - [ ] LinkedIn API (hardest, requires auth flow)

3. **Approval flow?**
   - [ ] All recommendations need user approval before sending
   - [ ] Auto-send if confidence > threshold
   - [ ] User can toggle per recommendation type

4. **Integration credentials?**
   - [ ] Store in database (encrypted)
   - [ ] Store in .env (simpler, less flexible)

---

## Testing Strategy

**Unit Tests:**
- Model relationships
- Service logic (enrichment, clustering, scoring)
- Helper functions (hash, scoring calculations)

**Feature Tests:**
- Objective creation → enrichment
- Signal creation → clustering → opportunity
- Recommendation generation
- Email execution → result tracking
- Search command finds due objectives
- Deduplication prevents duplicates

**How to run:**
```bash
php artisan test --filter=Objective
php artisan test --filter=Signal
php artisan test Feature/ObjectiveCreation*
```

---

## Success Metrics

✅ User creates objective with goal  
✅ AI enriches with target profile + search strategy  
✅ Signals detected from integrations (or manually)  
✅ Opportunities clustered by company  
✅ Recommendations generated with confidence scores  
✅ User executes recommendations from UI  
✅ Results tracked (opened, replied, won)  
✅ Activity log shows every step  
✅ Dashboard shows funnel progress  

---

## Notes

- Keep each phase standalone and testable
- Don't build Phase N until Phase N-1 works
- Start with manual signal entry before API automation
- Stub AI calls in tests (use mock responses)
- Keep search_terms flexible for future integrations
- Activity logs are critical for transparency
