# Phase 1: Gather User Info (Objective Creation & Enrichment)

## Core Flow Progress

```
User Creates Objective              👈 Phase 1 Starts Here
  ↓
AI Enriches (target profile)        👈 Phase 1 + Human-in-Loop
  ↓
Define Search Terms (what to hunt)  👈 Phase 1 Output (enriched_data.search_terms)
  ↓
Automated Searches Run (on schedule) ⏳ Phase 2
  ↓
Signals Detected (from integrations) ⏳ Phase 2+
  ↓
Opportunities Clustered (by company) ⏳ Phase 2+
  ↓
Recommendations Generated           ⏳ Phase 2+
  ↓
User Reviews & Executes             ⏳ Phase 2+
  ↓
Track Results → Loop back to Phase 2 (autonomous, human approval for key actions)
```

---

## What Phase 1 Creates

When user completes Phase 1, they'll have created an **Objective** record with:

```json
{
  "id": 1,
  "team_id": 1,
  "owner_id": 5,
  "name": "Land 10 Retail Accounts",
  "goal": "Find and secure 10 retail partners in beauty who need brand strategy",
  "goal_target": 10,
  "goal_type": "new_accounts",
  "enriched_data": {
    "target_context": {
      "industries": ["Natural & Clean Beauty", "Specialty Retail"],
      "company_size": "$5M-$250M revenue",
      "decision_makers": ["Buyer", "Merchant", "CMO"]
    },
    "services_positioned": ["Brand Strategy", "Identity Design"],
    "strategy": [
      "Monitor LinkedIn for new hires",
      "Track product launches",
      "Watch for conference mentions"
    ],
    "search_terms": [
      {
        "id": "linkedin_new_hire",
        "source": "linkedin",
        "query": {...},
        "signal_type": "new_hire",
        "frequency": "daily",
        "confidence_threshold": 0.75,
        "enabled": true
      },
      {
        "id": "news_product_launch",
        "source": "news_api",
        "query": {...},
        "signal_type": "product_launch",
        "frequency": "hourly",
        "enabled": true
      }
    ]
  },
  "brand_voice": {
    "tone": "professional, consultative",
    "value_props": ["Brand strategy accelerates launches", "Design that sells"],
    "case_studies": ["Client A story", "Client B story"]
  },
  "end_date": "2026-08-18",
  "next_search_run_at": "2026-08-07T10:00:00",
  "status": "active",
  "created_at": "2026-08-07T...",
  "updated_at": "2026-08-07T..."
}
```

---

## Phase 1 Workflow (Step-by-Step)

### Step 1: User Inputs (Form)
User fills out objective form:
- What's your goal? (text)
- Who's your ideal customer? (text)
- What do you sell/offer? (text)
- What geographies? (select)
- Any case studies to reference? (optional)

**Database:** Objective created with basic info

### Step 2: AI Enrichment (laravel/ai with human-in-loop)
System calls `EnrichObjectiveService`:
1. Takes user input
2. Calls Claude via laravel/ai
3. Claude generates:
   - target_context (industries, company size, decision makers)
   - strategy (what to monitor for)
   - search_terms (specific searches to run)
   - brand_voice (tone, value props)

**Database:** enriched_data and brand_voice populated

### Step 3: Review & Approve (Human-in-Loop)
User sees AI-generated content and can:
- Accept as-is
- Edit target_context
- Modify search strategies
- Adjust confidence thresholds
- Add/remove search sources

**Database:** User edits saved to enriched_data

### Step 4: Objective Ready
System:
- Sets `status = 'active'`
- Sets `next_search_run_at = now()`
- Returns to dashboard

**Next:** Phase 2 starts autonomous loop with this objective

---

## Phase 1 Components to Build

### Backend
```
app/Http/Controllers/ObjectiveController.php
  - create()     → show empty form
  - store()      → save initial objective
  - enrich()     → call AI enrichment
  - review()     → show enrichment review page
  - update()     → save user edits
  - show()       → view objective details

app/Services/EnrichObjectiveService.php
  - enrich(Objective): Objective
    - buildPrompt()
    - parseAiResponse()
    - validateSearchTerms()
    
app/Traits/CanEnrichWithAI.php
  - For human-in-loop patterns (approve, edit, retry)
```

### Frontend
```
resources/js/pages/Objectives/Create.vue
  → Step 1: Form for user input
  → Shows: goal, customer, offerings, geographies, case studies

resources/js/pages/Objectives/Enrich.vue
  → Step 2: AI enrichment in progress
  → Shows: loading, AI thinking, streaming response

resources/js/pages/Objectives/Review.vue
  → Step 3: Review AI output
  → Shows: target_context, strategy, search_terms
  → Allows: edit, approve, regenerate

resources/js/pages/Objectives/Show.vue
  → Active objective view
  → Shows: current settings, search status, next run time
```

### Database (Already Complete ✓)
- objectives table with all fields
- Models with relationships
- Factories for testing

---

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────┐
│ User Input (Create.vue)                                 │
│  - goal, customer, offerings, geographies               │
└──────────────────────┬──────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────┐
│ ObjectiveController@store()                             │
│  - Save Objective (basic fields)                         │
│  - Dispatch EnrichObjectiveJob                          │
└──────────────────────┬──────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────┐
│ EnrichObjectiveService@enrich()                         │
│  - Call Claude via laravel/ai                           │
│  - Parse response → enriched_data                       │
│  - Validate search_terms                                │
│  - Save to objective.enriched_data                      │
└──────────────────────┬──────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────┐
│ Review.vue (Human-in-Loop)                              │
│  - Show AI output                                       │
│  - Allow edits                                          │
│  - Approve or regenerate                                │
└──────────────────────┬──────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────┐
│ ObjectiveController@update()                            │
│  - Save user edits                                      │
│  - Set status = 'active'                                │
│  - Set next_search_run_at = now()                       │
└──────────────────────┬──────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────┐
│ Objective Ready for Phase 2                             │
│  - search_terms defined and approved                    │
│  - brand_voice captured                                 │
│  - Ready for autonomous searches                        │
└─────────────────────────────────────────────────────────┘
```

---

## Success Criteria for Phase 1

✅ User can create objective with goal info  
✅ AI enriches objective with target_context + strategy + search_terms  
✅ User can review and edit AI output  
✅ Objective saves with enriched_data ready for Phase 2  
✅ search_terms are valid and enabled  
✅ brand_voice captures company positioning  
✅ Unit & feature tests pass  

---

## Files to Create for Phase 1

**Controllers:**
- `app/Http/Controllers/ObjectiveController.php`

**Services:**
- `app/Services/EnrichObjectiveService.php`
- `app/Traits/CanEnrichWithAI.php` (for human-in-loop patterns)

**Jobs:**
- `app/Jobs/EnrichObjectiveJob.php` (if async)

**Vue Components:**
- `resources/js/pages/Objectives/Create.vue`
- `resources/js/pages/Objectives/Enrich.vue`
- `resources/js/pages/Objectives/Review.vue`
- `resources/js/pages/Objectives/Show.vue`
- `resources/js/components/SearchTermsList.vue` (reusable)
- `resources/js/components/TargetContextEditor.vue` (reusable)

**Tests:**
- `tests/Feature/ObjectiveCreation*.php`
- `tests/Feature/ObjectiveEnrichment*.php`
- `tests/Unit/Services/EnrichObjectiveService*.php`

---

## What Phase 1 Sets Up for Phase 2+

Once Phase 1 is complete, Phase 2 reads from objective:
- `enriched_data.search_terms` → tells SearchObjectiveJob what to search for
- `enriched_data.target_context` → tells AI how to score signals
- `brand_voice` → tells AI how to write recommendations
- `next_search_run_at` → scheduled command triggers searches
- `status: 'active'` → system knows to process this objective

---

## Implementation Order

1. **ObjectiveController** — basic CRUD
2. **EnrichObjectiveService** — AI integration + parsing
3. **Create.vue** — form for user input
4. **Enrich.vue** — show AI thinking (streaming)
5. **Review.vue** — edit + approve
6. **Show.vue** — active objective view
7. **Tests** — feature + unit tests

Start with controller, then service, then frontend. Test as you go.

---

## Notes

- Phase 1 is **synchronous** (user submits, waits for AI, reviews, approves)
- Phase 2+ is **autonomous** (scheduled searches, automatic signal detection, automatic recommendations)
- Human approval needed at: initial creation, enrichment review, before large actions (send email campaigns)
- laravel/ai SDK handles streaming, human-in-loop patterns out of the box
