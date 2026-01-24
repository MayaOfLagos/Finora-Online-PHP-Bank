<div class="space-y-6">
    {{-- Basic Information --}}
    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
        <h3 class="mb-3 text-lg font-medium text-gray-900 dark:text-white">Template Details</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Name:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $template->name }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Slug:</span>
                <p class="font-mono text-sm font-medium text-gray-900 dark:text-white">{{ $template->slug }}</p>
            </div>
            <div class="col-span-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">Description:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $template->description ?? 'No description' }}</p>
            </div>
            <div class="col-span-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">Instructions:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $template->instructions ?? 'No instructions' }}</p>
            </div>
        </div>
    </div>

    {{-- Requirements --}}
    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
        <h3 class="mb-3 text-lg font-medium text-gray-900 dark:text-white">Requirements</h3>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
            <div class="flex items-center space-x-2">
                @if($template->is_required)
                    <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                @else
                    <x-heroicon-o-x-circle class="w-5 h-5 text-gray-400" />
                @endif
                <span class="text-gray-900 dark:text-white">Required Document</span>
            </div>
            <div class="flex items-center space-x-2">
                @if($template->requires_front_image)
                    <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                @else
                    <x-heroicon-o-x-circle class="w-5 h-5 text-gray-400" />
                @endif
                <span class="text-gray-900 dark:text-white">Front Image</span>
            </div>
            <div class="flex items-center space-x-2">
                @if($template->requires_back_image)
                    <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                @else
                    <x-heroicon-o-x-circle class="w-5 h-5 text-gray-400" />
                @endif
                <span class="text-gray-900 dark:text-white">Back Image</span>
            </div>
            <div class="flex items-center space-x-2">
                @if($template->requires_selfie)
                    <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                @else
                    <x-heroicon-o-x-circle class="w-5 h-5 text-gray-400" />
                @endif
                <span class="text-gray-900 dark:text-white">Selfie with Document</span>
            </div>
            <div class="flex items-center space-x-2">
                @if($template->requires_document_number)
                    <x-heroicon-o-check-circle class="w-5 h-5 text-green-500" />
                @else
                    <x-heroicon-o-x-circle class="w-5 h-5 text-gray-400" />
                @endif
                <span class="text-gray-900 dark:text-white">Document Number</span>
            </div>
        </div>
    </div>

    {{-- File Settings --}}
    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
        <h3 class="mb-3 text-lg font-medium text-gray-900 dark:text-white">File Settings</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Accepted Formats:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $template->accepted_formats_list }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Max File Size:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $template->max_file_size_for_humans }}</p>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
        <h3 class="mb-3 text-lg font-medium text-gray-900 dark:text-white">Statistics</h3>
        <div class="grid grid-cols-3 gap-4">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Total Submissions:</span>
                <p class="text-lg font-medium text-gray-900 dark:text-white">{{ $template->kycVerifications()->count() }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Created:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $template->created_at->format('M j, Y') }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Last Updated:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $template->updated_at->format('M j, Y') }}</p>
            </div>
        </div>
    </div>
</div>
