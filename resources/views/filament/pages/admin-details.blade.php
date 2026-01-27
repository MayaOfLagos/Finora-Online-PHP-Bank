<div class="space-y-6">
    {{-- Admin Profile Header --}}
    <div class="flex items-center gap-6 bg-gray-50 dark:bg-gray-800 rounded-lg p-6">
        <img src="{{ $admin->avatar_url }}" 
             alt="{{ $admin->full_name }}" 
             class="w-24 h-24 rounded-full ring-4 ring-white dark:ring-gray-700 shadow-lg">
        <div class="flex-1">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $admin->full_name }}</h2>
            <p class="text-gray-500 dark:text-gray-400">{{ $admin->email }}</p>
            <div class="flex items-center gap-3 mt-3">
                <x-filament::badge :color="$admin->role?->color() ?? 'gray'">
                    {{ $admin->role?->label() ?? 'Unknown' }}
                </x-filament::badge>
                <x-filament::badge :color="$admin->is_active ? 'success' : 'danger'">
                    {{ $admin->is_active ? 'Active' : 'Inactive' }}
                </x-filament::badge>
            </div>
        </div>
    </div>

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Personal Information --}}
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <x-heroicon-o-user class="w-5 h-5 text-primary-500"/>
                Personal Information
            </h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">First Name</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $admin->first_name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Last Name</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $admin->last_name }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $admin->email }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Phone</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $admin->phone_number ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Account Information --}}
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <x-heroicon-o-shield-check class="w-5 h-5 text-primary-500"/>
                Account Information
            </h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Role</dt>
                    <dd>
                        <x-filament::badge :color="$admin->role?->color() ?? 'gray'" size="sm">
                            {{ $admin->role?->label() ?? 'Unknown' }}
                        </x-filament::badge>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Status</dt>
                    <dd>
                        <x-filament::badge :color="$admin->is_active ? 'success' : 'danger'" size="sm">
                            {{ $admin->is_active ? 'Active' : 'Inactive' }}
                        </x-filament::badge>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Email Verified</dt>
                    <dd>
                        <x-filament::badge :color="$admin->email_verified_at ? 'success' : 'warning'" size="sm">
                            {{ $admin->email_verified_at ? 'Verified' : 'Not Verified' }}
                        </x-filament::badge>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">2FA Enabled</dt>
                    <dd>
                        <x-filament::badge :color="$admin->two_factor_secret ? 'success' : 'gray'" size="sm">
                            {{ $admin->two_factor_secret ? 'Enabled' : 'Disabled' }}
                        </x-filament::badge>
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Activity Information --}}
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <x-heroicon-o-clock class="w-5 h-5 text-primary-500"/>
                Activity
            </h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Account Created</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $admin->created_at->format('M j, Y') }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Last Updated</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $admin->updated_at->format('M j, Y') }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Last Login</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $admin->last_login_at ? $admin->last_login_at->format('M j, Y g:i A') : 'Never' }}
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Last Login IP</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $admin->last_login_ip ?? '—' }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Security Information --}}
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-5">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <x-heroicon-o-lock-closed class="w-5 h-5 text-primary-500"/>
                Security
            </h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Admin ID</dt>
                    <dd class="text-sm font-mono font-medium text-gray-900 dark:text-white">{{ $admin->id }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Has Transaction PIN</dt>
                    <dd>
                        <x-filament::badge :color="$admin->pin_hash ? 'success' : 'gray'" size="sm">
                            {{ $admin->pin_hash ? 'Yes' : 'No' }}
                        </x-filament::badge>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Password Last Changed</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $admin->password_changed_at ?? 'Unknown' }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</div>
