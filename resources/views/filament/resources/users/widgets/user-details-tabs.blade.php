<x-filament-widgets::widget>
    <div x-data="{ activeTab: 'statistics' }">
        <x-filament::tabs>
            {{-- Statistics Tab --}}
            <x-filament::tabs.item
                icon="heroicon-o-chart-bar"
                alpine-active="activeTab === 'statistics'"
                x-on:click="activeTab = 'statistics'"
            >
                {{ $this->getTabLabels()['statistics'] }}
            </x-filament::tabs.item>

            {{-- Information Tab --}}
            <x-filament::tabs.item
                icon="heroicon-o-information-circle"
                alpine-active="activeTab === 'information'"
                x-on:click="activeTab = 'information'"
            >
                {{ $this->getTabLabels()['information'] }}
            </x-filament::tabs.item>

            {{-- Transactions Tab --}}
            <x-filament::tabs.item
                icon="heroicon-o-banknotes"
                alpine-active="activeTab === 'transactions'"
                x-on:click="activeTab = 'transactions'"
            >
                {{ $this->getTabLabels()['transactions'] }}
            </x-filament::tabs.item>

            {{-- Referrals Tab --}}
            <x-filament::tabs.item
                icon="heroicon-o-users"
                alpine-active="activeTab === 'referrals'"
                x-on:click="activeTab = 'referrals'"
            >
                {{ $this->getTabLabels()['referrals'] }}
            </x-filament::tabs.item>

            {{-- Activity Log Tab --}}
            <x-filament::tabs.item
                icon="heroicon-o-clock"
                alpine-active="activeTab === 'activity'"
                x-on:click="activeTab = 'activity'"
            >
                {{ $this->getTabLabels()['activity'] }}
            </x-filament::tabs.item>

            {{-- Security Tab --}}
            <x-filament::tabs.item
                icon="heroicon-o-shield-check"
                alpine-active="activeTab === 'security'"
                x-on:click="activeTab = 'security'"
            >
                {{ $this->getTabLabels()['security'] }}
            </x-filament::tabs.item>
        </x-filament::tabs>

        <div class="mt-6">
        {{-- Statistics Tab Content --}}
        <div x-show="activeTab === 'statistics'" x-cloak>
            {{-- Stats Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                {{-- Send Money (Total All Transfers) --}}
                <x-filament::section class="bg-gradient-to-br from-blue-50 to-blue-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ $this->getStatCardLabels()['send_money'] }}</p>
                            <p class="text-2xl font-bold text-blue-600">
                                ${{ number_format($this->totalTransfersStats()['total_amount'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->totalTransfersStats()['completed'] }} {{ $this->getStatDescriptions()['completed'] }}</p>
                        </div>
                        <div class="p-3 bg-blue-500 rounded-full">
                            <x-heroicon-o-arrow-up-right class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Wire Transfers --}}
                <x-filament::section class="bg-gradient-to-br from-purple-50 to-purple-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ $this->getStatCardLabels()['wire_transfers'] }}</p>
                            <p class="text-2xl font-bold text-purple-600">
                                ${{ number_format($this->wireTransfersStats()['total_amount'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->wireTransfersStats()['completed'] }} {{ $this->getStatDescriptions()['completed'] }}</p>
                        </div>
                        <div class="p-3 bg-purple-500 rounded-full">
                            <x-heroicon-o-globe-alt class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Domestic Transfers --}}
                <x-filament::section class="bg-gradient-to-br from-green-50 to-green-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ $this->getStatCardLabels()['domestic_transfers'] }}</p>
                            <p class="text-2xl font-bold text-green-600">
                                ${{ number_format($this->domesticTransfersStats()['total_amount'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->domesticTransfersStats()['completed'] }} {{ $this->getStatDescriptions()['completed'] }}</p>
                        </div>
                        <div class="p-3 bg-green-500 rounded-full">
                            <x-heroicon-o-home-modern class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- User to User Transfers --}}
                <x-filament::section class="bg-gradient-to-br from-orange-50 to-orange-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ $this->getStatCardLabels()['user_to_user'] }}</p>
                            <p class="text-2xl font-bold text-orange-600">
                                ${{ number_format($this->internalTransfersStats()['total_amount'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->internalTransfersStats()['completed'] }} {{ $this->getStatDescriptions()['completed'] }}</p>
                        </div>
                        <div class="p-3 bg-orange-500 rounded-full">
                            <x-heroicon-o-users class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Total Deposits --}}
                <x-filament::section class="bg-gradient-to-br from-yellow-50 to-yellow-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ $this->getStatCardLabels()['total_deposits'] }}</p>
                            <p class="text-2xl font-bold text-yellow-600">
                                ${{ number_format($this->depositsStats()['total_amount'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->depositsStats()['check'] + $this->depositsStats()['mobile'] + $this->depositsStats()['crypto'] }} {{ $this->getStatDescriptions()['total'] }}</p>
                        </div>
                        <div class="p-3 bg-yellow-500 rounded-full">
                            <x-heroicon-o-arrow-down-tray class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Bank Balance --}}
                <x-filament::section class="bg-gradient-to-br from-red-50 to-red-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ $this->getStatCardLabels()['total_balance'] }}</p>
                            <p class="text-2xl font-bold text-red-600">
                                ${{ number_format($this->totalBalance(), 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $record->bankAccounts()->count() }} {{ $this->getStatDescriptions()['accounts'] }}</p>
                        </div>
                        <div class="p-3 bg-red-500 rounded-full">
                            <x-heroicon-o-wallet class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Total Transactions --}}
                <x-filament::section class="bg-gradient-to-br from-indigo-50 to-indigo-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ $this->getStatCardLabels()['total_transactions'] }}</p>
                            <p class="text-2xl font-bold text-indigo-600">
                                {{ $this->totalTransactions() }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->getStatDescriptions()['all_time'] }}</p>
                        </div>
                        <div class="p-3 bg-indigo-500 rounded-full">
                            <x-heroicon-o-arrow-path class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Pending Tickets --}}
                <x-filament::section class="bg-gradient-to-br from-teal-50 to-teal-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ $this->getStatCardLabels()['pending_tickets'] }}</p>
                            <p class="text-2xl font-bold text-teal-600">
                                {{ $record->supportTickets()->where('status', 'pending')->count() }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->getStatDescriptions()['support_tickets'] }}</p>
                        </div>
                        <div class="p-3 bg-teal-500 rounded-full">
                            <x-heroicon-o-chat-bubble-left-right class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Loans --}}
                <x-filament::section class="bg-gradient-to-br from-cyan-50 to-cyan-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">{{ $this->getStatCardLabels()['loans'] }}</p>
                            <p class="text-2xl font-bold text-cyan-600">
                                ${{ number_format($this->loansStats()['total_amount'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->loansStats()['approved'] }} {{ $this->getStatDescriptions()['approved'] }}</p>
                        </div>
                        <div class="p-3 bg-cyan-500 rounded-full">
                            <x-heroicon-o-document-currency-dollar class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Grants --}}
                <x-filament::section class="bg-gradient-to-br from-rose-50 to-rose-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Grants</p>
                            <p class="text-2xl font-bold text-rose-600">
                                ${{ number_format($this->grantsStats()['total_amount'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->grantsStats()['approved'] }} {{ $this->getStatDescriptions()['approved'] }}</p>
                        </div>
                        <div class="p-3 bg-rose-500 rounded-full">
                            <x-heroicon-o-gift class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Cards --}}
                <x-filament::section class="bg-gradient-to-br from-lime-50 to-lime-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Cards</p>
                            <p class="text-2xl font-bold text-lime-600">
                                {{ $this->cardsStats()['active'] }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->cardsStats()['total'] }} total</p>
                        </div>
                        <div class="p-3 bg-lime-500 rounded-full">
                            <x-heroicon-o-credit-card class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Withdrawals --}}
                <x-filament::section class="bg-gradient-to-br from-amber-50 to-amber-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Withdrawals</p>
                            <p class="text-2xl font-bold text-amber-600">
                                ${{ number_format($this->withdrawalsStats()['total_amount'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->withdrawalsStats()['completed'] }} completed</p>
                        </div>
                        <div class="p-3 bg-amber-500 rounded-full">
                            <x-heroicon-o-arrow-left-on-rectangle class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Vouchers --}}
                <x-filament::section class="bg-gradient-to-br from-violet-50 to-violet-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Vouchers</p>
                            <p class="text-2xl font-bold text-violet-600">
                                ${{ number_format($this->vouchersStats()['total_amount'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->vouchersStats()['used'] }} used</p>
                        </div>
                        <div class="p-3 bg-violet-500 rounded-full">
                            <x-heroicon-o-ticket class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Rewards --}}
                <x-filament::section class="bg-gradient-to-br from-fuchsia-50 to-fuchsia-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Rewards</p>
                            <p class="text-2xl font-bold text-fuchsia-600">
                                {{ $this->rewardsStats()['total_amount'] }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->rewardsStats()['earned'] }} earned</p>
                        </div>
                        <div class="p-3 bg-fuchsia-500 rounded-full">
                            <x-heroicon-o-star class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Beneficiaries --}}
                <x-filament::section class="bg-gradient-to-br from-sky-50 to-sky-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Beneficiaries</p>
                            <p class="text-2xl font-bold text-sky-600">
                                {{ $this->beneficiariesStats()['verified'] }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->beneficiariesStats()['total'] }} total</p>
                        </div>
                        <div class="p-3 bg-sky-500 rounded-full">
                            <x-heroicon-o-user-group class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- IRS Refunds --}}
                <x-filament::section class="bg-gradient-to-br from-emerald-50 to-emerald-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">IRS Refunds</p>
                            <p class="text-2xl font-bold text-emerald-600">
                                ${{ number_format($this->taxRefundsStats()['total_amount'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->taxRefundsStats()['completed'] }} completed, {{ $this->taxRefundsStats()['pending'] }} pending</p>
                        </div>
                        <div class="p-3 bg-emerald-500 rounded-full">
                            <x-heroicon-o-building-library class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Money Requests --}}
                <x-filament::section class="bg-gradient-to-br from-pink-50 to-pink-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Money Requests</p>
                            <p class="text-2xl font-bold text-pink-600">
                                ${{ number_format($this->moneyRequestsStats()['total_amount'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->moneyRequestsStats()['sent'] }} sent, {{ $this->moneyRequestsStats()['received'] }} received</p>
                        </div>
                        <div class="p-3 bg-pink-500 rounded-full">
                            <x-heroicon-o-currency-dollar class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>

                {{-- Currency Exchange --}}
                <x-filament::section class="bg-gradient-to-br from-slate-50 to-slate-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Currency Exchange</p>
                            <p class="text-2xl font-bold text-slate-600">
                                ${{ number_format($this->exchangeMoneyStats()['total_from_amount'], 2) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">{{ $this->exchangeMoneyStats()['completed'] }} completed</p>
                        </div>
                        <div class="p-3 bg-slate-500 rounded-full">
                            <x-heroicon-o-arrows-right-left class="w-6 h-6 text-white" />
                        </div>
                    </div>
                </x-filament::section>
            </div>

            {{-- Transaction Summary Chart (Full Width) --}}
            <div class="mb-6">
            <x-filament::section>
                <x-slot name="heading">
                    Transaction Summary (Last 12 Months)
                </x-slot>

                <div class="mb-4 flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 bg-green-500 rounded-full"></span>
                        <span class="text-sm text-gray-600">Completed</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 bg-yellow-500 rounded-full"></span>
                        <span class="text-sm text-gray-600">Pending</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-3 h-3 bg-red-500 rounded-full"></span>
                        <span class="text-sm text-gray-600">Failed</span>
                    </div>
                </div>

                <div id="transactionChart" class="w-full h-80"></div>

                @push('scripts')
                <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const monthlyStats = @json($this->monthlyTransactionStats());
                        
                        const options = {
                            series: [
                                {
                                    name: 'Completed',
                                    data: monthlyStats.completed
                                },
                                {
                                    name: 'Pending',
                                    data: monthlyStats.pending
                                },
                                {
                                    name: 'Failed',
                                    data: monthlyStats.failed
                                }
                            ],
                            chart: {
                                height: 320,
                                type: 'area',
                                toolbar: {
                                    show: false
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            stroke: {
                                curve: 'smooth',
                                width: 2
                            },
                            colors: ['#10b981', '#f59e0b', '#ef4444'],
                            fill: {
                                type: 'gradient',
                                gradient: {
                                    opacityFrom: 0.6,
                                    opacityTo: 0.1,
                                }
                            },
                            xaxis: {
                                categories: monthlyStats.months
                            },
                            tooltip: {
                                shared: true,
                                intersect: false,
                            }
                        };

                        const chart = new ApexCharts(document.querySelector("#transactionChart"), options);
                        chart.render();
                    });
                </script>
                @endpush
            </x-filament::section>
            </div>
        </div>

        {{-- Information Tab Content --}}
        <div x-show="activeTab === 'information'" x-cloak>
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <span>User Information</span>
                        <x-filament::button color="primary" icon="heroicon-o-pencil-square" x-on:click="$dispatch('open-modal', { id: 'edit-user-info' })">
                            Edit user info
                        </x-filament::button>
                    </div>
                </x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $record->name }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Address</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $record->email }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone Number</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $record->phone_number ?? 'N/A' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date of Birth</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $record->date_of_birth?->format('M d, Y') ?? 'N/A' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Country</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $record->country ?? 'N/A' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">City</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $record->city ?? 'N/A' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $record->address ?? 'N/A' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Postal Code</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $record->postal_code ?? 'N/A' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Created</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $record->created_at->format('M d, Y h:i A') }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Login</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $record->last_login_at?->format('M d, Y h:i A') ?? 'Never' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">KYC Status</dt>
                        <dd>
                            <x-filament::badge :color="$record->kyc_verified_at ? 'success' : 'warning'">
                                {{ $record->kyc_verified_at ? 'Verified' : 'Not Verified' }}
                            </x-filament::badge>
                        </dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Verification</dt>
                        <dd>
                            <x-filament::badge :color="$record->email_verified_at ? 'success' : 'danger'">
                                {{ $record->email_verified_at ? 'Verified' : 'Not Verified' }}
                            </x-filament::badge>
                        </dd>
                    </div>
                </div>
            </x-filament::section>

            {{-- KYC Documents Section --}}
            @if($record->kycVerifications()->exists())
            <x-filament::section class="mt-6">
                <x-slot name="heading">KYC Documents</x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($record->kycVerifications as $document)
                        <div class="p-4 border border-gray-200 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <h5 class="font-semibold">{{ $document->type }}</h5>
                                <x-filament::badge :color="$document->status === 'approved' ? 'success' : ($document->status === 'rejected' ? 'danger' : 'warning')">
                                    {{ ucfirst($document->status) }}
                                </x-filament::badge>
                            </div>
                            <p class="text-sm text-gray-600">{{ $document->document_number }}</p>
                            <p class="text-xs text-gray-500 mt-1">Uploaded: {{ $document->created_at->format('M d, Y') }}</p>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
            @endif
        </div>

        {{-- Security Tab Content --}}
        <div x-show="activeTab === 'security'" x-cloak>
            <x-filament::section>
                <x-slot name="heading">Security controls</x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-filament::section>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Reset password</p>
                                <p class="text-sm text-gray-600">Force a password reset for this user.</p>
                            </div>
                            <x-filament::button color="danger" icon="heroicon-o-key" x-on:click="$dispatch('open-modal', { id: 'reset-password' })">
                                Reset
                            </x-filament::button>
                        </div>
                    </x-filament::section>

                    <x-filament::section>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Force logout</p>
                                <p class="text-sm text-gray-600">Invalidate all active sessions for this user.</p>
                            </div>
                            <x-filament::button color="warning" icon="heroicon-o-arrow-left-on-rectangle" x-on:click="$dispatch('open-modal', { id: 'force-logout' })">
                                Logout
                            </x-filament::button>
                        </div>
                    </x-filament::section>

                    <x-filament::section>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $record->is_active ? 'Lock account' : 'Unlock account' }}</p>
                                <p class="text-sm text-gray-600">
                                    @if($record->is_active)
                                        Temporarily disable sign-in for this user.
                                    @else
                                        <span class="text-danger-600 dark:text-danger-400 font-medium">Account is currently locked.</span> Click to unlock.
                                    @endif
                                </p>
                            </div>
                            <x-filament::button 
                                color="{{ $record->is_active ? 'gray' : 'success' }}" 
                                icon="{{ $record->is_active ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open' }}" 
                                x-on:click="$dispatch('open-modal', { id: 'lock-account' })"
                            >
                                {{ $record->is_active ? 'Lock' : 'Unlock' }}
                            </x-filament::button>
                        </div>
                    </x-filament::section>

                    <x-filament::section>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Two-factor authentication</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    @if($record->two_factor_secret)
                                        <span class="inline-flex items-center gap-1 text-success-600 dark:text-success-400">
                                            <x-heroicon-o-check-circle class="w-4 h-4" />
                                            2FA is enabled
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400">
                                            <x-heroicon-o-x-circle class="w-4 h-4" />
                                            2FA is not enabled
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <x-filament::button color="primary" icon="heroicon-o-shield-check" x-on:click="$dispatch('open-modal', { id: 'two-factor' })">
                                Manage
                            </x-filament::button>
                        </div>
                    </x-filament::section>

                    <x-filament::section>
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Transaction PIN</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    @if($record->transaction_pin)
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-success-50 dark:bg-success-900/30">
                                            <x-heroicon-o-check-circle class="w-4 h-4 text-success-600 dark:text-success-400" />
                                            <span class="text-success-700 dark:text-success-300 text-sm font-medium">PIN is set</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-warning-50 dark:bg-warning-900/30">
                                            <x-heroicon-o-exclamation-circle class="w-4 h-4 text-warning-600 dark:text-warning-400" />
                                            <span class="text-warning-700 dark:text-warning-300 text-sm font-medium">No PIN set</span>
                                        </span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Use the "Manage PIN" button in the header to create or change the PIN</p>
                            </div>
                        </div>
                    </x-filament::section>
                </div>
            </x-filament::section>
        </div>

        {{-- Edit user info modal --}}
        <x-filament::modal id="edit-user-info" width="3xl">
            <x-slot name="heading">Edit User Information</x-slot>

            <form wire:submit="updateUserInformation($event.target.elements)" class="space-y-6">
                {{-- Personal Information Section --}}
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit-first-name" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">First Name *</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-first-name" name="first_name" type="text" value="{{ $record->first_name }}" required />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-last-name" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Last Name *</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-last-name" name="last_name" type="text" value="{{ $record->last_name }}" required />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-email" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Email Address *</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-email" name="email" type="email" value="{{ $record->email }}" required />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-phone" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Phone Number</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-phone" name="phone_number" type="tel" value="{{ $record->phone_number }}" />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-dob" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Date of Birth</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-dob" name="date_of_birth" type="date" value="{{ $record->date_of_birth?->format('Y-m-d') }}" />
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                </div>

                {{-- Address Information Section --}}
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Address Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label for="edit-address1" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Address Line 1</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-address1" name="address_line_1" type="text" value="{{ $record->address_line_1 }}" />
                            </x-filament::input.wrapper>
                        </div>

                        <div class="md:col-span-2">
                            <label for="edit-address2" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Address Line 2</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-address2" name="address_line_2" type="text" value="{{ $record->address_line_2 }}" />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-city" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">City</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-city" name="city" type="text" value="{{ $record->city }}" />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-state" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">State/Province</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-state" name="state" type="text" value="{{ $record->state }}" />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-postal" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Postal Code</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-postal" name="postal_code" type="text" value="{{ $record->postal_code }}" />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-country" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Country</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-country" name="country" type="text" value="{{ $record->country }}" />
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                </div>

                {{-- Account Status Section --}}
                <div class="pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Account Status</h3>
                    <div class="grid grid-cols-1 gap-6">
                        {{-- Account Active Toggle --}}
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex-1">
                                <label for="edit-is-active" class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer">
                                    Account Active
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Inactive accounts cannot login</p>
                            </div>
                            <div>
                                <button
                                    type="button"
                                    role="switch"
                                    aria-checked="{{ $record->is_active ? 'true' : 'false' }}"
                                    x-data="{ isActive: {{ $record->is_active ? 'true' : 'false' }} }"
                                    @click="isActive = !isActive"
                                    :aria-checked="isActive"
                                    :class="isActive ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                    <input type="hidden" name="is_active" :value="isActive ? '1' : '0'" />
                                    <span
                                        :class="isActive ? 'translate-x-6' : 'translate-x-1'"
                                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform">
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- Email Verified Toggle --}}
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <div class="flex-1">
                                <label for="edit-is-verified" class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer">
                                    Email Verified
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Verified accounts have confirmed their email</p>
                            </div>
                            <div>
                                <button
                                    type="button"
                                    role="switch"
                                    aria-checked="{{ $record->is_verified ? 'true' : 'false' }}"
                                    x-data="{ isVerified: {{ $record->is_verified ? 'true' : 'false' }} }"
                                    @click="isVerified = !isVerified"
                                    :aria-checked="isVerified"
                                    :class="isVerified ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                    <input type="hidden" name="is_verified" :value="isVerified ? '1' : '0'" />
                                    <span
                                        :class="isVerified ? 'translate-x-6' : 'translate-x-1'"
                                        class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform">
                                    </span>
                                </button>
                            </div>
                        </div>

                        {{-- KYC Level --}}
                        <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <label for="edit-kyc-level" class="text-sm font-medium text-gray-900 dark:text-white mb-2 block">
                                KYC Level
                            </label>
                            <select 
                                id="edit-kyc-level" 
                                name="kyc_level" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                <option value="0" {{ $record->kyc_level == 0 ? 'selected' : '' }}>Level 0 - Not Verified</option>
                                <option value="1" {{ $record->kyc_level == 1 ? 'selected' : '' }}>Level 1 - Basic Verification</option>
                                <option value="2" {{ $record->kyc_level == 2 ? 'selected' : '' }}>Level 2 - Enhanced Verification</option>
                                <option value="3" {{ $record->kyc_level == 3 ? 'selected' : '' }}>Level 3 - Full Verification</option>
                            </select>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Higher levels unlock more features and higher limits</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-filament::button color="secondary" type="button" x-on:click="$dispatch('close-modal', { id: 'edit-user-info' })">
                        Cancel
                    </x-filament::button>
                    <x-filament::button color="primary" type="submit">
                        Save Changes
                    </x-filament::button>
                </div>
            </form>
        </x-filament::modal>

        {{-- Reset password modal --}}
        <x-filament::modal id="reset-password">
            <x-slot name="heading">Reset password</x-slot>

            <form class="space-y-4" wire:submit.prevent="resetPassword($refs.newPassword.value, $refs.confirmPassword.value)">
                <div>
                    <label for="new-password" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">New password</label>
                    <x-filament::input.wrapper>
                        <x-filament::input x-ref="newPassword" id="new-password" name="password" type="password" required minlength="8" />
                    </x-filament::input.wrapper>
                    <p class="text-xs text-gray-500 mt-1">Minimum 8 characters</p>
                </div>

                <div>
                    <label for="confirm-password" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Confirm password</label>
                    <x-filament::input.wrapper>
                        <x-filament::input x-ref="confirmPassword" id="confirm-password" name="password_confirmation" type="password" required minlength="8" />
                    </x-filament::input.wrapper>
                </div>

                <div class="flex justify-end gap-2">
                    <x-filament::button color="secondary" type="button" x-on:click="$dispatch('close-modal', { id: 'reset-password' })">Cancel</x-filament::button>
                    <x-filament::button color="danger" type="submit">Reset password</x-filament::button>
                </div>
            </form>
        </x-filament::modal>

        {{-- Force logout modal --}}
        <x-filament::modal id="force-logout">
            <x-slot name="heading">Force logout</x-slot>

            <div class="space-y-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">This will invalidate all active sessions for <strong>{{ $record->email }}</strong>.</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">The user will be logged out from all devices and will need to sign in again.</p>
                <div class="flex justify-end gap-2">
                    <x-filament::button color="secondary" type="button" x-on:click="$dispatch('close-modal', { id: 'force-logout' })">Cancel</x-filament::button>
                    <x-filament::button color="warning" wire:click="forceLogout">Force logout</x-filament::button>
                </div>
            </div>
        </x-filament::modal>

        {{-- Lock account modal --}}
        <x-filament::modal id="lock-account">
            <x-slot name="heading">{{ $record->is_active ? 'Lock account' : 'Unlock account' }}</x-slot>

            <div class="space-y-4">
                @if($record->is_active)
                    <p class="text-sm text-gray-600 dark:text-gray-400">This will prevent <strong>{{ $record->email }}</strong> from signing in until unlocked by an admin.</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">The user will also be logged out from all active sessions.</p>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-400">This will allow <strong>{{ $record->email }}</strong> to sign in again.</p>
                @endif
                <div class="flex justify-end gap-2">
                    <x-filament::button color="secondary" type="button" x-on:click="$dispatch('close-modal', { id: 'lock-account' })">Cancel</x-filament::button>
                    <x-filament::button color="{{ $record->is_active ? 'danger' : 'success' }}" wire:click="toggleAccountLock">
                        {{ $record->is_active ? 'Lock account' : 'Unlock account' }}
                    </x-filament::button>
                </div>
            </div>
        </x-filament::modal>

        {{-- Two-factor modal --}}
        <x-filament::modal id="two-factor">
            <x-slot name="heading">Two-factor authentication</x-slot>

            <div class="space-y-4">
                <div class="flex items-center gap-3 p-4 rounded-lg {{ $record->two_factor_secret ? 'bg-success-50 dark:bg-success-900/20' : 'bg-gray-50 dark:bg-gray-800' }}">
                    @if($record->two_factor_secret)
                        <x-heroicon-o-shield-check class="w-8 h-8 text-success-600 dark:text-success-400" />
                        <div>
                            <p class="font-semibold text-success-700 dark:text-success-300">2FA is Enabled</p>
                            <p class="text-sm text-success-600 dark:text-success-400">This account has two-factor authentication enabled.</p>
                        </div>
                    @else
                        <x-heroicon-o-shield-exclamation class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                        <div>
                            <p class="font-semibold text-gray-700 dark:text-gray-300">2FA is Disabled</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">This account does not have two-factor authentication enabled.</p>
                        </div>
                    @endif
                </div>

                @if($record->two_factor_secret)
                    <div class="p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg">
                        <p class="text-sm text-warning-700 dark:text-warning-300">
                            <strong>Warning:</strong> Resetting 2FA will disable two-factor authentication for this user. They will need to set it up again from their account settings.
                        </p>
                    </div>
                @endif

                <div class="flex justify-end gap-2">
                    <x-filament::button color="secondary" type="button" x-on:click="$dispatch('close-modal', { id: 'two-factor' })">Close</x-filament::button>
                    @if($record->two_factor_secret)
                        <x-filament::button color="danger" wire:click="resetTwoFactor">Reset 2FA</x-filament::button>
                    @endif
                </div>
            </div>
        </x-filament::modal>

        {{-- Transactions Tab Content --}}
        <div x-show="activeTab === 'transactions'" x-cloak>
            <x-filament::section>
                <x-slot name="heading">Recent Transactions</x-slot>

                @php
                    // Get transaction histories (admin-generated transactions)
                    $transactionHistories = $record->transactionHistories()->latest()->take(30)->get();
                    
                    // Combine all transfers into one collection
                    $allTransactions = collect([
                        ...$transactionHistories,
                        ...$record->wireTransfers()->latest()->take(10)->get(),
                        ...$record->sentInternalTransfers()->latest()->take(5)->get(),
                        ...$record->domesticTransfers()->latest()->take(5)->get(),
                    ])->sortByDesc('created_at')->take(30);
                @endphp

                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 bg-white text-start dark:divide-white/5 dark:bg-gray-900">
                        <thead class="divide-y divide-gray-200 dark:divide-white/5">
                            <tr class="bg-gray-50 dark:bg-gray-800">
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                    <span class="text-sm font-semibold text-gray-950 dark:text-white">Date</span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5">
                                    <span class="text-sm font-semibold text-gray-950 dark:text-white">Type</span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5">
                                    <span class="text-sm font-semibold text-gray-950 dark:text-white">Reference</span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5">
                                    <span class="text-sm font-semibold text-gray-950 dark:text-white">Amount</span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:last-of-type:pe-6">
                                    <span class="text-sm font-semibold text-gray-950 dark:text-white">Status</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                            @forelse($allTransactions as $transaction)
                                @php
                                    $isTransactionHistory = $transaction instanceof \App\Models\TransactionHistory;

                                    $transactionTypeValue = null;
                                    if ($isTransactionHistory) {
                                        $rawType = $transaction->transaction_type;
                                        $transactionTypeValue = $rawType instanceof \BackedEnum ? $rawType->value : (string) $rawType;
                                        $transactionType = $rawType instanceof \App\Enums\TransactionType
                                            ? $rawType->label()
                                            : ucfirst(str_replace('_', ' ', $transactionTypeValue));
                                    } else {
                                        $transactionType = ucfirst(str_replace('_', ' ', class_basename($transaction)));
                                        $transactionTypeValue = $transactionType;
                                    }
                                    
                                    // Determine if it's a debit or credit
                                    $isCredit = $isTransactionHistory && in_array($transactionTypeValue, ['credit', 'deposit', 'refund'], true);
                                    $isDebit = $isTransactionHistory && in_array($transactionTypeValue, ['debit', 'withdrawal'], true);
                                    
                                    // Format amount
                                    if ($isTransactionHistory) {
                                        $amountValue = $transaction->amount;
                                        $sign = $isCredit ? '+' : ($isDebit ? '-' : '');
                                        $colorClass = $isCredit ? 'text-success-600 dark:text-success-400' : ($isDebit ? 'text-danger-600 dark:text-danger-400' : 'text-gray-600 dark:text-gray-400');
                                    } else {
                                        $amountValue = $transaction->amount / 100;
                                        $sign = '-';
                                        $colorClass = 'text-danger-600 dark:text-danger-400';
                                    }
                                @endphp
                                <tr class="fi-ta-row bg-white transition duration-75 hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-white/5">
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                <div class="flex">
                                                    <div class="flex max-w-max">
                                                        <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-sm text-gray-950 dark:text-white">
                                                            {{ $transaction->created_at->format('M d, Y') }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="fi-ta-text grid w-full gap-y-1">
                                                <div class="flex">
                                                    <div class="flex max-w-max">
                                                        <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-sm text-gray-950 dark:text-white">
                                                            {{ $transactionType }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="fi-ta-text grid w-full gap-y-1">
                                                <div class="flex">
                                                    <div class="flex max-w-max">
                                                        <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                                                            {{ $transaction->reference_number ?? 'N/A' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="fi-ta-text grid w-full gap-y-1">
                                                <div class="flex">
                                                    <div class="flex max-w-max">
                                                        <div class="fi-ta-text-item inline-flex items-center gap-1.5 text-sm font-semibold {{ $colorClass }}">
                                                            {{ $sign }}${{ number_format($amountValue, 2) }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0">
                                        <div class="fi-ta-col-wrp px-3 py-4">
                                            <div class="fi-ta-text grid w-full gap-y-1">
                                                <div class="flex">
                                                    <div class="flex max-w-max">
                                                        <x-filament::badge
                                                            :color="($transaction->status->value ?? $transaction->status) === 'completed' ? 'success' : (($transaction->status->value ?? $transaction->status) === 'failed' ? 'danger' : 'warning')">
                                                            {{ ucfirst($transaction->status->value ?? $transaction->status) }}
                                                        </x-filament::badge>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="fi-ta-empty-state px-6 py-12">
                                            <div class="fi-ta-empty-state-content mx-auto grid max-w-lg justify-items-center text-center">
                                                <div class="fi-ta-empty-state-icon-ctn mb-4 rounded-full bg-gray-100 p-3 dark:bg-gray-700/50">
                                                    <x-filament::icon
                                                        icon="heroicon-o-x-mark"
                                                        class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400"
                                                    />
                                                </div>
                                                <h4 class="fi-ta-empty-state-heading text-base font-semibold text-gray-950 dark:text-white">
                                                    No transactions found
                                                </h4>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>

        {{-- Referrals Tab Content --}}
        <div x-show="activeTab === 'referrals'" x-cloak>
            <x-filament::section>
                <x-slot name="heading">Referral Information</x-slot>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <p class="text-sm text-gray-600">Referral Code</p>
                        <p class="text-xl font-bold text-blue-600">{{ $record->referral_code ?? 'N/A' }}</p>
                    </div>

                    <div class="p-4 bg-green-50 rounded-lg">
                        <p class="text-sm text-gray-600">Total Referrals</p>
                        <p class="text-xl font-bold text-green-600">0</p>
                    </div>

                    <div class="p-4 bg-purple-50 rounded-lg">
                        <p class="text-sm text-gray-600">Referral Earnings</p>
                        <p class="text-xl font-bold text-purple-600">$0.00</p>
                    </div>
                </div>

                <p class="text-center text-gray-500 py-8">Referral system coming soon</p>
            </x-filament::section>
        </div>

        {{-- Activity Log Tab Content --}}
        <div x-show="activeTab === 'activity'" x-cloak>
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <span>Activity Log</span>
                        <div class="text-sm text-gray-500">
                            Total: {{ $record->activityLogs()->count() }} activities
                        </div>
                    </div>
                </x-slot>

                @php
                    $activityLogs = $record->activityLogs()->latest()->paginate(50);
                    $groupedLogs = $activityLogs->groupBy(function($log) {
                        return $log->created_at->format('Y-m-d');
                    });
                @endphp

                <div class="space-y-6">
                    @forelse($groupedLogs as $date => $logs)
                        <div>
                            <div class="flex items-center gap-2 mb-4">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($date)->format('F d, Y') }}
                                </h4>
                                <span class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">
                                    {{ count($logs) }} activities
                                </span>
                            </div>

                            <div class="space-y-2">
                                @foreach($logs as $log)
                                    @php
                                        $actionType = explode('.', $log->action)[0] ?? 'other';
                                        $iconClass = match($actionType) {
                                            'auth' => 'heroicon-o-lock-closed',
                                            'transaction' => 'heroicon-o-banknotes',
                                            'security' => 'heroicon-o-shield-exclamation',
                                            'account' => 'heroicon-o-user',
                                            'loan' => 'heroicon-o-document-text',
                                            'card' => 'heroicon-o-credit-card',
                                            'grant' => 'heroicon-o-gift',
                                            'support' => 'heroicon-o-chat-bubble-left-right',
                                            'admin' => 'heroicon-o-cog-6-tooth',
                                            default => 'heroicon-o-information-circle',
                                        };
                                        $bgClass = match($actionType) {
                                            'auth' => 'bg-blue-100 dark:bg-blue-900',
                                            'transaction' => 'bg-green-100 dark:bg-green-900',
                                            'security' => 'bg-red-100 dark:bg-red-900',
                                            'account' => 'bg-purple-100 dark:bg-purple-900',
                                            'loan' => 'bg-yellow-100 dark:bg-yellow-900',
                                            'card' => 'bg-indigo-100 dark:bg-indigo-900',
                                            'grant' => 'bg-pink-100 dark:bg-pink-900',
                                            'support' => 'bg-orange-100 dark:bg-orange-900',
                                            'admin' => 'bg-gray-100 dark:bg-gray-800',
                                            default => 'bg-gray-100 dark:bg-gray-800',
                                        };
                                        $iconColor = match($actionType) {
                                            'auth' => 'text-blue-600 dark:text-blue-400',
                                            'transaction' => 'text-green-600 dark:text-green-400',
                                            'security' => 'text-red-600 dark:text-red-400',
                                            'account' => 'text-purple-600 dark:text-purple-400',
                                            'loan' => 'text-yellow-600 dark:text-yellow-400',
                                            'card' => 'text-indigo-600 dark:text-indigo-400',
                                            'grant' => 'text-pink-600 dark:text-pink-400',
                                            'support' => 'text-orange-600 dark:text-orange-400',
                                            'admin' => 'text-gray-600 dark:text-gray-400',
                                            default => 'text-gray-600 dark:text-gray-400',
                                        };
                                    @endphp

                                    <div class="flex items-start gap-4 p-4 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow">
                                        <div class="shrink-0">
                                            <div class="w-10 h-10 {{ $bgClass }} rounded-full flex items-center justify-center">
                                                <x-dynamic-component :component="$iconClass" class="w-5 h-5 {{ $iconColor }}" />
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-3 mb-1">
                                                <p class="font-medium text-gray-900 dark:text-white">{{ $log->description }}</p>
                                                <span class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                                    {{ $log->created_at->format('h:i A') }}
                                                </span>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mt-2">
                                                @if($log->ip_address)
                                                    <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">
                                                        <x-heroicon-o-globe-alt class="w-3 h-3" />
                                                        {{ $log->ip_address }}
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">
                                                    <x-heroicon-o-tag class="w-3 h-3" />
                                                    {{ ucfirst($actionType) }}
                                                </span>
                                                @if($log->subject_type)
                                                    <span class="inline-flex items-center gap-1 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">
                                                        <x-heroicon-o-link class="w-3 h-3" />
                                                        {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($log->metadata && count($log->metadata) > 0)
                                                <details class="mt-2">
                                                    <summary class="text-xs text-primary-600 dark:text-primary-400 cursor-pointer hover:underline">
                                                        View details
                                                    </summary>
                                                    <div class="mt-2 p-2 bg-gray-50 dark:bg-gray-800 rounded text-xs">
                                                        <pre class="text-gray-700 dark:text-gray-300 overflow-x-auto">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                </details>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                                <x-heroicon-o-clock class="w-8 h-8 text-gray-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No activity logs yet</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">User activities will appear here when they start using the platform</p>
                        </div>
                    @endforelse

                    @if($activityLogs->hasPages())
                        <div class="mt-6">
                            {{ $activityLogs->links() }}
                        </div>
                    @endif
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-widgets::widget>
