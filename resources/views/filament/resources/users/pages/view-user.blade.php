<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Left Sidebar: User Profile Card --}}
        <div class="lg:col-span-4">
            <x-filament::section>
                <div class="space-y-6">

                    {{-- User Avatar & Basic Info --}}
                    <div class="flex flex-col items-center text-center">
                        <div class="relative">
                            @if($record->profile_photo_path)
                                <img src="{{ Storage::url($record->profile_photo_path) }}"
                                     alt="{{ $record->name }}"
                                     class="w-32 h-32 rounded-full object-cover ring-4 ring-primary-500/20">
                            @else
                                <div class="w-32 h-32 rounded-full bg-primary-500 flex items-center justify-center text-white text-4xl font-bold ring-4 ring-primary-500/20">
                                    {{ substr($record->first_name, 0, 1) }}{{ substr($record->last_name, 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <h3 class="mt-4 text-xl font-semibold text-gray-900 dark:text-white">
                            {{ $record->name }}
                        </h3>

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $record->country }}
                        </p>

                        <div class="mt-2 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                            <div>
                                Last Login: {{ $record->loginHistories()->latest()->first()?->created_at->format('Y-m-d H:i') ?? 'Never' }}
                            </div>
                            <div>
                                Browser: Chrome {{-- TODO: Get from login history --}}
                            </div>
                        </div>
                    </div>

                    {{-- User Accounts/Wallets --}}
                    <div class="space-y-2">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <x-heroicon-o-wallet class="w-4 h-4"/>
                            Bank Accounts
                        </h4>

                        @forelse($record->bankAccounts as $account)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary-100 dark:bg-primary-900 rounded-lg flex items-center justify-center">
                                        <x-heroicon-o-banknotes class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-sm text-gray-900 dark:text-white">
                                            {{ $account->accountType->name }}
                                            @if($account->is_primary)
                                                <span class="ml-1 text-xs px-2 py-0.5 bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300 rounded-full">
                                                    Default
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $account->account_number }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-gray-900 dark:text-white">
                                        ${{ number_format($account->balance / 100, 2) }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-sm text-gray-500 dark:text-gray-400 py-4">
                                <p>No bank accounts yet</p>
                                <p class="text-xs mt-2">Use the "Create Bank Account" button in the header to add one (max 2)</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- User Controls --}}
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2 mb-3">
                            <x-heroicon-o-lock-closed class="w-4 h-4"/>
                            User Controls
                        </h4>

                        <div class="space-y-2 max-h-96 overflow-y-auto pr-2">
                            {{-- Can Transfer --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-1">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">Transfer Money</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Allow user to transfer funds</div>
                                </div>
                                <div>
                                    <button
                                        type="button"
                                        wire:click="togglePermission('can_transfer')"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $record->can_transfer ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                        <span class="sr-only">Toggle transfer permission</span>
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $record->can_transfer ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>
                            </div>

                            {{-- Can Withdraw --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-1">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">Withdraw Funds</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Allow user to withdraw money</div>
                                </div>
                                <div>
                                    <button
                                        type="button"
                                        wire:click="togglePermission('can_withdraw')"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $record->can_withdraw ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                        <span class="sr-only">Toggle withdraw permission</span>
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $record->can_withdraw ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>
                            </div>

                            {{-- Can Deposit --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-1">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">Deposit Funds</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Allow user to deposit money</div>
                                </div>
                                <div>
                                    <button
                                        type="button"
                                        wire:click="togglePermission('can_deposit')"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $record->can_deposit ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                        <span class="sr-only">Toggle deposit permission</span>
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $record->can_deposit ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>
                            </div>

                            {{-- Can Request Loan --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-1">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">Request Loans</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Allow user to apply for loans</div>
                                </div>
                                <div>
                                    <button
                                        type="button"
                                        wire:click="togglePermission('can_request_loan')"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $record->can_request_loan ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                        <span class="sr-only">Toggle loan request permission</span>
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $record->can_request_loan ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>
                            </div>

                            {{-- Can Request Card --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-1">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">Request Cards</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Allow user to request debit/credit cards</div>
                                </div>
                                <div>
                                    <button
                                        type="button"
                                        wire:click="togglePermission('can_request_card')"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $record->can_request_card ? 'bg-primary-600' : 'bg-gray-200 dark:border-gray-700' }}">
                                        <span class="sr-only">Toggle card request permission</span>
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $record->can_request_card ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>
                            </div>

                            {{-- Can Apply Grant --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-1">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">Apply for Grants</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Allow user to apply for grant programs</div>
                                </div>
                                <div>
                                    <button
                                        type="button"
                                        wire:click="togglePermission('can_apply_grant')"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $record->can_apply_grant ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                        <span class="sr-only">Toggle grant application permission</span>
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $record->can_apply_grant ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>
                            </div>

                            {{-- Can Send Wire Transfer --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-1">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">Wire Transfers</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Allow international wire transfers</div>
                                </div>
                                <div>
                                    <button
                                        type="button"
                                        wire:click="togglePermission('can_send_wire_transfer')"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $record->can_send_wire_transfer ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                        <span class="sr-only">Toggle wire transfer permission</span>
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $record->can_send_wire_transfer ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>
                            </div>

                            {{-- Can Send Internal Transfer --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-1">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">Internal Transfers</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Allow transfers within bank</div>
                                </div>
                                <div>
                                    <button
                                        type="button"
                                        wire:click="togglePermission('can_send_internal_transfer')"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $record->can_send_internal_transfer ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                        <span class="sr-only">Toggle internal transfer permission</span>
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $record->can_send_internal_transfer ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>
                            </div>

                            {{-- Can Send Domestic Transfer --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-1">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">Domestic Transfers</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Allow local bank transfers</div>
                                </div>
                                <div>
                                    <button
                                        type="button"
                                        wire:click="togglePermission('can_send_domestic_transfer')"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $record->can_send_domestic_transfer ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                        <span class="sr-only">Toggle domestic transfer permission</span>
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $record->can_send_domestic_transfer ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>
                            </div>

                            {{-- Can Create Beneficiary --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex-1">
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">Manage Beneficiaries</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Allow user to add beneficiaries</div>
                                </div>
                                <div>
                                    <button
                                        type="button"
                                        wire:click="togglePermission('can_create_beneficiary')"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $record->can_create_beneficiary ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700' }}">
                                        <span class="sr-only">Toggle beneficiary permission</span>
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $record->can_create_beneficiary ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                    </button>
                                </div>
                            </div>

                            {{-- Account Status (readonly display) --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 mt-4">
                                <div>
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">Account Status</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Overall account status</div>
                                </div>
                                <div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $record->is_active ? 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300' : 'bg-danger-100 text-danger-700 dark:bg-danger-900 dark:text-danger-300' }}">
                                        {{ $record->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>

                            {{-- Email Verification (readonly display) --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">Email Verification</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Email verification status</div>
                                </div>
                                <div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $record->email_verified_at ? 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300' : 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300' }}">
                                        {{ $record->email_verified_at ? 'Verified' : 'Not Verified' }}
                                    </span>
                                </div>
                            </div>

                            {{-- KYC Verification (readonly display) --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">KYC Verification</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Level: {{ $record->kyc_level }}</div>
                                </div>
                                <div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $record->is_verified ? 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300' : 'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300' }}">
                                        {{ $record->is_verified ? 'Verified' : 'Pending' }}
                                    </span>
                                </div>
                            </div>

                            {{-- 2FA Status (readonly display) --}}
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="font-medium text-sm text-gray-900 dark:text-white">Two-Factor Auth</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">2FA security status</div>
                                </div>
                                <div>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $record->two_factor_enabled ? 'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300' }}">
                                        {{ $record->two_factor_enabled ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </x-filament::section>
        </div>

        {{-- Right Content Area: Tabs --}}
        <div class="lg:col-span-8">
            @livewire(\App\Filament\Resources\Users\Widgets\UserDetailsTabs::class, ['record' => $record])
        </div>

    </div>
</x-filament-panels::page>
