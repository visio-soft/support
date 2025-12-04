<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Ticket Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Conversation Thread --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="w-5 h-5" />
                        <span>Konuşma</span>
                        <span class="text-gray-500 font-normal text-xs">({{ $record->replies()->count() }} yanıt)</span>
                    </h3>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($record->replies()->orderBy('created_at', 'asc')->get() as $reply)
                        <div class="p-4 {{ $reply->is_internal_note ? 'bg-yellow-50 dark:bg-yellow-900/10' : '' }}">
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
                                            <x-filament::badge color="success" size="xs">Yönetici</x-filament::badge>
                                        @endif
                                        @if($reply->is_admin_reply && $reply->rating)
                                            <div class="flex items-center gap-1 ml-2 px-2 py-0.5 bg-yellow-50 dark:bg-yellow-900/20 rounded border border-yellow-100 dark:border-yellow-900/30">
                                                <span class="text-xs font-medium text-yellow-700 dark:text-yellow-500">{{ $reply->rating }}</span>
                                                <x-filament::icon icon="heroicon-s-star" class="w-3 h-3 text-yellow-400" />
                                            </div>
                                        @endif
                                        @if($reply->is_internal_note)
                                            <x-filament::badge color="warning" size="xs">
                                                <x-filament::icon icon="heroicon-o-lock-closed" class="w-3 h-3 mr-1" />
                                                Dahili Not (Sadece Yöneticiler)
                                            </x-filament::badge>
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
                    @endforelse
                </div>

                {{-- Reply Form --}}
                @if($record->isOpen())
                    <div class="p-4 border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        <form wire:submit="sendMessage">
                            <div class="space-y-3">
                                <div>
                                    <textarea
                                        wire:model="newMessage"
                                        rows="3"
                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        placeholder="Mesajınızı buraya yazın..."
                                    ></textarea>
                                    @error('newMessage')
                                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input 
                                                type="checkbox" 
                                                wire:model="isInternalNote"
                                                class="rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 dark:bg-gray-800"
                                            >
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Dahili Not</span>
                                        </label>
                                        @if($isInternalNote)
                                            <span class="text-xs text-yellow-600 dark:text-yellow-400">
                                                <x-filament::icon icon="heroicon-o-lock-closed" class="w-3 h-3 inline" />
                                                Sadece yöneticiler görebilir
                                            </span>
                                        @endif
                                    </div>

                                    <button 
                                        type="submit"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        wire:loading.attr="disabled"
                                    >
                                        <x-filament::icon icon="heroicon-o-paper-airplane" class="w-4 h-4" wire:loading.remove />
                                        <x-filament::loading-indicator class="h-4 w-4" wire:loading />
                                        <span wire:loading.remove>{{ $isInternalNote ? 'Not' : 'Yanıt' }} Gönder</span>
                                        <span wire:loading>Gönderiliyor...</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            <x-filament::icon icon="heroicon-o-lock-closed" class="w-4 h-4 inline" />
                            Bu talep kapalı. Yanıt eklemek için yeniden açın.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Ticket Header & Request --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Talep #{{ $record->id }}</span>
                                <x-filament::badge :color="$record->status->getColor()">
                                    {{ $record->status->getLabel() }}
                                </x-filament::badge>
                                <x-filament::badge :color="$record->priority->getColor()">
                                    {{ $record->priority->getLabel() }}
                                </x-filament::badge>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $record->subject }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $record->user->name }}</span> tarafından {{ $record->created_at->format('d.m.Y H:i') }} tarihinde oluşturuldu
                            </p>
                        </div>
                    </div>

                    {{-- Quick Status Actions --}}
                    @if($record->isOpen())
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Hızlı İşlemler</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach(\VisioSoft\Support\Enums\SupportStatus::cases() as $status)
                                    @if($status->value !== $record->status->value)
                                        <button 
                                            wire:click="changeStatus('{{ $status->value }}')"
                                            type="button"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md border transition-colors
                                                {{ $status->getColor() === 'success' ? 'border-green-300 text-green-700 hover:bg-green-50 dark:border-green-700 dark:text-green-400 dark:hover:bg-green-900/50' : '' }}
                                                {{ $status->getColor() === 'warning' ? 'border-yellow-300 text-yellow-700 hover:bg-yellow-50 dark:border-yellow-700 dark:text-yellow-400 dark:hover:bg-yellow-900/50' : '' }}
                                                {{ $status->getColor() === 'danger' ? 'border-red-300 text-red-700 hover:bg-red-50 dark:border-red-700 dark:text-red-400 dark:hover:bg-red-900/50' : '' }}
                                                {{ $status->getColor() === 'info' ? 'border-blue-300 text-blue-700 hover:bg-blue-50 dark:border-blue-700 dark:text-blue-400 dark:hover:bg-blue-900/50' : '' }}
                                                {{ $status->getColor() === 'gray' ? 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-900/50' : '' }}">
                                            {{ $status->getLabel() }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="p-6 bg-gray-50/50 dark:bg-gray-900/10">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5 text-gray-400" />
                        Talep İçeriği
                    </h3>
                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 text-sm">
                        {!! $record->content !!}
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Sidebar Info --}}
        <div class="space-y-6">
            {{-- Ticket Info --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Talep Bilgileri</h3>
                <dl class="space-y-4">
                    <div class="flex items-start gap-6">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Durum</dt>
                            <dd>
                                <x-filament::badge :color="$record->status->getColor()">
                                    {{ $record->status->getLabel() }}
                                </x-filament::badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Öncelik</dt>
                            <dd>
                                <x-filament::badge :color="$record->priority->getColor()">
                                    {{ $record->priority->getLabel() }}
                                </x-filament::badge>
                            </dd>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700 space-y-3">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Müşteri</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $record->user->name }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Atanan Kişi</dt>
                            <dd class="text-sm text-gray-900 dark:text-white mt-0.5">{{ $record->assignedTo?->name ?? 'Atanmamış' }}</dd>
                        </div>

                        @if($record->park_id)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Park ID</dt>
                                <dd class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">#{{ $record->park_id }}</dd>
                            </div>
                        @endif

                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Oluşturma Tarih Saat</dt>
                            <dd class="text-sm text-gray-900 dark:text-white mt-0.5">{{ $record->created_at->format('d.m.Y H:i') }}</dd>
                        </div>

                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Son Güncelleme</dt>
                            <dd class="text-sm text-gray-900 dark:text-white mt-0.5">{{ $record->updated_at->diffForHumans() }}</dd>
                        </div>

                        @if($record->closed_at)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Kapatıldı</dt>
                                <dd class="text-sm text-gray-900 dark:text-white mt-0.5">{{ $record->closed_at->format('d.m.Y H:i') }}</dd>
                            </div>
                        @endif

                        @if($record->closedBy)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Kapatan Kişi</dt>
                                <dd class="text-sm text-gray-900 dark:text-white mt-0.5">{{ $record->closedBy->name }}</dd>
                            </div>
                        @endif
                    </div>
                </dl>
            </div>

            {{-- Activity Summary --}}
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Aktivite</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Toplam Yanıt</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $record->replies()->count() }}</dd>
                    </div>
                    
                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Herkese Açık Yanıtlar</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $record->publicReplies()->count() }}</dd>
                    </div>

                    <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                        <dt class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Dahili Notlar</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $record->internalNotes()->count() }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-filament-panels::page>
