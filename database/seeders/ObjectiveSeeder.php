<?php

namespace Database\Seeders;

use App\Models\Objective;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class ObjectiveSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'user@user.com')->first();

        if (! $user) {
            return;
        }

        // Get or create teams
        $blueTeam = Team::firstOrCreate(
            ['slug' => 'blue-team'],
            ['name' => 'Blue team', 'is_personal' => false]
        );

        $redTeam = Team::firstOrCreate(
            ['slug' => 'red-team'],
            ['name' => 'Red team', 'is_personal' => false]
        );

        // Ensure user is a member of both teams
        if (! $blueTeam->members()->where('user_id', $user->id)->exists()) {
            $blueTeam->members()->attach($user->id, ['role' => 'owner']);
        }
        if (! $redTeam->members()->where('user_id', $user->id)->exists()) {
            $redTeam->members()->attach($user->id, ['role' => 'owner']);
        }

        $user->update(['current_team_id' => $blueTeam->id]);

        // Concrete example for Blue team
        Objective::factory()->create([
            'team_id' => $blueTeam->id,
            'owner_id' => $user->id,
            'name' => 'Partner with YouTube Influencers 500k+',
            'goal' => 'Find YouTube influencers with 500k+ subscribers to team up with for brand collaborations',
            'status' => 'active',
            'end_date' => now()->addWeeks(2),
            'enriched_data' => [
                'target_context' => [
                    'platforms' => ['YouTube'],
                    'subscriber_range' => '500k-5M',
                    'content_categories' => ['Tech', 'Lifestyle', 'Business', 'Wellness'],
                    'engagement_rate' => '2-8%',
                    'geography' => [
                        'Utah' => ['Salt Lake City', 'Provo', 'Ogden'],
                        'Wyoming' => ['Cheyenne', 'Casper', 'Laramie'],
                        'Colorado' => ['Denver', 'Colorado Springs', 'Boulder'],
                        'New Mexico' => ['Albuquerque', 'Santa Fe', 'Las Cruces'],
                        'Arizona' => ['Phoenix', 'Tucson', 'Mesa'],
                        'Nevada' => ['Las Vegas', 'Reno', 'Henderson'],
                        'Idaho' => ['Boise', 'Pocatello', 'Idaho Falls'],
                    ],
                    'audience_demographics' => ['18-35', 'Tech-savvy', 'High income'],
                ],
                'services_positioned' => [
                    'Brand partnerships',
                    'Sponsored content',
                    'Product launches',
                    'Affiliate programs',
                ],
                'strategy' => [
                    'Search YouTube for creators in target categories',
                    'Check subscriber counts and engagement metrics',
                    'Analyze recent video performance',
                    'Review audience demographics',
                    'Monitor collaboration patterns',
                ],
                'search_terms' => [
                    [
                        'id' => 'youtube_search_1',
                        'source' => 'youtube',
                        'frequency' => 'daily',
                        'enabled' => true,
                    ],
                    [
                        'id' => 'influencer_db_1',
                        'source' => 'influencer_database',
                        'frequency' => 'weekly',
                        'enabled' => true,
                    ],
                    [
                        'id' => 'social_media_1',
                        'source' => 'social_media_analytics',
                        'frequency' => 'daily',
                        'enabled' => true,
                    ],
                ],
                'integrations_used' => ['youtube_api', 'influencer_database', 'social_analytics'],
            ],
            'brand_voice' => [
                'tone' => 'collaborative, energetic, forward-thinking',
                'value_props' => [
                    'Authentic brand partnerships',
                    'Genuine audience connections',
                    'Creative collaboration',
                ],
                'case_studies' => [
                    'Successfully partnered with 3 creators reaching 2M combined audience',
                    'Generated 500k+ impressions through influencer campaigns',
                ],
            ],
        ]);

        // Filler objectives for Blue team
        Objective::factory()->count(2)->create([
            'team_id' => $blueTeam->id,
            'owner_id' => $user->id,
        ]);

        // Concrete example for Red team
        Objective::factory()->create([
            'team_id' => $redTeam->id,
            'owner_id' => $user->id,
            'name' => 'Grow SaaS customer base by 50%',
            'goal' => 'Identify and convert enterprise customers in the tech sector who need our B2B SaaS solution',
            'status' => 'active',
            'end_date' => now()->addMonths(3),
        ]);

        // Filler objectives for Red team
        Objective::factory()->count(2)->create([
            'team_id' => $redTeam->id,
            'owner_id' => $user->id,
        ]);

    }
}
