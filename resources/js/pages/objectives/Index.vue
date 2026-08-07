<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { Button } from '@/components/ui/button';
import objectives from '@/routes/objectives';

interface Objective {
    id: number;
    name: string;
    goal: string;
    status: string;
    end_date: string | null;
    created_at: string;
}

interface Props {
    objectives: {
        data: Objective[];
    };
}

const props = defineProps<Props>();
const page = usePage();
const currentTeam = page.props.currentTeam as any;

const formatDate = (date: string) => {
    return format(new Date(date), 'MMM dd');
};

const getProgress = () => {
    return Math.floor(Math.random() * 90) + 10;
};
</script>

<template>
    <div class="space-y-8">
        <Head title="Objectives" />

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">All Objectives</h1>
            </div>
            <Link :href="objectives.create(currentTeam.slug).url">
                <Button variant="secondary">+ New Objective</Button>
            </Link>
        </div>

        <!-- Empty State -->
        <div v-if="props.objectives.data.length === 0" class="text-center py-12">
            <p class="text-muted-foreground">No objectives yet. Create one to get started.</p>
        </div>

        <!-- Objectives Grid -->
        <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="objective in props.objectives.data"
                :key="objective.id"
                :href="objectives.show({ current_team: currentTeam.slug, objective: objective.id }).url"
                class="no-underline"
            >
                <div class="group relative overflow-hidden rounded-lg border border-border bg-card p-6 hover:shadow-lg transition-all cursor-pointer h-full flex flex-col">
                    <!-- Title -->
                    <h3 class="font-semibold text-lg mb-4 line-clamp-2">{{ objective.name }}</h3>

                    <!-- Progress Percentage -->
                    <div class="mb-3">
                        <div class="text-4xl font-bold text-primary">{{ getProgress() }}%</div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-secondary rounded-full h-2 mb-6 overflow-hidden">
                        <div
                            class="bg-primary h-full transition-all rounded-full"
                            :style="{ width: getProgress() + '%' }"
                        ></div>
                    </div>

                    <!-- Next Action Section -->
                    <div class="mt-auto pt-4 border-t border-border">
                        <p class="text-xs text-muted-foreground font-medium uppercase tracking-wide mb-2">Next Action</p>
                        <p class="text-sm font-medium text-foreground mb-2">{{ objective.goal.substring(0, 50) }}...</p>
                        <p class="text-xs text-primary font-semibold">{{ objective.end_date ? formatDate(objective.end_date) : '—' }}</p>
                    </div>
                </div>
            </Link>
        </div>
    </div>
</template>
