<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle2, X } from '@lucide/vue';
import { computed } from 'vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Team } from '@/types';

const page = usePage();

interface Props {
    team: Team & {
        enriched_data?: Record<string, unknown>;
        pending_enriched_data?: Record<string, unknown>;
        enrichment_status?: string;
        onboarded_at?: string;
    };
}

const props = defineProps<Props>();

const isReviewingEnrichment = computed(() => props.team.enrichment_status === 'pending_review');

const pendingEnrichedData = computed(() => {
    if (!props.team.pending_enriched_data) return null;
    if (typeof props.team.pending_enriched_data === 'string') {
        try {
            return JSON.parse(props.team.pending_enriched_data);
        } catch {
            return null;
        }
    }
    return props.team.pending_enriched_data;
});

const approvedEnrichedData = computed(() => {
    if (!props.team.enriched_data) return null;
    if (typeof props.team.enriched_data === 'string') {
        try {
            return JSON.parse(props.team.enriched_data);
        } catch {
            return null;
        }
    }
    return props.team.enriched_data;
});

defineOptions({
    layout: {
        title: 'Team Profile',
        description: 'Define your team identity and how AI understands your business',
    },
});
</script>

<template>
    <Head title="Team Profile" />

    <div class="mx-auto max-w-6xl py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight">Team Profile</h1>
            <p class="mt-2 text-lg text-muted-foreground">
                Define your team identity. This information powers AI enrichment across your workspace.
            </p>
        </div>

        <!-- Review Mode Alert -->
        <Alert v-if="isReviewingEnrichment" class="mb-6 border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950">
            <AlertCircle class="h-4 w-4 text-amber-600 dark:text-amber-400" />
            <AlertDescription class="text-amber-800 dark:text-amber-200">
                Review the AI's suggestions below.
            </AlertDescription>
        </Alert>

        <!-- Two Column Layout -->
        <div :class="['grid gap-8', isReviewingEnrichment ? 'grid-cols-1 lg:grid-cols-2' : 'grid-cols-2']">
            <!-- Left Column: Form (or User Input Review) -->
            <div class="space-y-6">
                <!-- Review Mode: Show User Input -->
                <div v-if="isReviewingEnrichment" class="rounded-lg border border-sidebar-border bg-card p-6">
                    <h3 class="font-semibold mb-4 flex items-center gap-2">
                        <CheckCircle2 class="h-5 w-5 text-green-600" />
                        Your Input
                    </h3>
                    <div class="space-y-4 text-sm">
                        <div>
                            <p class="text-xs font-medium text-muted-foreground uppercase">Website</p>
                            <p class="mt-1 text-foreground break-all">{{ team.website }}</p>
                        </div>
                        <div v-if="team.description" class="pt-2 border-t border-sidebar-border">
                            <p class="text-xs font-medium text-muted-foreground uppercase">Description</p>
                            <p class="mt-1 text-foreground whitespace-pre-wrap">{{ team.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Edit Mode: Form -->
                <form
                    v-else
                    :key="`${team.id}-form`"
                    :action="`/${team.slug}/team-profile/enrich`"
                    method="POST"
                    class="space-y-6"
                >
                    <input type="hidden" name="_token" :value="page.props.csrf_token || ''" />
                    <div class="space-y-2">
                        <Label for="website">Company website</Label>
                        <Input
                            id="website"
                            name="website"
                            :value="team.website ?? undefined"
                            placeholder="https://example.com"

                        />
                        <p class="text-sm text-muted-foreground">
                            We'll research your organization to understand your business
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Company description</Label>
                        <textarea
                            id="description"
                            name="description"
                            placeholder="Tell us what your company does, your value proposition, and any other context..."
                            class="rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 min-h-40 w-full"
                        >{{ team.description || '' }}</textarea>
                        <p class="text-sm text-muted-foreground">
                            Help AI understand your business better
                        </p>
                    </div>

                    <Button type="submit" class="w-full">
                        Analyze with AI
                    </Button>
                </form>
            </div>

            <!-- Right Column: Info & AI Results -->
            <div class="space-y-6">
                <!-- Info Card (always show when not reviewing) -->
                <div v-if="!isReviewingEnrichment" class="rounded-lg border border-sidebar-border bg-card p-6">
                    <h3 class="font-semibold mb-3">How this helps</h3>
                    <ul class="space-y-3 text-sm text-muted-foreground">
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span>AI learns about your team and business model</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span>Enriches objectives with targeted insights</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span>Powers smart signal detection and scoring</span>
                        </li>
                        <li class="flex gap-2">
                            <span class="text-primary">•</span>
                            <span>Generates recommendations in your voice</span>
                        </li>
                    </ul>
                </div>

                <!-- Review Mode: Editable AI Suggestions & Actions -->
                <template v-if="isReviewingEnrichment">
                    <form v-if="pendingEnrichedData" :action="`/${team.slug}/team-profile/approve`" method="POST" class="rounded-lg border border-sidebar-border bg-card p-6">
                        <input type="hidden" name="_token" :value="page.props.csrf_token || ''" />
                        <h3 class="font-semibold mb-4">AI Suggestions (editable)</h3>
                        <div class="space-y-4 text-sm">
                            <div v-if="pendingEnrichedData.description">
                                <p class="text-xs font-medium text-muted-foreground uppercase mb-2">Suggested Description</p>
                                <textarea
                                    name="description"
                                    class="rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 min-h-24 w-full"
                                >{{ pendingEnrichedData.description }}</textarea>
                            </div>
                            <div v-if="pendingEnrichedData.summary" class="pt-2 border-t border-sidebar-border">
                                <p class="text-xs font-medium text-muted-foreground uppercase mb-2">Summary</p>
                                <textarea
                                    name="summary"
                                    class="rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 min-h-16 w-full"
                                >{{ pendingEnrichedData.summary }}</textarea>
                            </div>
                            <div v-if="pendingEnrichedData.industry" class="pt-2 border-t border-sidebar-border">
                                <p class="text-xs font-medium text-muted-foreground uppercase mb-2">Industry</p>
                                <input
                                    type="text"
                                    name="industry"
                                    :value="pendingEnrichedData.industry"
                                    class="rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 w-full"
                                />
                            </div>
                            <div v-if="pendingEnrichedData.target_market" class="pt-2 border-t border-sidebar-border">
                                <p class="text-xs font-medium text-muted-foreground uppercase mb-2">Target Market</p>
                                <textarea
                                    name="target_market"
                                    class="rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 min-h-16 w-full"
                                >{{ pendingEnrichedData.target_market }}</textarea>
                            </div>
                        </div>

                        <div class="space-y-3 mt-6">
                            <Button type="submit" class="w-full bg-green-600 hover:bg-green-700">
                                Approve & Save
                            </Button>
                        </div>
                    </form>

                    <form :action="`/${team.slug}/team-profile/reject`" method="POST" class="space-y-3">
                        <input type="hidden" name="_token" :value="page.props.csrf_token || ''" />
                        <Button type="submit" variant="outline" class="w-full">
                            <X class="h-4 w-4 mr-2" />
                            Reject & Edit
                            </Button>
                        </form>
                </template>

                <!-- Enriched Data Section (approved) -->
                <div v-else-if="approvedEnrichedData" class="rounded-lg border border-sidebar-border bg-card p-6">
                    <h3 class="font-semibold mb-3 flex items-center gap-2">
                        <CheckCircle2 class="h-5 w-5 text-green-600" />
                        AI Analysis (Approved)
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div v-if="approvedEnrichedData.summary" class="text-muted-foreground">
                            {{ approvedEnrichedData.summary }}
                        </div>
                        <div v-if="approvedEnrichedData.industry" class="pt-2 border-t border-sidebar-border">
                            <p class="text-xs font-medium text-muted-foreground uppercase">Industry</p>
                            <p class="mt-1 text-foreground">{{ approvedEnrichedData.industry }}</p>
                        </div>
                        <div v-if="approvedEnrichedData.target_market" class="pt-2 border-t border-sidebar-border">
                            <p class="text-xs font-medium text-muted-foreground uppercase">Target Market</p>
                            <p class="mt-1 text-foreground">{{ approvedEnrichedData.target_market }}</p>
                        </div>
                        <div v-if="team.onboarded_at" class="text-xs text-muted-foreground pt-2 border-t border-sidebar-border">
                            Last updated: {{ new Date(team.onboarded_at).toLocaleDateString() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
