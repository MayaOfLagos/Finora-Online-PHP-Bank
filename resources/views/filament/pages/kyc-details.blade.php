<div class="space-y-6">
    {{-- User Information --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">User Information</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Name:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $record->user?->full_name ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Email:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $record->user?->email ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Phone:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $record->user?->phone ?? 'N/A' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Account Created:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $record->user?->created_at?->format('M j, Y') ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    {{-- Document Information --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Document Information</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Document Type:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $record->document_type_name }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Document Number:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $record->document_number ?? 'Not provided' }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Submitted:</span>
                <p class="font-medium text-gray-900 dark:text-white">{{ $record->created_at->format('F j, Y \a\t g:i A') }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500 dark:text-gray-400">Status:</span>
                <x-filament::badge :color="$record->status->color()">
                    {{ $record->status->label() }}
                </x-filament::badge>
            </div>
        </div>
    </div>

    {{-- Document Images --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Uploaded Documents</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @if($record->document_front_path)
                <div class="text-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400 block mb-2">Front</span>
                    <a href="{{ asset('storage/' . $record->document_front_path) }}" target="_blank" class="block">
                        <img src="{{ asset('storage/' . $record->document_front_path) }}" 
                             alt="Document Front" 
                             class="w-full h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700 hover:opacity-75 transition-opacity">
                    </a>
                </div>
            @endif
            
            @if($record->document_back_path)
                <div class="text-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400 block mb-2">Back</span>
                    <a href="{{ asset('storage/' . $record->document_back_path) }}" target="_blank" class="block">
                        <img src="{{ asset('storage/' . $record->document_back_path) }}" 
                             alt="Document Back" 
                             class="w-full h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700 hover:opacity-75 transition-opacity">
                    </a>
                </div>
            @endif
            
            @if($record->selfie_path)
                <div class="text-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400 block mb-2">Selfie</span>
                    <a href="{{ asset('storage/' . $record->selfie_path) }}" target="_blank" class="block">
                        <img src="{{ asset('storage/' . $record->selfie_path) }}" 
                             alt="Selfie" 
                             class="w-full h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700 hover:opacity-75 transition-opacity">
                    </a>
                </div>
            @endif
            
            @if($record->address_proof_path)
                <div class="text-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400 block mb-2">Address Proof</span>
                    <a href="{{ asset('storage/' . $record->address_proof_path) }}" target="_blank" class="block">
                        <img src="{{ asset('storage/' . $record->address_proof_path) }}" 
                             alt="Address Proof" 
                             class="w-full h-32 object-cover rounded-lg border border-gray-200 dark:border-gray-700 hover:opacity-75 transition-opacity">
                    </a>
                </div>
            @endif
        </div>
        
        @if(!$record->document_front_path && !$record->document_back_path && !$record->selfie_path && !$record->address_proof_path)
            <p class="text-gray-500 dark:text-gray-400 text-center py-4">No documents uploaded</p>
        @endif
    </div>

    {{-- Review Information --}}
    @if($record->verified_at)
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3">Review Information</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Reviewed By:</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $record->verifier?->full_name ?? 'System' }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Reviewed At:</span>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $record->verified_at->format('F j, Y \a\t g:i A') }}</p>
                </div>
            </div>
            
            @if($record->rejection_reason)
                <div class="mt-4">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Rejection Reason:</span>
                    <p class="font-medium text-red-600 dark:text-red-400 mt-1">{{ $record->rejection_reason }}</p>
                </div>
            @endif
            
            @if($record->admin_notes)
                <div class="mt-4">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Admin Notes:</span>
                    <p class="font-medium text-gray-900 dark:text-white mt-1">{{ $record->admin_notes }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
