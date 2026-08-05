<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { update } from '@/routes/onboarding';
import { dashboard } from '@/routes';
import type { Team } from '@/types';

type Props = {
    team: Team;
};

defineProps<Props>();

defineOptions({
    layout: {
        title: 'Set up your team',
        description: 'Help us learn more about your organization so we can personalize your experience',
    },
});
</script>

<template>
    <Head title="Set up your team" />

    <Form
        :key="`${team.id}-form`"
        v-bind="update.form({ current_team: team.slug })"
        class="space-y-6"
        v-slot="{ errors, processing }"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="website">Company website</Label>
                <Input
                    id="website"
                    type="url"
                    name="website"
                    placeholder="https://example.com"
                    required
                />
                <InputError :message="errors.website" />
                <p class="text-sm text-muted-foreground">
                    We'll research your organization to personalize the experience
                </p>
            </div>

            <div class="grid gap-2">
                <Label for="description">Company description (optional)</Label>
                <textarea
                    id="description"
                    name="description"
                    placeholder="Tell us what your company does..."
                    class="rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 min-h-25"
                />
                <InputError :message="errors.description" />
                <p class="text-sm text-muted-foreground">
                    Any additional context helps us understand your business better
                </p>
            </div>

            <Button
                type="submit"
                class="w-full"
                :disabled="processing"
            >
                <Spinner v-if="processing" />
                {{ processing ? 'Setting up your team...' : 'Continue' }}
            </Button>
        </div>

        <div class="text-center text-sm">
            <TextLink
                :href="dashboard(team.slug)"
                class="text-muted-foreground hover:text-foreground underline underline-offset-4"
            >
                Skip for now
            </TextLink>
        </div>
    </Form>
</template>
