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
                            <p class="text-xs text-gray-500 mt-1">{{ $this->record->bankAccounts()->count() }} {{ $this->getStatDescriptions()['accounts'] }}</p>
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
                                {{ $this->record->supportTickets()->where('status', 'pending')->count() }}
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
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->record->name }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Address</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->record->email }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone Number</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->record->phone_number ?? 'N/A' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date of Birth</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->record->date_of_birth?->format('M d, Y') ?? 'N/A' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Country</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->record->country ?? 'N/A' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">City</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->record->city ?? 'N/A' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->record->address ?? 'N/A' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Postal Code</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->record->postal_code ?? 'N/A' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Account Created</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->record->created_at->format('M d, Y h:i A') }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Login</dt>
                        <dd class="text-sm font-semibold text-gray-950 dark:text-white">{{ $this->record->last_login_at?->format('M d, Y h:i A') ?? 'Never' }}</dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">KYC Status</dt>
                        <dd>
                            @php
                                $kycLevel = $this->record->kyc_level ?? 0;
                                $kycLabels = [
                                    0 => 'Not Verified',
                                    1 => 'Level 1 - Basic',
                                    2 => 'Level 2 - Enhanced',
                                    3 => 'Level 3 - Full',
                                ];
                                $kycColors = [
                                    0 => 'warning',
                                    1 => 'info',
                                    2 => 'primary',
                                    3 => 'success',
                                ];
                            @endphp
                            <x-filament::badge :color="$kycColors[$kycLevel] ?? 'warning'">
                                {{ $kycLabels[$kycLevel] ?? 'Not Verified' }}
                            </x-filament::badge>
                        </dd>
                    </div>

                    <div class="space-y-1">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email Verification</dt>
                        <dd>
                            <x-filament::badge :color="$this->record->email_verified_at ? 'success' : 'danger'">
                                {{ $this->record->email_verified_at ? 'Verified' : 'Not Verified' }}
                            </x-filament::badge>
                        </dd>
                    </div>
                </div>
            </x-filament::section>

            {{-- KYC Documents Section --}}
            @if($this->record->kycVerifications()->exists())
            <x-filament::section class="mt-6">
                <x-slot name="heading">KYC Documents</x-slot>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($this->record->kycVerifications as $document)
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
            <div class="space-y-6">
                {{-- Password Section --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-key class="w-5 h-5 text-primary-500" />
                            Password
                        </div>
                    </x-slot>
                    <x-slot name="description">Manage the user's account password</x-slot>

                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Force a password reset for this user. They will be required to set a new password.
                            </p>
                            @if($this->record->password_changed_at)
                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                    Last changed: {{ $this->record->password_changed_at->diffForHumans() }}
                                </p>
                            @endif
                        </div>
                        <x-filament::button 
                            color="danger" 
                            icon="heroicon-o-key" 
                            x-on:click="$dispatch('open-modal', { id: 'reset-password' })"
                        >
                            Reset Password
                        </x-filament::button>
                    </div>
                </x-filament::section>

                {{-- Transaction PIN Section --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-finger-print class="w-5 h-5 text-primary-500" />
                            Transaction PIN
                        </div>
                    </x-slot>
                    <x-slot name="description">Manage the user's 6-digit transaction PIN for secure operations</x-slot>

                    <div class="flex items-center justify-between">
                        <div class="space-y-2">
                            @if($this->record->transaction_pin)
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-success-50 dark:bg-success-900/30 border border-success-200 dark:border-success-700">
                                        <x-heroicon-s-check-circle class="w-4 h-4 text-success-600 dark:text-success-400" />
                                        <span class="text-success-700 dark:text-success-300 text-sm font-medium">PIN is set</span>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                    The user has a transaction PIN configured for secure operations.
                                </p>
                            @else
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-warning-50 dark:bg-warning-900/30 border border-warning-200 dark:border-warning-700">
                                        <x-heroicon-s-exclamation-circle class="w-4 h-4 text-warning-600 dark:text-warning-400" />
                                        <span class="text-warning-700 dark:text-warning-300 text-sm font-medium">No PIN set</span>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                    The user has not set up a transaction PIN yet.
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($this->record->transaction_pin)
                                <x-filament::button 
                                    color="gray" 
                                    icon="heroicon-o-trash" 
                                    x-on:click="$dispatch('open-modal', { id: 'clear-pin' })"
                                    size="sm"
                                >
                                    Clear
                                </x-filament::button>
                            @endif
                            <x-filament::button 
                                color="primary" 
                                icon="heroicon-o-finger-print" 
                                x-on:click="$dispatch('open-modal', { id: 'manage-pin' })"
                            >
                                {{ $this->record->transaction_pin ? 'Change PIN' : 'Set PIN' }}
                            </x-filament::button>
                        </div>
                    </div>
                </x-filament::section>

                {{-- Two-Factor Authentication Section --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-shield-check class="w-5 h-5 text-primary-500" />
                            Two-Factor Authentication
                        </div>
                    </x-slot>
                    <x-slot name="description">Manage two-factor authentication settings for enhanced security</x-slot>

                    <div class="flex items-center justify-between">
                        <div class="space-y-2">
                            @if($this->record->two_factor_secret)
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-success-50 dark:bg-success-900/30 border border-success-200 dark:border-success-700">
                                        <x-heroicon-s-shield-check class="w-4 h-4 text-success-600 dark:text-success-400" />
                                        <span class="text-success-700 dark:text-success-300 text-sm font-medium">2FA Enabled</span>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                    Two-factor authentication is active. 
                                    @if($this->record->two_factor_confirmed_at)
                                        Confirmed {{ $this->record->two_factor_confirmed_at->diffForHumans() }}.
                                    @endif
                                </p>
                            @else
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                        <x-heroicon-o-shield-exclamation class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                        <span class="text-gray-600 dark:text-gray-400 text-sm font-medium">2FA Disabled</span>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                    Two-factor authentication is not enabled for this account.
                                </p>
                            @endif
                        </div>
                        <x-filament::button 
                            color="{{ $this->record->two_factor_secret ? 'danger' : 'gray' }}" 
                            icon="heroicon-o-shield-check" 
                            x-on:click="$dispatch('open-modal', { id: 'two-factor' })"
                            :disabled="!$this->record->two_factor_secret"
                        >
                            {{ $this->record->two_factor_secret ? 'Reset 2FA' : 'Not Enabled' }}
                        </x-filament::button>
                    </div>
                </x-filament::section>

                {{-- Account Status Section --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-lock-closed class="w-5 h-5 text-primary-500" />
                            Account Status
                        </div>
                    </x-slot>
                    <x-slot name="description">Control user account access and lock status</x-slot>

                    <div class="flex items-center justify-between">
                        <div class="space-y-2">
                            @if($this->record->is_active)
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-success-50 dark:bg-success-900/30 border border-success-200 dark:border-success-700">
                                        <x-heroicon-s-check-circle class="w-4 h-4 text-success-600 dark:text-success-400" />
                                        <span class="text-success-700 dark:text-success-300 text-sm font-medium">Account Active</span>
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                    The user can sign in and access their account normally.
                                </p>
                            @else
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-danger-50 dark:bg-danger-900/30 border border-danger-200 dark:border-danger-700">
                                        <x-heroicon-s-lock-closed class="w-4 h-4 text-danger-600 dark:text-danger-400" />
                                        <span class="text-danger-700 dark:text-danger-300 text-sm font-medium">Account Locked</span>
                                    </span>
                                </div>
                                <p class="text-xs text-danger-600 dark:text-danger-400">
                                    This account is locked. The user cannot sign in until unlocked.
                                </p>
                            @endif
                        </div>
                        <x-filament::button 
                            color="{{ $this->record->is_active ? 'warning' : 'success' }}" 
                            icon="{{ $this->record->is_active ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open' }}" 
                            x-on:click="$dispatch('open-modal', { id: 'lock-account' })"
                        >
                            {{ $this->record->is_active ? 'Lock Account' : 'Unlock Account' }}
                        </x-filament::button>
                    </div>
                </x-filament::section>

                {{-- Session Management Section --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-computer-desktop class="w-5 h-5 text-primary-500" />
                            Session Management
                        </div>
                    </x-slot>
                    <x-slot name="description">Manage user sessions and force logout from all devices</x-slot>

                    <div class="flex items-center justify-between">
                        <div class="space-y-1">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Invalidate all active sessions for this user across all devices and browsers.
                            </p>
                            @if($this->record->last_login_at)
                                <p class="text-xs text-gray-500 dark:text-gray-500">
                                    Last login: {{ $this->record->last_login_at->diffForHumans() }}
                                    @if($this->record->last_login_ip)
                                        from {{ $this->record->last_login_ip }}
                                    @endif
                                </p>
                            @endif
                        </div>
                        <x-filament::button 
                            color="warning" 
                            icon="heroicon-o-arrow-right-start-on-rectangle" 
                            x-on:click="$dispatch('open-modal', { id: 'force-logout' })"
                        >
                            Force Logout
                        </x-filament::button>
                    </div>
                </x-filament::section>

                {{-- Security Overview --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-clipboard-document-check class="w-5 h-5 text-primary-500" />
                            Security Overview
                        </div>
                    </x-slot>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        {{-- Email Verification --}}
                        <div class="p-4 rounded-lg border {{ $this->record->email_verified_at ? 'bg-success-50 dark:bg-success-900/20 border-success-200 dark:border-success-700' : 'bg-warning-50 dark:bg-warning-900/20 border-warning-200 dark:border-warning-700' }}">
                            <div class="flex items-center gap-2 mb-2">
                                @if($this->record->email_verified_at)
                                    <x-heroicon-s-check-circle class="w-5 h-5 text-success-600 dark:text-success-400" />
                                @else
                                    <x-heroicon-s-exclamation-circle class="w-5 h-5 text-warning-600 dark:text-warning-400" />
                                @endif
                                <span class="text-sm font-medium {{ $this->record->email_verified_at ? 'text-success-700 dark:text-success-300' : 'text-warning-700 dark:text-warning-300' }}">Email</span>
                            </div>
                            <p class="text-xs {{ $this->record->email_verified_at ? 'text-success-600 dark:text-success-400' : 'text-warning-600 dark:text-warning-400' }}">
                                {{ $this->record->email_verified_at ? 'Verified' : 'Not Verified' }}
                            </p>
                        </div>

                        {{-- Transaction PIN --}}
                        <div class="p-4 rounded-lg border {{ $this->record->transaction_pin ? 'bg-success-50 dark:bg-success-900/20 border-success-200 dark:border-success-700' : 'bg-warning-50 dark:bg-warning-900/20 border-warning-200 dark:border-warning-700' }}">
                            <div class="flex items-center gap-2 mb-2">
                                @if($this->record->transaction_pin)
                                    <x-heroicon-s-check-circle class="w-5 h-5 text-success-600 dark:text-success-400" />
                                @else
                                    <x-heroicon-s-exclamation-circle class="w-5 h-5 text-warning-600 dark:text-warning-400" />
                                @endif
                                <span class="text-sm font-medium {{ $this->record->transaction_pin ? 'text-success-700 dark:text-success-300' : 'text-warning-700 dark:text-warning-300' }}">PIN</span>
                            </div>
                            <p class="text-xs {{ $this->record->transaction_pin ? 'text-success-600 dark:text-success-400' : 'text-warning-600 dark:text-warning-400' }}">
                                {{ $this->record->transaction_pin ? 'Set' : 'Not Set' }}
                            </p>
                        </div>

                        {{-- 2FA --}}
                        <div class="p-4 rounded-lg border {{ $this->record->two_factor_secret ? 'bg-success-50 dark:bg-success-900/20 border-success-200 dark:border-success-700' : 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700' }}">
                            <div class="flex items-center gap-2 mb-2">
                                @if($this->record->two_factor_secret)
                                    <x-heroicon-s-check-circle class="w-5 h-5 text-success-600 dark:text-success-400" />
                                @else
                                    <x-heroicon-o-minus-circle class="w-5 h-5 text-gray-400 dark:text-gray-500" />
                                @endif
                                <span class="text-sm font-medium {{ $this->record->two_factor_secret ? 'text-success-700 dark:text-success-300' : 'text-gray-600 dark:text-gray-400' }}">2FA</span>
                            </div>
                            <p class="text-xs {{ $this->record->two_factor_secret ? 'text-success-600 dark:text-success-400' : 'text-gray-500 dark:text-gray-500' }}">
                                {{ $this->record->two_factor_secret ? 'Enabled' : 'Disabled' }}
                            </p>
                        </div>

                        {{-- Account Status --}}
                        <div class="p-4 rounded-lg border {{ $this->record->is_active ? 'bg-success-50 dark:bg-success-900/20 border-success-200 dark:border-success-700' : 'bg-danger-50 dark:bg-danger-900/20 border-danger-200 dark:border-danger-700' }}">
                            <div class="flex items-center gap-2 mb-2">
                                @if($this->record->is_active)
                                    <x-heroicon-s-check-circle class="w-5 h-5 text-success-600 dark:text-success-400" />
                                @else
                                    <x-heroicon-s-x-circle class="w-5 h-5 text-danger-600 dark:text-danger-400" />
                                @endif
                                <span class="text-sm font-medium {{ $this->record->is_active ? 'text-success-700 dark:text-success-300' : 'text-danger-700 dark:text-danger-300' }}">Status</span>
                            </div>
                            <p class="text-xs {{ $this->record->is_active ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400' }}">
                                {{ $this->record->is_active ? 'Active' : 'Locked' }}
                            </p>
                        </div>
                    </div>
                </x-filament::section>
            </div>
        </div>

        {{-- Edit user info modal --}}
        <x-filament::modal id="edit-user-info" width="3xl">
            <x-slot name="heading">Edit User Information</x-slot>

            <form 
                x-data="{
                    isActive: {{ $this->record->is_active ? 'true' : 'false' }},
                    emailVerified: {{ $this->record->email_verified_at ? 'true' : 'false' }},
                    submitForm(event) {
                        const formData = new FormData(event.target);
                        const data = Object.fromEntries(formData.entries());
                        data.is_active = this.isActive;
                        data.email_verified = this.emailVerified;
                        $wire.updateUserInformation(data);
                    }
                }"
                @submit.prevent="submitForm($event)"
                class="space-y-6"
            >
                {{-- Personal Information Section --}}
                <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit-first-name" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">First Name *</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-first-name" name="first_name" type="text" value="{{ $this->record->first_name }}" required />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-last-name" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Last Name *</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-last-name" name="last_name" type="text" value="{{ $this->record->last_name }}" required />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-email" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Email Address *</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-email" name="email" type="email" value="{{ $this->record->email }}" required />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-phone" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Phone Number</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-phone" name="phone_number" type="tel" value="{{ $this->record->phone_number }}" />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-dob" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Date of Birth</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-dob" name="date_of_birth" type="date" value="{{ $this->record->date_of_birth?->format('Y-m-d') }}" />
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
                                <x-filament::input id="edit-address1" name="address_line_1" type="text" value="{{ $this->record->address_line_1 }}" />
                            </x-filament::input.wrapper>
                        </div>

                        <div class="md:col-span-2">
                            <label for="edit-address2" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Address Line 2</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-address2" name="address_line_2" type="text" value="{{ $this->record->address_line_2 }}" />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-city" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">City</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-city" name="city" type="text" value="{{ $this->record->city }}" />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-state" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">State/Province</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-state" name="state" type="text" value="{{ $this->record->state }}" />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-postal" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Postal Code</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-postal" name="postal_code" type="text" value="{{ $this->record->postal_code }}" />
                            </x-filament::input.wrapper>
                        </div>

                        <div>
                            <label for="edit-country" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Country</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="edit-country" name="country" type="text" value="{{ $this->record->country }}" />
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
                                    :aria-checked="isActive"
                                    @click="isActive = !isActive"
                                    :class="isActive ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
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
                                <label for="edit-email-verified" class="text-sm font-medium text-gray-900 dark:text-white cursor-pointer">
                                    Email Verified
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Verified accounts have confirmed their email</p>
                            </div>
                            <div>
                                <button
                                    type="button"
                                    role="switch"
                                    :aria-checked="emailVerified"
                                    @click="emailVerified = !emailVerified"
                                    :class="emailVerified ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                                    <span
                                        :class="emailVerified ? 'translate-x-6' : 'translate-x-1'"
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
                                <option value="0" {{ $this->record->kyc_level == 0 ? 'selected' : '' }}>Level 0 - Not Verified</option>
                                <option value="1" {{ $this->record->kyc_level == 1 ? 'selected' : '' }}>Level 1 - Basic Verification</option>
                                <option value="2" {{ $this->record->kyc_level == 2 ? 'selected' : '' }}>Level 2 - Enhanced Verification</option>
                                <option value="3" {{ $this->record->kyc_level == 3 ? 'selected' : '' }}>Level 3 - Full Verification</option>
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
                <p class="text-sm text-gray-600 dark:text-gray-400">This will invalidate all active sessions for <strong>{{ $this->record->email }}</strong>.</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">The user will be logged out from all devices and will need to sign in again.</p>
                <div class="flex justify-end gap-2">
                    <x-filament::button color="secondary" type="button" x-on:click="$dispatch('close-modal', { id: 'force-logout' })">Cancel</x-filament::button>
                    <x-filament::button color="warning" wire:click="forceLogout">Force logout</x-filament::button>
                </div>
            </div>
        </x-filament::modal>

        {{-- Lock account modal --}}
        <x-filament::modal id="lock-account">
            <x-slot name="heading">{{ $this->record->is_active ? 'Lock account' : 'Unlock account' }}</x-slot>

            <div class="space-y-4">
                @if($this->record->is_active)
                    <p class="text-sm text-gray-600 dark:text-gray-400">This will prevent <strong>{{ $this->record->email }}</strong> from signing in until unlocked by an admin.</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">The user will also be logged out from all active sessions.</p>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-400">This will allow <strong>{{ $this->record->email }}</strong> to sign in again.</p>
                @endif
                <div class="flex justify-end gap-2">
                    <x-filament::button color="secondary" type="button" x-on:click="$dispatch('close-modal', { id: 'lock-account' })">Cancel</x-filament::button>
                    <x-filament::button color="{{ $this->record->is_active ? 'danger' : 'success' }}" wire:click="toggleAccountLock">
                        {{ $this->record->is_active ? 'Lock account' : 'Unlock account' }}
                    </x-filament::button>
                </div>
            </div>
        </x-filament::modal>

        {{-- Two-factor modal --}}
        <x-filament::modal id="two-factor">
            <x-slot name="heading">Two-factor authentication</x-slot>

            <div class="space-y-4">
                <div class="flex items-center gap-3 p-4 rounded-lg {{ $this->record->two_factor_secret ? 'bg-success-50 dark:bg-success-900/20' : 'bg-gray-50 dark:bg-gray-800' }}">
                    @if($this->record->two_factor_secret)
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

                @if($this->record->two_factor_secret)
                    <div class="p-4 bg-warning-50 dark:bg-warning-900/20 rounded-lg">
                        <p class="text-sm text-warning-700 dark:text-warning-300">
                            <strong>Warning:</strong> Resetting 2FA will disable two-factor authentication for this user. They will need to set it up again from their account settings.
                        </p>
                    </div>
                @endif

                <div class="flex justify-end gap-2">
                    <x-filament::button color="secondary" type="button" x-on:click="$dispatch('close-modal', { id: 'two-factor' })">Close</x-filament::button>
                    @if($this->record->two_factor_secret)
                        <x-filament::button color="danger" wire:click="resetTwoFactor">Reset 2FA</x-filament::button>
                    @endif
                </div>
            </div>
        </x-filament::modal>

        {{-- Manage PIN modal --}}
        <x-filament::modal id="manage-pin" width="md">
            <x-slot name="heading">
                {{ $this->record->transaction_pin ? 'Change Transaction PIN' : 'Set Transaction PIN' }}
            </x-slot>
            <x-slot name="description">
                Enter a 6-digit PIN that will be used to authorize transactions.
            </x-slot>

            <form class="space-y-4" wire:submit.prevent="setTransactionPin($refs.newPin.value, $refs.confirmPin.value)">
                <div>
                    <label for="new-pin" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                        {{ $this->record->transaction_pin ? 'New PIN' : 'Transaction PIN' }}
                    </label>
                    <x-filament::input.wrapper>
                        <x-filament::input 
                            x-ref="newPin" 
                            id="new-pin" 
                            name="pin" 
                            type="password" 
                            inputmode="numeric" 
                            pattern="[0-9]{6}" 
                            maxlength="6"
                            required 
                            placeholder="••••••"
                        />
                    </x-filament::input.wrapper>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Must be exactly 6 digits</p>
                </div>

                <div>
                    <label for="confirm-pin" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">Confirm PIN</label>
                    <x-filament::input.wrapper>
                        <x-filament::input 
                            x-ref="confirmPin" 
                            id="confirm-pin" 
                            name="pin_confirmation" 
                            type="password" 
                            inputmode="numeric" 
                            pattern="[0-9]{6}" 
                            maxlength="6"
                            required 
                            placeholder="••••••"
                        />
                    </x-filament::input.wrapper>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <x-filament::button color="gray" type="button" x-on:click="$dispatch('close-modal', { id: 'manage-pin' })">
                        Cancel
                    </x-filament::button>
                    <x-filament::button color="primary" type="submit" icon="heroicon-o-finger-print">
                        {{ $this->record->transaction_pin ? 'Update PIN' : 'Set PIN' }}
                    </x-filament::button>
                </div>
            </form>
        </x-filament::modal>

        {{-- Clear PIN confirmation modal --}}
        <x-filament::modal id="clear-pin" width="md">
            <x-slot name="heading">Clear Transaction PIN</x-slot>

            <div class="space-y-4">
                <div class="flex items-start gap-3 p-4 rounded-lg bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-700">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6 text-danger-600 dark:text-danger-400 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="font-semibold text-danger-700 dark:text-danger-300">Warning</p>
                        <p class="text-sm text-danger-600 dark:text-danger-400 mt-1">
                            This will remove the transaction PIN for <strong>{{ $this->record->email }}</strong>. 
                            They will not be able to perform secure operations until a new PIN is set.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <x-filament::button color="gray" type="button" x-on:click="$dispatch('close-modal', { id: 'clear-pin' })">
                        Cancel
                    </x-filament::button>
                    <x-filament::button color="danger" wire:click="clearTransactionPin" icon="heroicon-o-trash">
                        Clear PIN
                    </x-filament::button>
                </div>
            </div>
        </x-filament::modal>

        {{-- Transactions Tab Content --}}
        <div x-show="activeTab === 'transactions'" x-cloak>
            <x-filament::section>
                <x-slot name="heading">All Transactions</x-slot>

                @php
                    // Get paginated transactions from the component method
                    $pagination = $this->getPaginatedTransactions($this->transactionsPage);
                    $allTransactions = $pagination['data'];
                    $transactionCounts = $this->getTransactionCounts();
                @endphp

                {{-- Transaction Summary Cards --}}
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3 mb-6">
                    <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg text-center">
                        <p class="text-xs text-gray-600 dark:text-gray-400">Wire</p>
                        <p class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $transactionCounts['wire'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center">
                        <p class="text-xs text-gray-600 dark:text-gray-400">Internal</p>
                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $transactionCounts['internal_sent'] + $transactionCounts['internal_received'] }}</p>
                    </div>
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg text-center">
                        <p class="text-xs text-gray-600 dark:text-gray-400">Domestic</p>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ $transactionCounts['domestic'] }}</p>
                    </div>
                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-center">
                        <p class="text-xs text-gray-600 dark:text-gray-400">Deposits</p>
                        <p class="text-lg font-bold text-yellow-600 dark:text-yellow-400">{{ $transactionCounts['check_deposit'] + $transactionCounts['mobile_deposit'] + $transactionCounts['crypto_deposit'] }}</p>
                    </div>
                    <div class="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg text-center">
                        <p class="text-xs text-gray-600 dark:text-gray-400">Withdrawals</p>
                        <p class="text-lg font-bold text-red-600 dark:text-red-400">{{ $transactionCounts['withdrawal'] }}</p>
                    </div>
                    <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg text-center">
                        <p class="text-xs text-gray-600 dark:text-gray-400">Exchange</p>
                        <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $transactionCounts['exchange'] }}</p>
                    </div>
                </div>

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
                                    <span class="text-sm font-semibold text-gray-950 dark:text-white">Description</span>
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
                                    $transactionType = $transaction->_type ?? class_basename($transaction);
                                    $category = $transaction->_category ?? 'other';
                                    
                                    // Determine color and sign based on category
                                    $isCredit = in_array($category, ['deposit', 'transfer_in']);
                                    $isDebit = in_array($category, ['transfer_out', 'withdrawal']);
                                    
                                    // Special handling for TransactionHistory
                                    if ($transaction instanceof \App\Models\TransactionHistory) {
                                        $rawType = $transaction->transaction_type;
                                        $transactionTypeValue = $rawType instanceof \BackedEnum ? $rawType->value : (string) $rawType;
                                        $isCredit = in_array($transactionTypeValue, ['credit', 'deposit', 'refund'], true);
                                        $isDebit = in_array($transactionTypeValue, ['debit', 'withdrawal'], true);
                                        $transactionType = $rawType instanceof \App\Enums\TransactionType
                                            ? $rawType->label()
                                            : ucfirst(str_replace('_', ' ', $transactionTypeValue));
                                    }
                                    
                                    // Format amount
                                    if ($transaction instanceof \App\Models\TransactionHistory) {
                                        $amountValue = $transaction->amount;
                                    } elseif ($transaction instanceof \App\Models\ExchangeMoney) {
                                        $amountValue = $transaction->from_amount / 100;
                                    } elseif ($transaction instanceof \App\Models\CryptoDeposit) {
                                        $amountValue = $transaction->usd_amount / 100;
                                    } else {
                                        $amountValue = ($transaction->amount ?? 0) / 100;
                                    }
                                    
                                    $sign = $isCredit ? '+' : ($isDebit ? '-' : '');
                                    $colorClass = $isCredit ? 'text-success-600 dark:text-success-400' : ($isDebit ? 'text-danger-600 dark:text-danger-400' : 'text-gray-600 dark:text-gray-400');
                                    
                                    // Get description/reference
                                    $description = $transaction->reference_number 
                                        ?? $transaction->description 
                                        ?? $transaction->narration 
                                        ?? ($transaction instanceof \App\Models\ExchangeMoney ? ($transaction->from_currency . ' → ' . $transaction->to_currency) : null)
                                        ?? 'N/A';
                                    
                                    // Get status
                                    $status = $transaction->status ?? 'completed';
                                    $statusValue = $status instanceof \BackedEnum ? $status->value : (string) $status;
                                    
                                    // Type badge color
                                    $typeBadgeColor = match($category) {
                                        'transfer_out' => 'danger',
                                        'transfer_in' => 'success',
                                        'deposit' => 'success',
                                        'withdrawal' => 'warning',
                                        'exchange' => 'info',
                                        'request' => 'gray',
                                        'admin' => 'primary',
                                        default => 'gray',
                                    };
                                @endphp
                                <tr class="fi-ta-row bg-white transition duration-75 hover:bg-gray-50 dark:bg-gray-900 dark:hover:bg-white/5">
                                    <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                        <div class="px-3 py-4">
                                            <div class="text-sm text-gray-950 dark:text-white">
                                                {{ $transaction->created_at->format('M d, Y') }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $transaction->created_at->format('h:i A') }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0">
                                        <div class="px-3 py-4">
                                            <x-filament::badge :color="$typeBadgeColor" size="sm">
                                                {{ $transactionType }}
                                            </x-filament::badge>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0">
                                        <div class="px-3 py-4">
                                            <div class="text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                                {{ Str::limit($description, 30) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0">
                                        <div class="px-3 py-4">
                                            <div class="text-sm font-semibold {{ $colorClass }}">
                                                {{ $sign }}${{ number_format($amountValue, 2) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fi-ta-cell p-0">
                                        <div class="px-3 py-4">
                                            <x-filament::badge
                                                :color="$statusValue === 'completed' ? 'success' : ($statusValue === 'failed' ? 'danger' : ($statusValue === 'cancelled' ? 'gray' : 'warning'))">
                                                {{ ucfirst($statusValue) }}
                                            </x-filament::badge>
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
                                                        icon="heroicon-o-banknotes"
                                                        class="fi-ta-empty-state-icon h-6 w-6 text-gray-500 dark:text-gray-400"
                                                    />
                                                </div>
                                                <h4 class="fi-ta-empty-state-heading text-base font-semibold text-gray-950 dark:text-white">
                                                    No transactions found
                                                </h4>
                                                <p class="fi-ta-empty-state-description text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    This user hasn't made any transactions yet.
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Pagination Controls --}}
                @if($pagination['total'] > 0)
                    <div class="fi-ta-pagination flex items-center justify-between gap-x-3 px-4 py-3 sm:px-6 border-t border-gray-200 dark:border-white/10">
                        <div class="flex-1 text-sm text-gray-700 dark:text-gray-300">
                            Showing <span class="font-medium">{{ $pagination['from'] }}</span> to <span class="font-medium">{{ $pagination['to'] }}</span> of <span class="font-medium">{{ $pagination['total'] }}</span> transactions
                        </div>
                        
                        <div class="flex items-center gap-x-1">
                            {{-- Previous Button --}}
                            <x-filament::button
                                color="gray"
                                size="sm"
                                icon="heroicon-m-chevron-left"
                                icon-position="before"
                                :disabled="$pagination['current_page'] <= 1"
                                wire:click="previousTransactionsPage"
                            >
                                Previous
                            </x-filament::button>
                            
                            {{-- Page Numbers --}}
                            <div class="flex items-center gap-x-1 mx-2">
                                @php
                                    $currentPage = $pagination['current_page'];
                                    $lastPage = $pagination['last_page'];
                                    
                                    // Calculate which page numbers to show
                                    $startPage = max(1, $currentPage - 2);
                                    $endPage = min($lastPage, $currentPage + 2);
                                    
                                    // Adjust to always show 5 pages if possible
                                    if ($endPage - $startPage < 4) {
                                        if ($startPage === 1) {
                                            $endPage = min($lastPage, 5);
                                        } else {
                                            $startPage = max(1, $lastPage - 4);
                                        }
                                    }
                                @endphp
                                
                                @if($startPage > 1)
                                    <x-filament::button
                                        color="gray"
                                        size="sm"
                                        wire:click="goToTransactionsPage(1)"
                                    >
                                        1
                                    </x-filament::button>
                                    @if($startPage > 2)
                                        <span class="px-2 text-gray-500">...</span>
                                    @endif
                                @endif
                                
                                @for($i = $startPage; $i <= $endPage; $i++)
                                    <x-filament::button
                                        :color="$i === $currentPage ? 'primary' : 'gray'"
                                        size="sm"
                                        wire:click="goToTransactionsPage({{ $i }})"
                                    >
                                        {{ $i }}
                                    </x-filament::button>
                                @endfor
                                
                                @if($endPage < $lastPage)
                                    @if($endPage < $lastPage - 1)
                                        <span class="px-2 text-gray-500">...</span>
                                    @endif
                                    <x-filament::button
                                        color="gray"
                                        size="sm"
                                        wire:click="goToTransactionsPage({{ $lastPage }})"
                                    >
                                        {{ $lastPage }}
                                    </x-filament::button>
                                @endif
                            </div>
                            
                            {{-- Next Button --}}
                            <x-filament::button
                                color="gray"
                                size="sm"
                                icon="heroicon-m-chevron-right"
                                icon-position="after"
                                :disabled="$pagination['current_page'] >= $pagination['last_page']"
                                wire:click="nextTransactionsPage"
                            >
                                Next
                            </x-filament::button>
                        </div>
                    </div>
                @endif
            </x-filament::section>
        </div>

        {{-- Referrals Tab Content --}}
        <div x-show="activeTab === 'referrals'" x-cloak>
            <x-filament::section>
                <x-slot name="heading">Referral Information</x-slot>

                {{-- Referral Code & URL Section --}}
                <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-share class="w-5 h-5 text-blue-600" />
                            <h3 class="font-semibold text-gray-900 dark:text-white">Referral Code & URL</h3>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Referral Code --}}
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1 block">Referral Code</label>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 px-4 py-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <span class="text-lg font-mono font-bold text-blue-600 dark:text-blue-400" id="referral-code">{{ $this->record->referral_code ?? 'Not Generated' }}</span>
                                </div>
                                @if($this->record->referral_code)
                                    <button
                                        type="button"
                                        x-data
                                        x-on:click="
                                            navigator.clipboard.writeText('{{ $this->record->referral_code }}');
                                            $tooltip('Copied!', { timeout: 1500 });
                                            new FilamentNotification()
                                                .title('Referral code copied!')
                                                .success()
                                                .send();
                                        "
                                        class="p-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors"
                                        title="Copy Referral Code"
                                    >
                                        <x-heroicon-o-clipboard-document class="w-5 h-5" />
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="generateReferralCode"
                                        class="p-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors"
                                        title="Generate Referral Code"
                                    >
                                        <x-heroicon-o-plus-circle class="w-5 h-5" />
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Referral URL --}}
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1 block">Referral URL</label>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 px-4 py-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 truncate">
                                    <span class="text-sm text-gray-600 dark:text-gray-300" id="referral-url">{{ $this->record->referral_code ? url('/register?ref=' . $this->record->referral_code) : 'Generate code first' }}</span>
                                </div>
                                @if($this->record->referral_code)
                                    <button
                                        type="button"
                                        x-data
                                        x-on:click="
                                            navigator.clipboard.writeText('{{ url('/register?ref=' . $this->record->referral_code) }}');
                                            $tooltip('Copied!', { timeout: 1500 });
                                            new FilamentNotification()
                                                .title('Referral URL copied!')
                                                .success()
                                                .send();
                                        "
                                        class="p-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors"
                                        title="Copy Referral URL"
                                    >
                                        <x-heroicon-o-link class="w-5 h-5" />
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Stats Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center">
                                <x-heroicon-o-users class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Total Referrals</p>
                                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $this->record->total_referrals }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-yellow-100 dark:bg-yellow-800 rounded-full flex items-center justify-center">
                                <x-heroicon-o-clock class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
                                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $this->record->pending_referrals }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center">
                                <x-heroicon-o-check-circle class="w-5 h-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $this->record->completed_referrals }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-800 rounded-full flex items-center justify-center">
                                <x-heroicon-o-banknotes class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Total Earnings</p>
                                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">${{ $this->record->formatted_referral_earnings }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Referred By Section --}}
                @if($this->record->referrer)
                    <div class="mb-6 p-4 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800">
                        <div class="flex items-center gap-3">
                            <x-heroicon-o-user-plus class="w-5 h-5 text-emerald-600" />
                            <span class="text-sm text-gray-600 dark:text-gray-300">This user was referred by:</span>
                            <a href="{{ route('filament.admin.resources.users.view', $this->record->referrer) }}" class="font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300">
                                {{ $this->record->referrer->full_name }}
                            </a>
                            @if($this->record->referred_at)
                                <span class="text-xs text-gray-500">on {{ $this->record->referred_at->format('M d, Y') }}</span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Referred Users List --}}
                <div class="mt-6">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                        <x-heroicon-o-user-group class="w-5 h-5" />
                        Users Referred by {{ $this->record->first_name }}
                    </h4>

                    @php
                        $referredUsers = $this->record->referredUsers()->latest()->get();
                    @endphp

                    @if($referredUsers->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-800">
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Joined</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($referredUsers as $referredUser)
                                        <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                            <td class="px-4 py-4">
                                                <div class="flex items-center gap-3">
                                                    <img class="h-8 w-8 rounded-full object-cover" src="{{ $referredUser->avatar_url }}" alt="{{ $referredUser->full_name }}">
                                                    <div>
                                                        <p class="font-medium text-gray-900 dark:text-white">{{ $referredUser->full_name }}</p>
                                                        <p class="text-xs text-gray-500">ID: {{ $referredUser->id }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 text-gray-600 dark:text-gray-400">
                                                {{ $referredUser->email }}
                                            </td>
                                            <td class="px-4 py-4 text-gray-600 dark:text-gray-400">
                                                {{ $referredUser->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-4 py-4">
                                                @if($referredUser->is_active)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                        Active
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                                        Inactive
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4">
                                                <a href="{{ route('filament.admin.resources.users.view', $referredUser) }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                            <x-heroicon-o-user-group class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                            <p class="text-gray-500 dark:text-gray-400 text-sm">This user hasn't referred anyone yet.</p>
                            <p class="text-gray-400 dark:text-gray-500 text-xs mt-1">Referrals will appear here when someone signs up using their referral code.</p>
                        </div>
                    @endif
                </div>
            </x-filament::section>
        </div>

        {{-- Activity Log Tab Content --}}
        <div x-show="activeTab === 'activity'" x-cloak>
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <span>Activity Log</span>
                        <div class="text-sm text-gray-500">
                            Total: {{ $this->record->activityLogs()->count() }} activities
                        </div>
                    </div>
                </x-slot>

                @php
                    $activityLogs = $this->record->activityLogs()->latest()->paginate(50);
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
