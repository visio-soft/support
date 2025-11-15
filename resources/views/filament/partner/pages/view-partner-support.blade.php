<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Column: Conversation --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Ticket Header --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Ticket #{{ $record->id }}</span>
                            <x-filament::badge :color="$record->status->getColor()">
                                {{ $record->status->getLabel() }}
                            </x-filament::badge>
                            <x-filament::badge :color="$record->priority->getColor()">
                                {{ $record->priority->getLabel() }}
                            </x-filament::badge>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $record->subject }}</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Created on {{ $record->created_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Original Request --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5" />
                    Your Request
                </h3>
                <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                    {!! $record->content !!}
                </div>
            </div>

            {{-- Conversation Thread --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="w-5 h-5" />
                        Conversation ({{ $record->publicReplies()->count() }} {{ $record->publicReplies()->count() === 1 ? 'reply' : 'replies' }})
                    </h3>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($record->publicReplies()->orderBy('created_at', 'asc')->get() as $reply)
                        <div class="p-4">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full {{ $reply->is_admin_reply ? 'bg-green-100 dark:bg-green-900' : 'bg-blue-100 dark:bg-blue-900' }} flex items-center justify-center">
                                        <span class="text-sm font-semibold {{ $reply->is_admin_reply ? 'text-green-700 dark:text-green-300' : 'text-blue-700 dark:text-blue-300' }}">
                                            {{ substr($reply->user->name, 0, 2) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $reply->user->name }}</span>
                                        @if($reply->is_admin_reply)
                                            <x-filament::badge color="success" size="xs">Support Team</x-filament::badge>
                                        @else
                                            <x-filament::badge color="info" size="xs">You</x-filament::badge>
                                        @endif
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="prose dark:prose-invert prose-sm max-w-none text-gray-700 dark:text-gray-300">
                                        {!! $reply->content !!}
                                    </div>
                                    @if($reply->hasAttachments())
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            @foreach($reply->attachments as $attachment)
                                                <a href="{{ \Storage::disk(config('support.attachments.disk', 'public'))->url($attachment) }}" 
                                                   target="_blank"
                                                   class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors">
                                                    <x-filament::icon icon="heroicon-o-paper-clip" class="w-3 h-3" />
                                                    {{ basename($attachment) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                            <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                            <p class="text-sm">No replies yet. Our support team will respond soon!</p>
                        </div>
                    @endforelse
                </div>

                {{-- Reply Form --}}
                @if($record->isOpen())
                    <div class="p-4 border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        <form wire:submit="sendMessage">
                            <div class="space-y-3">
                                <div>
                                    <label for="newMessage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Add a reply
                                    </label>
                                    <textarea
                                        id="newMessage"
                                        wire:model="newMessage"
                                        rows="3"
                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        placeholder="Type your message here..."
                                    ></textarea>
                                    @error('newMessage')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex items-center justify-end">
                                    <button 
                                        type="submit"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        wire:loading.attr="disabled"
                                    >
                                        <x-filament::icon icon="heroicon-o-paper-airplane" class="w-4 h-4" wire:loading.remove />
                                        <x-filament::loading-indicator class="h-4 w-4" wire:loading />
                                        <span wire:loading.remove>Send Reply</span>
                                        <span wire:loading>Sending...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <x-filament::icon icon="heroicon-o-check-circle" class="w-4 h-4 inline" />
                            This ticket is {{ $record->status->getLabel() }}. 
                            @if($record->status->value === 'closed')
                                Contact support if you need to reopen it.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar: Ticket Information --}}
        <div class="space-y-6">
            {{-- Ticket Status --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ticket Status</h3>
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Current Status</dt>
                        <dd class="mt-1">
                            <x-filament::badge :color="$record->status->getColor()">
                                {{ $record->status->getLabel() }}
                            </x-filament::badge>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Priority</dt>
                        <dd class="mt-1">
                            <x-filament::badge :color="$record->priority->getColor()">
                                {{ $record->priority->getLabel() }}
                            </x-filament::badge>
                        </dd>
                    </div>

                    @if($record->assignedTo)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Assigned Support Agent</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->assignedTo->name }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Ticket Details --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Details</h3>
                <dl class="space-y-3">
                    @if($record->park_id)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Park ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">#{{ $record->park_id }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->created_at->format('M d, Y H:i') }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->updated_at->diffForHumans() }}</dd>
                    </div>

                    @if($record->closed_at)
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Closed</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $record->closed_at->format('M d, Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Help Text --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <x-filament::icon icon="heroicon-o-information-circle" class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                    <div>
                        <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-1">Need Help?</h4>
                        <p class="text-xs text-blue-700 dark:text-blue-300">
                            Our support team typically responds within 24 hours. You'll receive a notification when there's an update to your ticket.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
