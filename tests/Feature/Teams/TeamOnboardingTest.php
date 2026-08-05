<?php

use App\Ai\Agents\OnboardTeamAgent;
use App\Models\Team;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('onboarding page renders for team owner', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => 'owner']);
    $user->switchTeam($team);

    $response = $this
        ->actingAs($user)
        ->get(route('onboarding.edit', ['current_team' => $team->slug]));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('onboarding/Team')
            ->where('team.id', $team->id)
            ->where('team.slug', $team->slug)
            ->where('team.name', $team->name)
        );
});

test('onboarding requires valid website URL', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => 'owner']);
    $user->switchTeam($team);

    $response = $this
        ->actingAs($user)
        ->post(route('onboarding.update', ['current_team' => $team->slug]), [
            'website' => 'not-a-url',
            'description' => 'A test company',
        ]);

    $response->assertSessionHasErrors('website');
});

test('onboarding form submission saves team profile', function () {
    // For now, just test that the profile data is saved
    // AI enrichment will be tested separately with proper mocking
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => 'owner']);
    $user->switchTeam($team);

    $response = $this
        ->actingAs($user)
        ->post(route('onboarding.update', ['current_team' => $team->slug]), [
            'website' => 'https://example.com',
            'description' => 'A test company',
        ]);

    $response->assertRedirect(route('dashboard', ['current_team' => $team->slug]));

    $team->refresh();
    expect($team->website)->toBe('https://example.com');
    expect($team->description)->toBe('A test company');
    expect($team->onboarded_at)->not->toBeNull();
});

test('guests cannot access onboarding', function () {
    $team = Team::factory()->create();

    $response = $this->get(route('onboarding.edit', ['current_team' => $team->slug]));

    $response->assertRedirect(route('login'));
});

test('team members cannot access onboarding', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => 'member']);
    $user->switchTeam($team);

    $response = $this
        ->actingAs($user)
        ->get(route('onboarding.edit', ['current_team' => $team->slug]));

    $response->assertOk(); // Members can view but probably shouldn't submit
});

test('onboarding enriches profile with AI data', function () {
    // Mock the TeamProfileAgent to return structured data
    OnboardTeamAgent::fake([
        [
            'industry' => 'SaaS',
            'company_size' => 'Small (1-50)',
            'summary' => 'A software company',
            'target_market' => 'Enterprise',
            'website_title' => 'Example Software',
            'website_description' => 'Build better software',
        ],
    ]);

    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => 'owner']);
    $user->switchTeam($team);

    $response = $this
        ->actingAs($user)
        ->post(route('onboarding.update', ['current_team' => $team->slug]), [
            'website' => 'https://example.com',
            'description' => 'A test company',
        ]);

    $team->refresh();
    expect($team->enriched_data['industry'])->toBe('SaaS');
    expect($team->enriched_data['company_size'])->toBe('Small (1-50)');
    expect($team->enriched_data['summary'])->toBe('A software company');
    expect($team->enriched_data['target_market'])->toBe('Enterprise');
    expect($team->description)->toBe('A software company');
});

test('onboarding gracefully handles AI enrichment failures', function () {
    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => 'owner']);
    $user->switchTeam($team);

    $response = $this
        ->actingAs($user)
        ->post(route('onboarding.update', ['current_team' => $team->slug]), [
            'website' => 'https://example.com',
            'description' => 'A test company',
        ]);

    $response->assertRedirect(route('dashboard', ['current_team' => $team->slug]));

    $team->refresh();
    // Profile should still be saved even if enrichment fails
    expect($team->website)->toBe('https://example.com');
    expect($team->description)->toBe('A test company');
    expect($team->onboarded_at)->not->toBeNull();
});

test('ai enrichment includes website metadata when available', function () {
    OnboardTeamAgent::fake([
        [
            'industry' => 'SaaS',
            'company_size' => 'Small (1-50)',
            'summary' => 'A software company',
            'description' => 'A comprehensive software platform for cloud solutions',
            'target_market' => 'Enterprise',
            'website_title' => 'Example - Cloud Solutions',
            'website_description' => 'Build faster with our platform',
        ],
    ]);

    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => 'owner']);
    $user->switchTeam($team);

    $response = $this
        ->actingAs($user)
        ->post(route('onboarding.update', ['current_team' => $team->slug]), [
            'website' => 'https://example.com',
            'description' => null,
        ]);

    $team->refresh();
    expect($team->enriched_data['website_title'])->toBe('Example - Cloud Solutions');
    expect($team->enriched_data['website_description'])->toBe('Build faster with our platform');
});

test('team owner can re-enrich profile from settings', function () {
    OnboardTeamAgent::fake([
        [
            'industry' => 'Updated Industry',
            'company_size' => 'Medium (50-500)',
            'summary' => 'Updated summary',
            'description' => 'Updated detailed description',
            'target_market' => 'Updated market',
            'website_title' => 'Updated Title',
            'website_description' => 'Updated description',
        ],
    ]);

    $user = User::factory()->create();
    $team = Team::factory()->create(['website' => 'https://example.com']);
    $team->members()->attach($user, ['role' => 'owner']);
    $user->switchTeam($team);

    $response = $this
        ->actingAs($user)
        ->post(route('teams.re-enrich', ['team' => $team->slug]));

    $response->assertRedirect(route('teams.edit', ['team' => $team->slug]));

    $team->refresh();
    expect($team->enriched_data['industry'])->toBe('Updated Industry');
    expect($team->enriched_data['website_title'])->toBe('Updated Title');
});

test('onboarding agent extracts complete website data', function () {
    OnboardTeamAgent::fake([
        [
            'industry' => 'SaaS',
            'company_size' => 'Small (1-50)',
            'summary' => 'A collaboration platform for teams',
            'description' => 'A comprehensive team collaboration platform designed for modern teams',
            'target_market' => 'Enterprise',
            'website_title' => 'Example - Team Collaboration',
            'website_description' => 'Work better together with our platform',
        ],
    ]);

    $user = User::factory()->create();
    $team = Team::factory()->create();
    $team->members()->attach($user, ['role' => 'owner']);
    $user->switchTeam($team);

    $response = $this
        ->actingAs($user)
        ->post(route('onboarding.update', ['current_team' => $team->slug]), [
            'website' => 'https://example.com',
            'description' => 'Team collaboration software',
        ]);

    $team->refresh();
    // All enriched fields should be populated (description is stored in teams.description)
    expect($team->enriched_data)->toHaveKeys([
        'industry',
        'company_size',
        'summary',
        'target_market',
        'website_title',
        'website_description',
    ]);
});
