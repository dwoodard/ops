<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/vue3';
import objectives from '@/routes/objectives';

const page = usePage();
const currentTeam = page.props.currentTeam as any;

const form = useForm({
    name: '',
    goal: '',
    end_date: '',
});

const submit = () => {
    form.post(objectives.store({ current_team: currentTeam.slug }).url);
};
</script>

<template>
    <div class="space-y-8">
        <Head title="Create Objective" />

        <div>
            <h1 class="text-3xl font-bold tracking-tight">Create Objective</h1>
            <p class="text-muted-foreground mt-2">Define what you're trying to find</p>
        </div>

        <form @submit.prevent="submit" class="max-w-2xl space-y-6">
            <!-- Name -->
            <div class="space-y-2">
                <Label for="name">Objective Name</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    placeholder="e.g. Land 10 Retail Accounts"
                    type="text"
                    required
                />
                <p v-if="form.errors.name" class="text-sm text-red-500">{{ form.errors.name }}</p>
            </div>

            <!-- Goal Description -->
            <div class="space-y-2">
                <Label for="goal">Goal Description</Label>
                <textarea
                    id="goal"
                    v-model="form.goal"
                    placeholder="What are you trying to accomplish? Who do you want to reach?"
                    rows="4"
                    required
                    class="w-full px-3 py-2 border border-input rounded-md bg-background text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                />
                <p v-if="form.errors.goal" class="text-sm text-red-500">{{ form.errors.goal }}</p>
            </div>

            <!-- End Date -->
            <div class="space-y-2">
                <Label for="end_date">End Date</Label>
                <Input
                    id="end_date"
                    v-model="form.end_date"
                    type="text"
                    placeholder="e.g. 2026-08-18T14:00"
                    required
                />
                <p class="text-xs text-muted-foreground">Format: YYYY-MM-DDTHH:mm</p>
                <p v-if="form.errors.end_date" class="text-sm text-red-500">{{ form.errors.end_date }}</p>
            </div>

            <!-- Submit -->
            <div class="flex gap-2">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Creating...' : 'Create Objective' }}
                </Button>
            </div>
        </form>
    </div>
</template>
