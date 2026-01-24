<div class="space-y-4">
    @if(count($loginHistory) > 0)
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">IP Address</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Device</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Browser</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Location</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                    @foreach($loginHistory as $login)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                {{ $login['date'] }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $login['ip'] }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $login['device'] }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $login['browser'] }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $login['location'] }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm">
                                @if($login['status'] === 'success')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-success-100 px-2 py-1 text-xs font-medium text-success-700 dark:bg-success-900 dark:text-success-300">
                                        <x-heroicon-s-check-circle class="h-3 w-3" />
                                        Success
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-danger-100 px-2 py-1 text-xs font-medium text-danger-700 dark:bg-danger-900 dark:text-danger-300">
                                        <x-heroicon-s-x-circle class="h-3 w-3" />
                                        Failed
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-lg bg-gray-50 p-6 text-center dark:bg-gray-800">
            <x-heroicon-o-clock class="mx-auto h-12 w-12 text-gray-400" />
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No login history found.</p>
        </div>
    @endif
</div>
