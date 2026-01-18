<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Recruitment;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class HHruService
{
    protected $apiBaseUrl = 'https://api.hh.ru'; // API is ALWAYS hh.ru
    protected $authBaseUrl = 'https://hh.uz';    // Auth page can be hh.uz


    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;

    public function __construct()
    {
        $this->clientId = config('services.hh.client_id');
        $this->clientSecret = config('services.hh.client_secret');
        $this->redirectUri = config('services.hh.redirect');
    }

    public function getAuthUrl()
    {
        $redirect = urlencode($this->redirectUri);
        // Changed to hh.uz for better UX
        return "{$this->authBaseUrl}/oauth/authorize?response_type=code&client_id={$this->clientId}&redirect_uri={$redirect}";
    }

    public function authenticateCompany(Company $company, string $code)
    {
        // 1. Exchange Code for Token
        // Note: The token endpoint is usually on hh.ru, but hh.uz works too
        $response = Http::asForm()->post("{$this->authBaseUrl}/oauth/token", [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->redirectUri,
            'code'          => $code,
        ]);

        if ($response->failed()) {
            Log::error('HH Token Exchange Failed', $response->json());
            return false;
        }

        $data = $response->json();
        $accessToken = $data['access_token'];

        // 2. Get User Info (Check if Employer)
        $meResponse = Http::withToken($accessToken)->get($this->apiBaseUrl . '/me');
        $me = $meResponse->json();

        // Debug: See what HH returns
        Log::info('HH /me response:', $me);

        // 3. Save to Database
        $company->update([
            'hh_access_token'     => $accessToken,
            'hh_refresh_token'    => $data['refresh_token'],
            'hh_token_expires_at' => Carbon::now()->addSeconds($data['expires_in']),
            // If logged in as Job Seeker, 'employer' key might be missing.
            // We save it safely to avoid crashing.
            'hh_employer_id'      => $me['employer']['id'] ?? null,
        ]);

        return true;
    }

    public function postVacancy(Recruitment $recruitment)
    {
        $company = $recruitment->company;

        if (!$company || !$company->hh_access_token) {
            notify()->error('HH.ru is not connected for this company.');
            throw new \Exception("HH.ru is not connected for this company.");
        }

        $roleId = $recruitment->hh_professional_role_id;
        if (!$roleId) {
            notify()->error('Professional Role is required for HH.ru posting.');
            throw new \Exception("Professional Role is required for HH.ru posting.");
        }

        if (app()->environment('local')) {
            \Illuminate\Support\Facades\Log::info('FAKE HH POST: Job would be posted now.');

            $recruitment->update([
                'hh_vacancy_id' => '123456_TEST',
                'hh_url'        => 'https://hh.uz/vacancy/test_vacancy'
            ]);

            return true;
        }

        // Prepare Payload
        $payload = [
            'name'        => $recruitment->title,
            'description' => $recruitment->description,
            'professional_roles' => [
                ['id' => $roleId]
            ],
            'billing_type' => ['id' => $recruitment->billing_type ?? 'standard'],
            'key_skills'  => $this->mapKeySkills($recruitment->key_skills),
            'schedule'    => ['id' => $this->mapSchedule($recruitment->schedule)],
            'experience'  => ['id' => $this->mapExperience($recruitment->experience)],
            'employment'  => ['id' => $this->mapJobType($recruitment->job_type)],

            // ✅ IMPORTANT: Set Area to Tashkent (2759) or Uzbekistan
            'area'        => ['id' => '2759'],
            'type'        => ['id' => 'open'],
            'billing_type' => ['id' => 'standard'],
            'salary'      => $this->parseSalary($recruitment->salary_range),
        ];

        $response = Http::withToken($company->hh_access_token)
            ->post($this->apiBaseUrl . '/vacancies', $payload);

        if ($response->successful()) {
            $location = $response->header('Location');
            $hhId = basename($location);

            // Save URL as hh.uz
            $recruitment->update([
                'hh_vacancy_id' => $hhId,
                'hh_url'        => "https://hh.uz/vacancy/{$hhId}"
            ]);

            return true;
        }

        // Log the exact error from HH
        Log::error('HH Post Job Failed', $response->json());
        throw new \Exception("HH Error: " . ($response->json()['description'] ?? $response->body()));
    }

    // ... keep your helper mapping functions (mapExperience, etc) ...
    // ... keep parseSalary ...
    // ... keep mapKeySkills ...
    private function mapExperience($value)
    {
        return match ($value) {
            'No experience' => 'noExperience',
            '1-3 years'     => 'between1And3',
            '3-6 years'     => 'between3And6',
            '6+ years'      => 'moreThan6',
            default         => 'between1And3',
        };
    }

    private function mapSchedule($value)
    {
        return match ($value) {
            '5/2', '6/1', '2/2' => 'fullDay',
            'Flexible'          => 'flexible',
            'Remote'            => 'remote',
            default             => 'fullDay',
        };
    }

    private function mapJobType($value)
    {
        return match ($value) {
            'Full-time' => 'full',
            'Part-time' => 'part',
            'Internship' => 'probation',
            'Contract'  => 'project',
            default     => 'full',
        };
    }

    private function mapKeySkills($skills)
    {
        if (!$skills) return [];
        return array_map(fn($skill) => ['name' => $skill], $skills);
    }

    private function parseSalary($rangeString)
    {
        return null;
    }

    public function getProfessionalRoles()
    {
        return Cache::remember('hh_professional_roles', 86400, function () {
            $response = Http::get($this->apiBaseUrl . '/professional_roles');

            if ($response->successful()) {
                return $response->json()['categories'];
            }

            return [];
        });
    }
}
