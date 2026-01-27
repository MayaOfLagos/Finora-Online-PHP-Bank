@php
    $recaptchaConfig = $this->getRecaptchaConfig();
    $enabled = $recaptchaConfig['enabled'] ?? false;
    $siteKey = $recaptchaConfig['siteKey'] ?? '';
    $version = $recaptchaConfig['version'] ?? 'v2';
@endphp

<x-filament-panels::page.simple>
    @if ($enabled && $siteKey)
        @if ($version === 'v2')
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        @else
            <script src="https://www.google.com/recaptcha/api.js?render={{ $siteKey }}"></script>
        @endif
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        @if ($enabled && $siteKey)
            <div class="recaptcha-container my-4" wire:ignore>
                @if ($version === 'v2')
                    {{-- reCAPTCHA v2 Widget --}}
                    <div class="flex justify-center">
                        <div 
                            class="g-recaptcha" 
                            data-sitekey="{{ $siteKey }}"
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
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const form = document.getElementById('form');
                            if (form) {
                                form.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    
                                    grecaptcha.ready(function() {
                                        grecaptcha.execute('{{ $siteKey }}', {action: 'admin_login'}).then(function(token) {
                                            @this.set('recaptchaToken', token);
                                            @this.call('authenticate');
                                        });
                                    });
                                });
                            }
                        });
                    </script>
                @endif
            </div>
            
            {{-- Protected by reCAPTCHA notice --}}
            <div class="text-center text-xs text-gray-500 dark:text-gray-400">
                Protected by reCAPTCHA - 
                <a href="https://policies.google.com/privacy" target="_blank" class="text-primary-600 hover:underline">Privacy</a>
                &
                <a href="https://policies.google.com/terms" target="_blank" class="text-primary-600 hover:underline">Terms</a>
            </div>
        @endif

        <x-filament::button type="submit" class="w-full">
            Sign in
        </x-filament::button>
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
</x-filament-panels::page.simple>
