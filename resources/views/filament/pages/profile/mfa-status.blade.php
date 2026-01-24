<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- App Authentication Status --}}
        <div class="p-4 rounded-lg border {{ $hasAppAuth ? 'bg-success-50 dark:bg-success-950 border-success-200 dark:border-success-800' : 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700' }}">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    @if($hasAppAuth)
                        <x-heroicon-o-check-circle class="w-8 h-8 text-success-500" />
                    @else
                        <x-heroicon-o-device-phone-mobile class="w-8 h-8 text-gray-400" />
                    @endif
                </div>
                <div>
                    <h4 class="font-medium {{ $hasAppAuth ? 'text-success-700 dark:text-success-300' : 'text-gray-700 dark:text-gray-300' }}">
                        Authenticator App
                    </h4>
                    <p class="text-sm {{ $hasAppAuth ? 'text-success-600 dark:text-success-400' : 'text-gray-500 dark:text-gray-400' }}">
                        @if($hasAppAuth)
                            ✓ Configured - Use Google Authenticator, Authy, or similar apps
                        @else
                            Not configured - Scroll down to enable
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Email Authentication Status --}}
        <div class="p-4 rounded-lg border {{ $hasEmailAuth ? 'bg-success-50 dark:bg-success-950 border-success-200 dark:border-success-800' : 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700' }}">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    @if($hasEmailAuth)
                        <x-heroicon-o-check-circle class="w-8 h-8 text-success-500" />
                    @else
                        <x-heroicon-o-envelope class="w-8 h-8 text-gray-400" />
                    @endif
                </div>
                <div>
                    <h4 class="font-medium {{ $hasEmailAuth ? 'text-success-700 dark:text-success-300' : 'text-gray-700 dark:text-gray-300' }}">
                        Email Authentication
                    </h4>
                    <p class="text-sm {{ $hasEmailAuth ? 'text-success-600 dark:text-success-400' : 'text-gray-500 dark:text-gray-400' }}">
                        @if($hasEmailAuth)
                            ✓ Configured - One-time codes will be sent to your email
                        @else
                            Not configured - Scroll down to enable
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if(!$hasAppAuth && !$hasEmailAuth)
        <div class="p-4 rounded-lg bg-warning-50 dark:bg-warning-950 border border-warning-200 dark:border-warning-800">
            <div class="flex items-start gap-3">
                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-warning-500 flex-shrink-0 mt-0.5" />
                <div>
                    <h4 class="font-medium text-warning-700 dark:text-warning-300">
                        Enhance Your Account Security
                    </h4>
                    <p class="text-sm text-warning-600 dark:text-warning-400 mt-1">
                        We recommend enabling at least one MFA method. Scroll down to the "Multi-Factor Authentication"
                        section to configure your preferred authentication method.
                    </p>
                </div>
            </div>
        </div>
    @else
        <div class="p-4 rounded-lg bg-success-50 dark:bg-success-950 border border-success-200 dark:border-success-800">
            <div class="flex items-start gap-3">
                <x-heroicon-o-shield-check class="w-5 h-5 text-success-500 flex-shrink-0 mt-0.5" />
                <div>
                    <h4 class="font-medium text-success-700 dark:text-success-300">
                        Your Account is Protected
                    </h4>
                    <p class="text-sm text-success-600 dark:text-success-400 mt-1">
                        Multi-factor authentication is enabled. You will be prompted to verify your identity
                        using your configured method(s) when signing in.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
