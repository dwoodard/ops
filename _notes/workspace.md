# Complete Workspace Example

Full nested structure showing Workspace → Members → Objective → Signals → Opportunities → Activity Logs

```json
{
  "workspace": {
    "id": 1,
    "owner_id": 5,
    "name": "Emerald Bay Collective",
    "created_at": "2026-08-01T10:00:00Z",
    "updated_at": "2026-08-05T14:22:00Z",

    "members": [
      {
        "id": 1,
        "user_id": 6,
        "name": "Chance Nelson",
        "email": "chance@emeraldbay.com",
        "role": "workspace_member",
        "created_at": "2026-08-02T14:00:00Z"
      },
      {
        "id": 2,
        "user_id": 5,
        "name": "Dustin Woodard",
        "email": "dustin@emeraldbay.com",
        "role": "workspace_owner",
        "created_at": "2026-08-01T10:00:00Z"
      }
    ],

    "objectives": [
      {
        "id": 1,
        "workspace_id": 1,
        "owner_id": 5,
        "name": "Land 10 Retail Accounts",
        "goal": "Find and secure 10 retail partners in the beauty space who need brand strategy work",
        "enriched_data": {
          "target_context": {
            "industries": ["Natural & Clean Beauty", "Specialty Retail"],
            "company_size": "$5M-$250M revenue",
            "growth_stage": ["Scaling", "Launching", "Rebranding"],
            "geography": ["United States"],
            "decision_makers": ["Buyer", "Merchant", "Category Manager", "VP Marketing"]
          },
          "services_positioned": [
            "Brand Strategy",
            "Identity Design",
            "Website Design",
            "Brand Positioning"
          ],
          "strategy": [
            "Monitor LinkedIn for new hires in marketing/product roles",
            "Track product launch announcements",
            "Watch for companies at beauty/retail conferences",
            "Search for CEO/founder posts about expansion"
          ],
          "search_teams": [
            {
              "id": "linkedin_search_1",
              "source": "linkedin",
              "search_query": {
                "job_titles": ["VP Marketing", "VP Product", "CMO"],
                "industries": ["Beauty", "Specialty Retail"],
                "locations": ["United States"],
                "change_type": "job_change"
              },
              "signal_triggers": [
                "new_hire_in_target_role",
                "promoted_to_target_role"
              ],
              "executed_by": "ai_automation",
              "frequency": "daily",
              "confidence_threshold": 0.75
            },
            {
              "id": "news_search_1",
              "source": "news_api",
              "search_query": {
                "keywords": ["product launch", "expansion", "new line"],
                "industries": ["Beauty", "Retail"]
              },
              "signal_triggers": [
                "product_launch",
                "market_expansion"
              ],
              "executed_by": "ai_automation",
              "frequency": "hourly",
              "confidence_threshold": 0.80
            }
          ],
          "integrations_used": ["linkedin", "news_api", "event_tracking"]
        },
        "end_date": "2026-08-18T00:00:00Z",
        "status": "active",
        "created_at": "2026-08-04T10:30:00Z",
        "updated_at": "2026-08-05T14:22:00Z",

        "signals": [
          {
            "id": 101,
            "objective_id": 1,
            "signal_type": "new_hire",
            "source": "linkedin",
            "company_name": "Whole Foods Market",
            "description": "Whole Foods Market hired new VP of Product Development for beauty category",
            "url": "https://linkedin.com/company/wholefoods/posts/987654321",
            "detected_at": "2026-08-05T08:15:00Z",
            "relevance_score": 0.92,
            "metadata": {
              "person_name": "Sarah Johnson",
              "previous_role": "VP at Sephora",
              "connection_degree": 2
            },
            "actions_and_results": [
              {
                "action": {
                  "action_type": "email",
                  "sent_at": "2026-08-05T09:00:00Z",
                  "recipient": "sarah@wholefoods.com",
                  "subject": "Brand Strategy for Your Beauty Launch",
                  "body": "Hi Sarah, we saw you're expanding..."
                },
                "result": {
                  "status": "opened",
                  "opened_at": "2026-08-05T10:15:00Z",
                  "opened_count": 3,
                  "clicked": false
                }
              },
              {
                "action": {
                  "action_type": "followup_email",
                  "sent_at": "2026-08-10T14:00:00Z",
                  "recipient": "sarah@wholefoods.com",
                  "subject": "Quick follow-up: Brand Strategy for Beauty"
                },
                "result": {
                  "status": "replied",
                  "replied_at": "2026-08-10T16:30:00Z",
                  "reply": "This is interesting. Let's talk next week."
                }
              }
            ],
            "created_at": "2026-08-05T08:15:00Z",
            "updated_at": "2026-08-10T16:30:00Z"
          },
          {
            "id": 102,
            "objective_id": 1,
            "signal_type": "product_launch",
            "source": "news_api",
            "company_name": "Whole Foods Market",
            "description": "Whole Foods launches expanded clean beauty product line",
            "url": "https://news.wholewoodsmarket.com/clean-beauty-launch",
            "detected_at": "2026-08-04T14:20:00Z",
            "relevance_score": 0.88,
            "metadata": {
              "product_category": "Clean Beauty",
              "launch_date": "2026-09-01",
              "stores_impacted": 500
            },
            "actions_and_results": [],
            "created_at": "2026-08-04T14:20:00Z",
            "updated_at": "2026-08-04T14:20:00Z"
          }
        ],

        "opportunities": [
          {
            "id": 42,
            "objective_id": 1,
            "company_name": "Whole Foods Market",
            "description": "High-fit opportunity: expanding beauty category with new VP, launching product line",
            "fit_score": 0.90,
            "signal_ids": [101, 102],
            "overall_status": "in_progress",
            "total_deal_value": null,
            "last_signal_updated_at": "2026-08-10T16:30:00Z",
            "created_at": "2026-08-05T08:30:00Z",
            "updated_at": "2026-08-10T16:30:00Z",

            "contacts": [
              {
                "id": 1,
                "opportunity_id": 42,
                "name": "Sarah Johnson",
                "title": "VP of Product Development",
                "email": "sarah@wholefoods.com",
                "phone": "+1-555-0123",
                "source": "linkedin",
                "is_direct": true,
                "is_decision_maker": true,
                "verified": true,
                "created_at": "2026-08-05T08:20:00Z",
                "updated_at": "2026-08-05T08:20:00Z"
              },
              {
                "id": 2,
                "opportunity_id": 42,
                "name": "Michael Chen",
                "title": "Category Manager - Beauty",
                "email": "m.chen@wholefoods.com",
                "phone": null,
                "source": "website",
                "is_direct": false,
                "is_decision_maker": true,
                "verified": false,
                "created_at": "2026-08-05T08:25:00Z",
                "updated_at": "2026-08-05T08:25:00Z"
              }
            ],

            "recommendations": [
              {
                "id": 1,
                "opportunity_id": 42,
                "recommendation_type": "email",
                "content": {
                  "subject": "Brand Strategy Partnership - Whole Foods Beauty Expansion",
                  "body": "Hi Sarah, we noticed Whole Foods is expanding its beauty category and recently brought you on as VP of Product Development. We specialize in brand strategy for retail partners and have successfully worked with similar companies...",
                  "template_id": "beauty_expansion_email"
                },
                "confidence_score": 0.92,
                "score_breakdown": {
                  "fit_score": 0.90,
                  "signal_recency": 0.95,
                  "action_success_rate": 0.85,
                  "contact_quality": 0.95
                },
                "status": "pending",
                "created_at": "2026-08-05T08:35:00Z",
                "updated_at": "2026-08-05T08:35:00Z"
              },
              {
                "id": 2,
                "opportunity_id": 42,
                "recommendation_type": "call",
                "content": {
                  "talking_points": [
                    "Reference their new VP hire (shows we're paying attention)",
                    "Mention product launch timeline",
                    "Lead with value: how brand strategy accelerates market launch"
                  ],
                  "best_time": "Tuesday-Thursday, 10am-2pm CT"
                },
                "confidence_score": 0.68,
                "score_breakdown": {
                  "fit_score": 0.90,
                  "signal_recency": 0.85,
                  "action_success_rate": 0.60,
                  "contact_quality": 0.50
                },
                "status": "pending",
                "created_at": "2026-08-05T08:35:00Z",
                "updated_at": "2026-08-05T08:35:00Z"
              }
            ]
          }
        ],

        "activity_logs": [
          {
            "id": 501,
            "objective_id": 1,
            "action_type": "signal_detected",
            "description": "LinkedIn signal: Whole Foods hired VP of Product for beauty category",
            "details": {
              "signal_id": 101,
              "signal_type": "new_hire",
              "company": "Whole Foods Market",
              "source": "linkedin",
              "relevance_score": 0.92
            },
            "status": "success",
            "timestamp": "2026-08-05T08:15:00Z",
            "created_at": "2026-08-05T08:15:00Z"
          },
          {
            "id": 502,
            "objective_id": 1,
            "action_type": "signal_detected",
            "description": "News API signal: Whole Foods launches clean beauty line",
            "details": {
              "signal_id": 102,
              "signal_type": "product_launch",
              "company": "Whole Foods Market",
              "source": "news_api",
              "relevance_score": 0.88
            },
            "status": "success",
            "timestamp": "2026-08-04T14:20:00Z",
            "created_at": "2026-08-04T14:20:00Z"
          },
          {
            "id": 503,
            "objective_id": 1,
            "action_type": "opportunity_created",
            "description": "Opportunity created: Whole Foods Market (2 signals clustered)",
            "details": {
              "opportunity_id": 42,
              "company": "Whole Foods Market",
              "fit_score": 0.90,
              "signals_count": 2,
              "signal_ids": [101, 102]
            },
            "status": "success",
            "timestamp": "2026-08-05T08:30:00Z",
            "created_at": "2026-08-05T08:30:00Z"
          }
        ]
      },
      {... other objectives ...}
    ]
  }
}
```

---

## What This Shows

**Workspace (top level)**
- Owner and members
- Contains multiple objectives

**Objective (the goal)**
- Name + goal from user
- enriched_data populated by AI (target_context, strategy, search_teams)
- search_teams define what to search for and how
- end_date controls automation
- status tracks lifecycle

**Signals (detected events)**
- One signal per detected event (new hire, product launch, etc.)
- actions_and_results track what happened with each signal
- Multiple actions per signal (first email, follow-up, meeting, etc.)

**Opportunities (clustered prospects)**
- Groups signals by company
- fit_score is average of signal relevance scores
- overall_status is best result from all signals
- Shows progress toward objective goal

**Activity Logs (audit trail)**
- User sees every step AI took
- What was detected, when, why
- Builds trust through transparency
