<div class="space-y-4">
    @if(count($sessions) > 0)
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Device</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">IP Address</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Last Active</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                    @foreach($sessions as $session)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-computer-desktop class="h-5 w-5 text-gray-400" />
                                    {{ $session['device'] }}
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $session['ip'] }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $session['last_active'] }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                @if($session['is_current'] ?? false)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-success-100 px-2 py-1 text-xs font-medium text-success-700 dark:bg-success-900 dark:text-success-300">
                                        <x-heroicon-s-check-circle class="h-3 w-3" />
                                        Current
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        Active
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            <x-filament::button
                color="danger"
                icon="heroicon-o-arrow-right-on-rectangle"
                wire:click="logoutOtherSessions"
                wire:confirm="Are you sure you want to log out all other browser sessions?"
            >
                Log Out Other Sessions
            </x-filament::button>
        </div>
    @else
        <div class="rounded-lg bg-gray-50 p-6 text-center dark:bg-gray-800">
            <x-heroicon-o-computer-desktop class="mx-auto h-12 w-12 text-gray-400" />
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No active sessions found.</p>
        </div>
    @endif
</div>
