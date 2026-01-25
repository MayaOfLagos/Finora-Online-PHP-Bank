<x-filament-panels::page>
    {{-- Quick Actions --}}
    <div class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Custom Command Input --}}
            <div class="md:col-span-2">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Run Custom Command
                    </label>
                    <div class="flex gap-2">
                        <div class="flex-1 relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 font-mono text-sm">
                                php artisan
                            </span>
                            <input
                                type="text"
                                wire:model="customCommand"
                                wire:keydown.enter="runCustomCommand"
                                placeholder="cache:clear"
                                class="w-full pl-24 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 font-mono text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            >
                        </div>
                        <button
                            type="button"
                            wire:click="runCustomCommand"
                            wire:loading.attr="disabled"
                            class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium text-sm transition-colors disabled:opacity-50 flex items-center gap-2"
                        >
                            <x-heroicon-o-play class="w-4 h-4" wire:loading.remove wire:target="runCustomCommand" />
                            <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" wire:loading wire:target="runCustomCommand" />
                            Run
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                        Enter any artisan command (without "php artisan"). Some dangerous commands are blocked for security.
                    </p>
                </div>
            </div>

            {{-- System Status --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    System Status
                </label>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Environment</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ app()->environment('production') ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                            {{ app()->environment() }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Debug Mode</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ config('app.debug') ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                            {{ config('app.debug') ? 'ON' : 'OFF' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">PHP Version</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ PHP_VERSION }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Laravel</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ app()->version() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Command Output --}}
    @if($showOutput)
        <div class="mb-6">
            <div class="bg-gray-900 rounded-xl shadow-sm border border-gray-700 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 bg-gray-800 border-b border-gray-700">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-command-line class="w-5 h-5 text-green-400" />
                        <span class="text-sm font-medium text-gray-200">Output: {{ $selectedCommand }}</span>
                    </div>
                    <button
                        type="button"
                        wire:click="clearOutput"
                        class="text-gray-400 hover:text-gray-200 transition-colors"
                    >
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
                <div class="p-4 max-h-96 overflow-auto">
                    <pre class="text-sm text-green-400 font-mono whitespace-pre-wrap">{{ $commandOutput ?: 'Command executed successfully (no output)' }}</pre>
                </div>
            </div>
        </div>
    @endif

    {{-- Command Groups --}}
    <div class="space-y-6">
        @foreach($this->getCommandGroups() as $groupName => $commands)
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $groupName }}</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
                        @foreach($commands as $command)
                            <button
                                type="button"
                                wire:click="runCommand('{{ $command['name'] }}')"
                                wire:loading.attr="disabled"
                                wire:target="runCommand('{{ $command['name'] }}')"
                                @if(isset($command['dangerous']) && $command['dangerous'])
                                    onclick="return confirm('This is a potentially dangerous command. Are you sure you want to run: {{ $command['name'] }}?')"
                                @endif
                                class="group flex items-start gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-{{ $command['color'] }}-300 dark:hover:border-{{ $command['color'] }}-700 hover:bg-{{ $command['color'] }}-50 dark:hover:bg-{{ $command['color'] }}-900/20 transition-all text-left disabled:opacity-50"
                            >
                                <div class="flex-shrink-0 p-2 rounded-lg bg-{{ $command['color'] }}-100 dark:bg-{{ $command['color'] }}-900/30 text-{{ $command['color'] }}-600 dark:text-{{ $command['color'] }}-400">
                                    @if($command['icon'] === 'heroicon-o-trash')
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-cog-6-tooth')
                                        <x-heroicon-o-cog-6-tooth class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-bolt')
                                        <x-heroicon-o-bolt class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-map')
                                        <x-heroicon-o-map class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-eye')
                                        <x-heroicon-o-eye class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-bell')
                                        <x-heroicon-o-bell class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-rocket-launch')
                                        <x-heroicon-o-rocket-launch class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-arrow-path')
                                        <x-heroicon-o-arrow-path class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-sparkles')
                                        <x-heroicon-o-sparkles class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-squares-2x2')
                                        <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-circle-stack')
                                        <x-heroicon-o-circle-stack class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-clipboard-document-list')
                                        <x-heroicon-o-clipboard-document-list class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-server-stack')
                                        <x-heroicon-o-server-stack class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-link')
                                        <x-heroicon-o-link class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-queue-list')
                                        <x-heroicon-o-queue-list class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-exclamation-triangle')
                                        <x-heroicon-o-exclamation-triangle class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-wrench-screwdriver')
                                        <x-heroicon-o-wrench-screwdriver class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-check-circle')
                                        <x-heroicon-o-check-circle class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-clock')
                                        <x-heroicon-o-clock class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-play')
                                        <x-heroicon-o-play class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-information-circle')
                                        <x-heroicon-o-information-circle class="w-5 h-5" />
                                    @elseif($command['icon'] === 'heroicon-o-globe-alt')
                                        <x-heroicon-o-globe-alt class="w-5 h-5" />
                                    @else
                                        <x-heroicon-o-command-line class="w-5 h-5" />
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                            {{ $command['name'] }}
                                        </p>
                                        @if(isset($command['dangerous']) && $command['dangerous'])
                                            <span class="px-1.5 py-0.5 text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300 rounded">
                                                !
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-2">
                                        {{ $command['description'] }}
                                    </p>
                                </div>
                                <div wire:loading wire:target="runCommand('{{ $command['name'] }}')" class="flex-shrink-0">
                                    <x-heroicon-o-arrow-path class="w-4 h-4 text-gray-400 animate-spin" />
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Help Section --}}
    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-4">
        <div class="flex gap-3">
            <x-heroicon-o-light-bulb class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
            <div>
                <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">Tips for using Artisan Manager</h4>
                <ul class="mt-2 text-sm text-blue-700 dark:text-blue-300 space-y-1">
                    <li>• <strong>After deployment:</strong> Run "Quick Optimize" to cache all configurations</li>
                    <li>• <strong>After code changes:</strong> Run "Clear All Caches" then "Quick Optimize"</li>
                    <li>• <strong>Before going live:</strong> Make sure Debug Mode is OFF in production</li>
                    <li>• Commands marked with <span class="px-1 py-0.5 text-xs bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300 rounded">!</span> are potentially dangerous</li>
                </ul>
            </div>
        </div>
    </div>
</x-filament-panels::page>
