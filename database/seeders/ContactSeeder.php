<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Opportunity;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $opportunities = Opportunity::all();

        foreach ($opportunities as $opportunity) {
            Contact::factory()
                ->count(random_int(1, 5))
                ->create(['opportunity_id' => $opportunity->id]);
        }
    }
}
