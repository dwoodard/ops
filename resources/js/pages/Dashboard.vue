<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, Target, TrendingUp, Zap } from '@lucide/vue';
import PendingInvitationsModal from '@/components/PendingInvitationsModal.vue';
import TeamSetupAlert from '@/components/ops/TeamSetupAlert.vue';
import dashboardRoute from '@/routes/dashboard';
import objectivesRoute from '@/routes/objectives';
import type { DashboardInvitation, Team } from '@/types';

interface Objective {
    id: number;
    name: string;
    status: string;
    goal: string;
    owner: {
        id: number;
        name: string;
    };
    signalCount: number;
    opportunityCount: number;
    progress: {
        target: number;
        current: number;
        in_progress: number;
        percentage: number;
    };
}

interface Stats {
    totalObjectives: number;
    activeObjectives: number;
    completedObjectives: number;
}

const props = defineProps<{
    pendingInvitations?: DashboardInvitation[];
    objectives?: Objective[];
    stats?: Stats;
    currentTeam?: Team | null;
}>();

defineOptions({
    inheritAttrs: false,
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: props.currentTeam
                    ? dashboardRoute.index({ current_team: props.currentTeam.slug }).url
                    : '/',
            },
        ],
    }),
});

const getStatusColor = (status: string): string => {
    const colors: Record<string, string> = {
        active: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        completed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        paused: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        overdue: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
};
</script>

<template>
    <Head title="Dashboard" />

    <PendingInvitationsModal
        v-if="pendingInvitations && pendingInvitations.length > 0"
        :invitations="pendingInvitations"
    />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <TeamSetupAlert
            v-if="currentTeam && !currentTeam.setupComplete"
            :team="currentTeam"
        />
        <!-- Stats Cards -->
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-sidebar-border bg-card p-4 dark:border-sidebar-border dark:bg-card">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900">
                        <Target class="h-5 w-5 text-blue-600 dark:text-blue-300" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Total Objectives</p>
                        <p class="text-2xl font-bold">{{ stats?.totalObjectives ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-sidebar-border bg-card p-4 dark:border-sidebar-border dark:bg-card">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900">
                        <Zap class="h-5 w-5 text-green-600 dark:text-green-300" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Active Objectives</p>
                        <p class="text-2xl font-bold">{{ stats?.activeObjectives ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-sidebar-border bg-card p-4 dark:border-sidebar-border dark:bg-card">
                <div class="flex items-center gap-3">
                    <div class="rounded-lg bg-emerald-100 p-2 dark:bg-emerald-900">
                        <CheckCircle2 class="h-5 w-5 text-emerald-600 dark:text-emerald-300" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">Completed</p>
                        <p class="text-2xl font-bold">{{ stats?.completedObjectives ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Objectives List -->
        <div class="rounded-lg border border-sidebar-border dark:border-sidebar-border">
            <div class="border-b border-sidebar-border p-4 dark:border-sidebar-border">
                <div class="flex items-center gap-2">
                    <TrendingUp class="h-5 w-5" />
                    <h2 class="text-lg font-semibold">Recent Objectives</h2>
                </div>
            </div>

            <div v-if="!objectives || objectives.length === 0" class="p-8 text-center">
                <p class="text-sm text-muted-foreground">No objectives yet. Create one to get started.</p>
            </div>

            <div v-else class="divide-y divide-sidebar-border dark:divide-sidebar-border">
                <div
                    v-for="objective in objectives"
                    :key="objective.id"
                    class="p-4 hover:bg-muted/50 transition-colors"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <Link
                                :href="props.currentTeam ? objectivesRoute.show({ current_team: props.currentTeam.slug, objective: objective.id }).url : '#'"
                                class="flex items-center gap-2 hover:underline"
                            >
                                <h3 class="font-medium">{{ objective.name }}</h3>
                                <span :class="['inline-flex rounded-full px-2 py-1 text-xs font-medium', getStatusColor(objective.status)]">
                                    {{ objective.status }}
                                </span>
                            </Link>
                            <p class="mt-1 text-sm text-muted-foreground line-clamp-2">{{ objective.goal }}</p>
                            <div class="mt-3 flex gap-4 text-xs text-muted-foreground">
                                <span>{{ objective.signalCount }} signals</span>
                                <span>{{ objective.opportunityCount }} opportunities</span>
                                <span>Owner: {{ objective.owner.name }}</span>
                            </div>
                        </div>
                        <div class="w-40 text-right">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-xs font-medium">Progress</span>
                                <span class="text-xs font-bold">{{ Math.round(objective.progress.percentage) }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-muted">
                                <div
                                    class="h-full bg-blue-600 transition-all dark:bg-blue-400"
                                    :style="{ width: `${objective.progress.percentage}%` }"
                                />
                            </div>
                            <div class="mt-1 text-xs text-muted-foreground">
                                {{ objective.progress.current }} / {{ objective.progress.target }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
