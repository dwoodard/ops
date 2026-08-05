#!/bin/bash

# Generate Models, Migrations, Factories, Seeders
# Usage: ./models.sh

echo "🚀 Generating Laravel Models and Supporting Files..."

# Workspace
echo "📦 Creating Workspace..."
php artisan make:model Workspace -mf

# WorkspaceMember
echo "📦 Creating WorkspaceMember..."
php artisan make:model WorkspaceMember -m

# Objective
echo "📦 Creating Objective..."
php artisan make:model Objective -mf

# Signal
echo "📦 Creating Signal..."
php artisan make:model Signal -mf

# Contact
echo "📦 Creating Contact..."
php artisan make:model Contact -m

# Recommendation
echo "📦 Creating Recommendation..."
php artisan make:model Recommendation -m

# Opportunity
echo "📦 Creating Opportunity..."
php artisan make:model Opportunity -m

# ObjectiveActivityLog
echo "📦 Creating ObjectiveActivityLog..."
php artisan make:model ObjectiveActivityLog -m

# Seeders
echo "📦 Creating Seeders..."
php artisan make:seeder WorkspaceSeeder
php artisan make:seeder ObjectiveSeeder
php artisan make:seeder SignalSeeder
php artisan make:seeder OpportunitySeeder
php artisan make:seeder ContactSeeder
php artisan make:seeder RecommendationSeeder

echo ""
echo "✅ Model generation complete!"
echo ""
echo "Next steps:"
echo "  1. Define fillable properties in each model"
echo "  2. Add relationships between models"
echo "  3. Update migrations with schema"
echo "  4. Run migrations: php artisan migrate"
echo "  5. Seed data: php artisan db:seed"
