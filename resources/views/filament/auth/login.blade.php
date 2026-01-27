<x-filament-panels::page.simple>
    @php
        $recaptchaConfig = $this->getRecaptchaConfig();
    @endphp

    @if ($recaptchaConfig['enabled'])
        @if ($recaptchaConfig['version'] === 'v2')
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        @else
            <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaConfig['siteKey'] }}"></script>
        @endif
    @endif

    <x-filament-panels::form wire:submit="authenticate">
        {{ $this->form }}

        @if ($recaptchaConfig['enabled'])
            <div class="mt-4">
                @if ($recaptchaConfig['version'] === 'v2')
                    {{-- reCAPTCHA v2 Widget --}}
                    <div class="flex justify-center">
                        <div 
                            class="g-recaptcha" 
                            data-sitekey="{{ $recaptchaConfig['siteKey'] }}"
                            data-callback="onRecaptchaSuccess"
                            data-expired-callback="onRecaptchaExpired"
                            data-theme="light"
                        ></div>
                    </div>
                    
                    <script>
                        function onRecaptchaSuccess(token) {
                            @this.set('recaptchaToken', token);
                        }
                        
                        function onRecaptchaExpired() {
                            @this.set('recaptchaToken', null);
                        }
                        
                        // Listen for reset events
                        document.addEventListener('livewire:initialized', () => {
                            Livewire.on('recaptcha-reset', () => {
                                if (typeof grecaptcha !== 'undefined') {
                                    grecaptcha.reset();
                                }
                            });
                        });
                    </script>
                @else
                    {{-- reCAPTCHA v3 (invisible) --}}
                    <input type="hidden" wire:model="recaptchaToken" id="recaptcha-token">
                    
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Execute reCAPTCHA v3 on form submit
                            const form = document.querySelector('form');
                            if (form) {
                                form.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    
                                    grecaptcha.ready(function() {
                                        grecaptcha.execute('{{ $recaptchaConfig['siteKey'] }}', {action: 'admin_login'}).then(function(token) {
                                            @this.set('recaptchaToken', token);
                                            // Allow Livewire to handle the submit
                                            @this.call('authenticate');
                                        });
                                    });
                                });
                            }
                        });
                        
                        // Listen for reset events
                        document.addEventListener('livewire:initialized', () => {
                            Livewire.on('recaptcha-reset', () => {
                                // v3 doesn't need manual reset, it will get new token on next submit
                            });
                        });
                    </script>
                @endif
            </div>
        @endif

        <x-filament::button type="submit" class="w-full">
            {{ __('filament-panels::pages/auth/login.form.actions.authenticate.label') }}
        </x-filament::button>
    </x-filament-panels::form>

    {{-- Protected by reCAPTCHA notice --}}
    @if ($recaptchaConfig['enabled'])
        <div class="mt-4 text-center text-xs text-gray-500 dark:text-gray-400">
            This site is protected by reCAPTCHA and the Google
            <a href="https://policies.google.com/privacy" target="_blank" class="text-primary-600 hover:underline">Privacy Policy</a>
            and
            <a href="https://policies.google.com/terms" target="_blank" class="text-primary-600 hover:underline">Terms of Service</a>
            apply.
        </div>
    @endif
</x-filament-panels::page.simple>
