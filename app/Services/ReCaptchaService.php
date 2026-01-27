<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReCaptchaService
{
    protected string $secretKey;
    protected string $siteKey;
    protected string $version;
    protected float $scoreThreshold;
    protected bool $enabled;
    protected bool $enforceAdmin;
    protected bool $enforceUser;

    public function __construct()
    {
        $this->enabled = (bool) Setting::getValue('security', 'recaptcha_enabled', false);
        $this->enforceAdmin = (bool) Setting::getValue('security', 'recaptcha_enforce_admin', false);
        $this->enforceUser = (bool) Setting::getValue('security', 'recaptcha_enforce_user', false);
        $this->siteKey = Setting::getValue('security', 'recaptcha_site_key', '');
        $this->secretKey = Setting::getValue('security', 'recaptcha_secret_key', '');
        $this->version = Setting::getValue('security', 'recaptcha_version', 'v2');
        $this->scoreThreshold = (float) Setting::getValue('security', 'recaptcha_score_threshold', 0.5);
    }

    /**
     * Check if reCAPTCHA is enabled globally
     */
    public function isEnabled(): bool
    {
        return $this->enabled && !empty($this->siteKey) && !empty($this->secretKey);
    }

    /**
     * Check if reCAPTCHA is enforced for admin panel
     */
    public function isEnforcedForAdmin(): bool
    {
        return $this->isEnabled() && $this->enforceAdmin;
    }

    /**
     * Check if reCAPTCHA is enforced for user authentication
     */
    public function isEnforcedForUser(): bool
    {
        return $this->isEnabled() && $this->enforceUser;
    }

    /**
     * Get the site key for frontend
     */
    public function getSiteKey(): string
    {
        return $this->siteKey;
    }

    /**
     * Get the reCAPTCHA version (v2 or v3)
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Get the score threshold for v3
     */
    public function getScoreThreshold(): float
    {
        return $this->scoreThreshold;
    }

    /**
     * Verify reCAPTCHA token
     *
     * @param string|null $token The reCAPTCHA response token
     * @param string|null $remoteIp The user's IP address
     * @return array{success: bool, score?: float, action?: string, error_codes?: array}
     */
    public function verify(?string $token, ?string $remoteIp = null): array
    {
        // If not enabled, always return success
        if (!$this->isEnabled()) {
            return ['success' => true, 'skipped' => true];
        }

        if (empty($token)) {
            return [
                'success' => false,
                'error_codes' => ['missing-input-response'],
                'message' => 'reCAPTCHA verification is required.',
            ];
        }

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => $remoteIp,
            ]);

            $result = $response->json();

            if (!$result['success']) {
                Log::warning('reCAPTCHA verification failed', [
                    'error_codes' => $result['error-codes'] ?? [],
                    'ip' => $remoteIp,
                ]);

                return [
                    'success' => false,
                    'error_codes' => $result['error-codes'] ?? [],
                    'message' => 'reCAPTCHA verification failed. Please try again.',
                ];
            }

            // For v3, check the score
            if ($this->version === 'v3') {
                $score = $result['score'] ?? 0;
                
                if ($score < $this->scoreThreshold) {
                    Log::warning('reCAPTCHA v3 score too low', [
                        'score' => $score,
                        'threshold' => $this->scoreThreshold,
                        'action' => $result['action'] ?? 'unknown',
                        'ip' => $remoteIp,
                    ]);

                    return [
                        'success' => false,
                        'score' => $score,
                        'action' => $result['action'] ?? null,
                        'message' => 'Security verification failed. Please try again.',
                    ];
                }

                return [
                    'success' => true,
                    'score' => $score,
                    'action' => $result['action'] ?? null,
                ];
            }

            // v2 verification successful
            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error', [
                'error' => $e->getMessage(),
                'ip' => $remoteIp,
            ]);

            // On error, fail closed (deny access) for security
            return [
                'success' => false,
                'error_codes' => ['internal-error'],
                'message' => 'Security verification failed. Please try again later.',
            ];
        }
    }

    /**
     * Get reCAPTCHA configuration for frontend
     */
    public function getConfig(bool $forAdmin = false): array
    {
        $isEnforced = $forAdmin ? $this->isEnforcedForAdmin() : $this->isEnforcedForUser();

        return [
            'enabled' => $this->isEnabled() && $isEnforced,
            'siteKey' => $isEnforced ? $this->siteKey : '',
            'version' => $this->version,
        ];
    }
}
