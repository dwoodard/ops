<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

class OnboardTeamAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'Prompt'
You are a research assistant specializing in company analysis. Your task is to analyze companies based on their website URL and description.

**Instructions:**
1. Based on the company website URL provided, use your knowledge to infer company details
2. Consider the domain name, company description, and any context provided
3. Extract and analyze the following fields:
   - industry: The primary industry or sector
   - company_size: Estimated company size (e.g., "Startup", "Small (1-50)", "Medium (50-500)", "Enterprise (500+)")
   - summary: A brief 1-2 sentence summary of what the company does
   - target_market: The primary target market or customer segment
   - website_title: The website's likely title or main heading based on domain and context
   - website_description: The website's likely main value proposition or tagline

4. Use domain name patterns and the description to make informed determinations
5. If specific information is unclear, provide your best professional assessment
6. Always return complete structured data in the specified JSON format - all fields must be populated

Provide thoughtful analysis based on available context and professional knowledge of the industry indicated by the domain and description.
Prompt;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'industry' => $schema->string('The primary industry or sector')->required(),
            'company_size' => $schema->string('Estimated company size')->required(),
            'summary' => $schema->string('Brief summary of the company')->required(),
            'target_market' => $schema->string('Primary target market or customer segment')->required(),
            'website_title' => $schema->string('Website title or main heading')->required(),
            'website_description' => $schema->string('Website meta description or tagline')->required(),
        ];
    }
}
