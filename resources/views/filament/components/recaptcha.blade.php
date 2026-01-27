@php
    $config = $config ?? [];
    $enabled = $config['enabled'] ?? false;
    $siteKey = $config['siteKey'] ?? '';
    $version = $config['version'] ?? 'v2';
@endphp

@if ($enabled && $siteKey)
    <div class="recaptcha-container" wire:ignore>
        @if ($version === 'v2')
            {{-- reCAPTCHA v2 Widget --}}
            <div class="flex justify-center my-4">
                <div 
                    class="g-recaptcha" 
                    data-sitekey="{{ $siteKey }}"
                    data-callback="onRecaptchaSuccess"
                    data-expired-callback="onRecaptchaExpired"
                    data-theme="light"
                ></div>
            </div>
            
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
            <script src="https://www.google.com/recaptcha/api.js?render={{ $siteKey }}"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const form = document.querySelector('form');
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
    <div class="text-center text-xs text-gray-500 dark:text-gray-400 mt-2">
        Protected by reCAPTCHA - 
        <a href="https://policies.google.com/privacy" target="_blank" class="text-primary-600 hover:underline">Privacy</a>
        &
        <a href="https://policies.google.com/terms" target="_blank" class="text-primary-600 hover:underline">Terms</a>
    </div>
@endif
