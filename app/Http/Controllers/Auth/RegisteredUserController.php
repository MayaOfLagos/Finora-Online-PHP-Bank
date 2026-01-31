<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Countries;
use App\Helpers\Currencies;
use App\Http\Controllers\Controller;
use App\Models\AccountType;
use App\Models\BankAccount;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\AdminNotificationService;
use App\Services\ReCaptchaService;
use App\Services\ReferralService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected ReferralService $referralService
    ) {}

    /**
     * Display the registration view.
     */
    public function create(Request $request): Response
    {
        $recaptchaService = app(ReCaptchaService::class);

        // Get account types for dropdown
        $accountTypes = AccountType::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($type) => [
                'label' => $type->name,
                'value' => $type->code,
                'description' => $type->description,
            ])
            ->toArray();

        // Get countries for dropdown
        $countries = collect(Countries::all())
            ->map(fn ($name, $code) => [
                'label' => $name,
                'value' => $code,
            ])
            ->values()
            ->toArray();

        // Get currencies for dropdown
        $currencies = Currencies::forDropdown();

        // Check for referral code in URL
        $referralCode = $request->query('ref', '');
        $referralInfo = null;

        if ($referralCode && $this->referralService->isEnabled()) {
            $inviterInfo = $this->referralService->getReferrerInfo($referralCode);
            if ($inviterInfo) {
                $referralInfo = [
                    'enabled' => true,
                    'bonus_enabled' => $this->referralService->isNewUserBonusEnabled(),
                    'bonus_amount' => $this->referralService->isNewUserBonusEnabled()
                        ? $this->referralService->getNewUserBonusAmount()
                        : 0,
                    'inviter' => [
                        'name' => $inviterInfo['name'],
                        'avatar' => $inviterInfo['avatar'] ?? null,
                        'level' => $inviterInfo['level'] ?? null,
                    ],
                ];
            }
        }

        return Inertia::render('Auth/Register', [
            'status' => session('status'),
            'countries' => $countries,
            'currencies' => $currencies,
            'accountTypes' => $accountTypes,
            'recaptcha' => $recaptchaService->getConfig(forAdmin: false),
            'referralCode' => $referralCode,
            'referralInfo' => $referralInfo,
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Verify reCAPTCHA first
        $recaptchaService = app(ReCaptchaService::class);

        if ($recaptchaService->isEnforcedForUser()) {
            $recaptchaResult = $recaptchaService->verify(
                $request->input('recaptcha_token'),
                $request->ip()
            );

            if (! $recaptchaResult['success']) {
                return back()->withErrors([
                    'recaptcha_token' => $recaptchaResult['message'] ?? 'Security verification failed. Please try again.',
                ]);
            }
        }

        $validated = $request->validate([
            // Personal Info
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:users', 'regex:/^[a-zA-Z0-9_]+$/'],

            // Contact Info
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:2'],

            // Account Info
            'account_type' => ['required', 'string', 'exists:account_types,code'],
            'currency' => ['required', 'string', 'max:3'],

            // Security Info
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'transaction_pin' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/', 'confirmed'],

            // Agreements
            'agree_terms' => ['required', 'accepted'],
            'agree_privacy' => ['required', 'accepted'],

            // Referral (optional)
            'referral_code' => ['nullable', 'string', 'max:20'],
        ], [
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
            'transaction_pin.size' => 'Transaction PIN must be exactly 6 digits.',
            'transaction_pin.regex' => 'Transaction PIN must contain only numbers.',
            'country.max' => 'Please select a valid country.',
            'account_type.exists' => 'Please select a valid account type.',
        ]);

        // Validate currency
        if (! Currencies::isValid($validated['currency'])) {
            return back()->withErrors(['currency' => 'Please select a valid currency.']);
        }

        // Validate country
        if (! Countries::exists($validated['country'])) {
            return back()->withErrors(['country' => 'Please select a valid country.']);
        }

        // Create the user
        $user = User::create([
            'uuid' => Str::uuid(),
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone'],
            'address_line_1' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'postal_code' => $validated['postal_code'],
            'country' => $validated['country'],
            'password' => Hash::make($validated['password']),
            'transaction_pin' => Hash::make($validated['transaction_pin']),
            'is_active' => true,
            'kyc_level' => 0,
        ]);

        // Generate referral code for new user
        $user->generateReferralCode();

        // Get the selected account type
        $accountType = AccountType::where('code', $validated['account_type'])->first();

        if ($accountType) {
            BankAccount::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'account_type_id' => $accountType->id,
                'balance' => 0,
                'currency' => $validated['currency'],
                'is_primary' => true,
                'is_active' => true,
                'opened_at' => now(),
            ]);
        }

        // Process referral if a code was provided
        if (! empty($validated['referral_code'])) {
            try {
                $this->referralService->processReferral($user, $validated['referral_code']);
            } catch (\Exception $e) {
                // Log the error but don't fail registration
                \Log::warning('Failed to process referral during registration: '.$e->getMessage(), [
                    'user_id' => $user->id,
                    'referral_code' => $validated['referral_code'],
                ]);
            }
        }

        // Fire the Registered event (sends verification email)
        // Wrapped in try-catch to handle email failures gracefully
        try {
            event(new Registered($user));
        } catch (\Exception $e) {
            // Log the error but don't fail registration
            \Log::warning('Failed to send verification email during registration: '.$e->getMessage());
        }

        // Notify admins about new user registration
        AdminNotificationService::userRegistered($user);

        // Log user registration
        ActivityLogger::logAuth('registered', $user, [
            'ip_address' => $request->ip(),
            'account_type' => $validated['account_type'],
            'currency' => $validated['currency'],
        ]);

        Auth::login($user);

        return redirect(route('dashboard'));
    }
}
