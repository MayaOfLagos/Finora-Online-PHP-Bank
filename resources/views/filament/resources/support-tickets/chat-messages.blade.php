<div class="chat-container" style="background: #f9fafb; border-radius: 8px; padding: 16px; max-height: 500px; overflow-y: auto; border: 1px solid #e5e7eb;">
    @php
        $messages = $getRecord()->messages()->with('user')->orderBy('created_at', 'asc')->get();
    @endphp

    @if($messages->isEmpty())
        <div style="text-align: center; padding: 40px 20px; color: #9ca3af;">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px; margin: 0 auto 12px; opacity: 0.5;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <p style="font-size: 14px; font-weight: 500; color: #6b7280;">No messages yet</p>
            <p style="font-size: 13px; margin-top: 4px; color: #9ca3af;">Start the conversation by sending a reply</p>
        </div>
    @else
        @foreach($messages as $message)
            @php
                $isAgent = $message->type === 'agent';
                $isCurrentUser = $message->user_id === auth()->id();
            @endphp

            <div style="display: flex; justify-content: {{ $isAgent ? 'flex-end' : 'flex-start' }}; margin-bottom: 12px;">
                <div style="max-width: 70%; display: flex; flex-direction: column; {{ $isAgent ? 'align-items: flex-end' : 'align-items: flex-start' }};">
                    
                    {{-- Sender Name --}}
                    <div style="
                        font-size: 11px;
                        font-weight: 600;
                        margin-bottom: 4px;
                        color: #6b7280;
                        padding: 0 4px;
                    ">
                        {{ $message->user->name }}
                        @if($isAgent)
                            <span style="
                                background: #3b82f6;
                                color: white;
                                padding: 1px 6px;
                                border-radius: 6px;
                                font-size: 9px;
                                margin-left: 4px;
                            ">SUPPORT</span>
                        @endif
                    </div>

                    {{-- Message Bubble --}}
                    <div style="
                        background: {{ $isAgent ? '#3b82f6' : '#ffffff' }};
                        color: {{ $isAgent ? '#ffffff' : '#1f2937' }};
                        padding: 8px 12px;
                        border-radius: 12px;
                        {{ $isAgent ? 'border-bottom-right-radius: 2px;' : 'border-bottom-left-radius: 2px;' }}
                        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                        word-wrap: break-word;
                        position: relative;
                        border: 1px solid {{ $isAgent ? '#3b82f6' : '#e5e7eb' }};
                    ">
                        {{-- Message Content --}}
                        <div style="
                            font-size: 13px;
                            line-height: 1.5;
                            white-space: pre-wrap;
                        " class="prose prose-sm {{ $isAgent ? 'prose-invert' : '' }} max-w-none">
                            {!! str($message->message)->markdown() !!}
                        </div>

                        {{-- Timestamp --}}
                        <div style="
                            font-size: 10px;
                            margin-top: 4px;
                            opacity: {{ $isAgent ? '0.85' : '0.65' }};
                            text-align: right;
                        ">
                            {{ $message->created_at->format('M d, H:i') }}
                            @if($isAgent && $isCurrentUser)
                                <span style="margin-left: 2px;">✓✓</span>
                            @endif
                        </div>
                    </div>

                    {{-- User Email (for non-agent messages) --}}
                    @if(!$isAgent)
                        <div style="
                            font-size: 10px;
                            color: #9ca3af;
                            margin-top: 2px;
                            padding: 0 4px;
                        ">
                            {{ $message->user->email }}
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        {{-- Auto-scroll to bottom script --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const chatContainer = document.querySelector('.chat-container');
                if (chatContainer) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            });
            
            // Also scroll on Livewire updates
            document.addEventListener('livewire:navigated', function() {
                const chatContainer = document.querySelector('.chat-container');
                if (chatContainer) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            });
        </script>
    @endif
</div>

<style>
    .chat-container::-webkit-scrollbar {
        width: 6px;
    }
    
    .chat-container::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 3px;
    }
    
    .chat-container::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 3px;
    }
    
    .chat-container::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* Markdown styling for dark background */
    .prose-invert {
        color: white !important;
    }
    
    .prose-invert a {
        color: #bfdbfe !important;
    }
    
    .prose-invert code {
        background: rgba(255, 255, 255, 0.15);
        padding: 2px 5px;
        border-radius: 3px;
        font-size: 12px;
    }
    
    .prose-invert pre {
        background: rgba(0, 0, 0, 0.2);
        border-radius: 6px;
        padding: 8px;
    }
    
    .prose-invert strong {
        color: white;
    }
    
    /* Regular prose styling */
    .prose code {
        background: #f3f4f6;
        padding: 2px 5px;
        border-radius: 3px;
        font-size: 12px;
        color: #ef4444;
    }
    
    .prose pre {
        background: #1f2937;
        color: white;
        border-radius: 6px;
        padding: 8px;
        overflow-x: auto;
    }
</style>
