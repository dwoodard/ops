<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Objective {
    id: number;
    name: string;
    goal: string;
    status: string;
    end_date: string | null;
    enriched_data: Record<string, any> | null;
    brand_voice: Record<string, any> | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    objective: Objective;
}>();

const formatDate = (date: string) => {
    return format(new Date(date), 'MMM dd, yyyy HH:mm');
};
</script>

<template>
    <div class="space-y-8">
        <Head :title="`Objective: ${objective.name}`" />

        <div>
            <h1 class="text-3xl font-bold tracking-tight">{{ objective.name }}</h1>
            <p class="text-muted-foreground mt-2">{{ objective.goal }}</p>
        </div>

        <!-- Overview Cards -->
        <div class="grid gap-4 md:grid-cols-2">
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-sm font-medium">Status</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold capitalize">{{ objective.status }}</div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-sm font-medium">End Date</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="text-xs">
                        {{ objective.end_date ? formatDate(objective.end_date) : 'No end date' }}
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Enriched Data -->
        <Card v-if="objective.enriched_data">
            <CardHeader>
                <CardTitle>Enriched Data</CardTitle>
            </CardHeader>
            <CardContent>
                <pre class="text-xs overflow-auto p-4 bg-muted rounded">{{ JSON.stringify(objective.enriched_data, null, 2) }}</pre>
            </CardContent>
        </Card>

        <!-- Brand Voice -->
        <Card v-if="objective.brand_voice">
            <CardHeader>
                <CardTitle>Brand Voice</CardTitle>
            </CardHeader>
            <CardContent>
                <pre class="text-xs overflow-auto p-4 bg-muted rounded">{{ JSON.stringify(objective.brand_voice, null, 2) }}</pre>
            </CardContent>
        </Card>

        <!-- Placeholder for later sections -->
        <div class="grid gap-4 md:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Signals</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">Signals will appear here (Phase 2)</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Opportunities</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">Opportunities will appear here (Phase 2)</p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
