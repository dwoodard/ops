<script setup lang="ts">
import { AlertCircle, ChevronRight, X } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import teamProfile from '@/routes/team-profile';
import type { Team } from '@/types';

interface Props {
    team: Team & { setupComplete: boolean };
}

defineProps<Props>();

const dismissed = ref(false);

const getMissingFields = (team: Props['team']): string[] => {
    const missing: string[] = [];
    if (!team.website) missing.push('website');
    if (!team.description) missing.push('company description');
    return missing;
};
</script>

<template>
    <div
        v-if="!dismissed && !team.setupComplete"
        class="mb-4 flex items-center gap-4 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950"
    >
        <AlertCircle class="h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400" />

        <div class="flex-1">
            <h3 class="font-medium text-amber-900 dark:text-amber-100">
                Complete your team setup
            </h3>
            <p class="mt-1 text-sm text-amber-800 dark:text-amber-200">
                Add your {{ getMissingFields(team).join(' and ') }} to enable AI enrichment for your objectives.
            </p>
        </div>

        <Link
            :href="teamProfile.show({ current_team: team.slug }).url"
            class="flex flex-shrink-0 items-center gap-2 rounded-md bg-amber-600 px-3 py-2 text-sm font-medium text-white transition-colors hover:bg-amber-700 dark:bg-amber-700 dark:hover:bg-amber-600"
        >
            Set up now
            <ChevronRight class="h-4 w-4" />
        </Link>

        <button
            @click="dismissed = true"
            class="flex-shrink-0 text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300"
            aria-label="Dismiss alert"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
