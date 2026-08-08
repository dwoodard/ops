# Session Tasks

## Current Focus: Objective Create Form → Save to DB

**Goal:** User can create an Objective from a form and see it saved. No enrichment yet (stub it), just prove the flow works.

**Workflow:** Schema → Artisan scaffolding → Routes → Vue → End-to-end test

**Steps:**
1. [x] Review schema (Objective fields)
2. [x] Scaffold files via artisan (ObjectiveController, StoreObjectiveRequest)
   - `app/Http/Controllers/Objectives/ObjectiveController.php`
   - `app/Http/Requests/StoreObjectiveRequest.php`
3. [x] Add routes to routes/web.php (under team prefix)
4. [ ] Build Create.vue form (goal, goal_target, goal_type, end_date)
5. [ ] Wire controller (create, store actions)
6. [ ] Build Show.vue (display saved Objective)
7. [ ] Test in browser (create → submit → show)

---

# Building Phase 1: Foundation (Database + Models)

## ✅ Completed

- [x] Analyzed existing schema and models
- [x] Created planning.md with complete roadmap
- [x] Updated create_ migrations to include all fields from planning.md:
  - objectives: goal_target, goal_type, brand_voice, next_search_run_at
  - signals: deduplication_hash, enrichment
  - opportunities: company_id, owner_id, engagement_status
  - recommendations: executed_at, executed_by, result_comment, auto_generated
- [x] Updated models with new casts:
  - Objective, Signal, Opportunity, Recommendation
- [x] Added relationships:
  - Opportunity.owner(), Recommendation.executedBy()
- [x] Database migrations reset and re-run with updated schemas

## 🚧 In Progress

- [x] Planned OpsEngine architecture (see /Users/dustin/.claude/plans/i-really-want-to-ticklish-sky.md)
  - Part A: Setup (objective creation + AI enrichment)
  - Part B: Autonomous Loop (searches → signals → opportunities → recommendations → send)
  - Reusing existing Team onboarding pattern (Agent + Action)
  - Using laravel/ai's built-in Approvals system for human-in-the-loop

## ⏳ TODO (Part A Build — Next)

Build order per approved plan:
- [ ] `app/OpsEngine/Agents/EnrichObjectiveAgent.php` (mirrors OnboardTeamAgent)
- [ ] `app/OpsEngine/Actions/EnrichObjective.php` (mirrors EnrichTeamProfile)
- [ ] `app/Http/Controllers/Objectives/ObjectiveController.php` (create, store, show, edit/update)
- [ ] `resources/js/pages/objectives/Create.vue` (form)
- [ ] `resources/js/pages/objectives/Show.vue` (display + edit search_terms)
- [ ] Routes in routes/web.php (nested under team prefix)
- [ ] `tests/Feature/Objectives/ObjectiveCreationTest.php` (fakes + assertions)

## 📋 Part B Build (Autonomous Loop) — Later

- Phase 2: Objective Setup (User-Facing First Page)
- Phase 3: Manual Signal Entry + Clustering
- Phase 4: Integration Services (API Abstraction)
- Phase 5: Scheduled Search Execution
- Phase 6: Recommendation Generation
- Phase 7: Email Sending + Result Tracking
- Phase 8: Frontend Dashboard

---

## Notes

- Schema is now cleaner with all fields in create_ migrations (no separate add_fields)
- All migrations ran successfully and database is current
- Models have proper relationships and casts in place
- Next focus: Complete Phase 1 integrations, then write tests
