<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Settings2, AlertCircle } from '@lucide/vue';
import teamProfile from '@/routes/team-profile';
import type { Team } from '@/types';

interface Props {
    team: Team & { setupComplete?: boolean };
}

defineProps<Props>();
</script>

<template>
    <Link
        :href="teamProfile.show({ current_team: team.slug }).url"
        class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-muted"
        :class="[
            team.setupComplete
                ? 'text-foreground hover:text-foreground'
                : 'text-amber-600 dark:text-amber-400'
        ]"
        :title="team.setupComplete ? 'Edit team profile' : 'Complete team setup'"
    >
        <div class="flex items-center gap-2">
            <span class="hidden sm:inline text-xs">{{ team.name }}</span>
            <div class="relative">
                <Settings2 class="h-4 w-4" />
                <div
                    v-if="!team.setupComplete"
                    class="absolute top-0 right-0 h-2 w-2 rounded-full bg-amber-500 animate-pulse"
                />
            </div>
        </div>
    </Link>
</template>
