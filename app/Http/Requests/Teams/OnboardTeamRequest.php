<?php

namespace App\Http\Requests\Teams;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OnboardTeamRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'website' => $this->normalizeWebsite($this->input('website')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'website' => ['required', 'url'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function normalizeWebsite(?string $website): ?string
    {
        if (! $website) {
            return $website;
        }

        if (preg_match('~^https?://~i', $website)) {
            return $website;
        }

        if (str_contains($website, '.')) {
            return 'https://'.$website;
        }

        return $website;
    }
}
