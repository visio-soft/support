<x-filament-panels::page>
    <div x-data="{
        ratingModalOpen: false,
        ratingReplyId: null,
        ratingValue: 0,
        hoverValue: 0,
        openRatingModal(replyId, currentRating) {
            this.ratingReplyId = replyId;
            this.ratingValue = currentRating || 0;
            this.hoverValue = 0;
            this.ratingModalOpen = true;
        },
        closeRatingModal() {
            this.ratingModalOpen = false;
            this.ratingReplyId = null;
            this.ratingValue = 0;
            this.hoverValue = 0;
        },
        setRating(value) {
            this.ratingValue = value;
        },
        submitRating() {
            if (this.ratingValue > 0) {
                $wire.rateReply(this.ratingReplyId, this.ratingValue);
                this.closeRatingModal();
            }
        }
    }" class="space-y-6">
        {{-- Conversation Thread --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="w-5 h-5" />
                    <span>Konuşma</span>
                    <span class="text-gray-500 font-normal text-xs">({{ $record->publicReplies()->count() }} yanıt)</span>
                </h3>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($record->publicReplies()->orderBy('created_at', 'asc')->get() as $reply)
                        <div class="p-4" wire:key="reply-{{ $reply->id }}-{{ $reply->rating ?? 'none' }}">
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
                                            <x-filament::badge color="success" size="xs">Destek Ekibi</x-filament::badge>
                                        @else
                                            <x-filament::badge color="info" size="xs">Siz</x-filament::badge>
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

                                    @if($reply->is_admin_reply)
                                        <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $reply->rating ? 'Verdiğiniz Puan:' : 'Bu yanıtı değerlendirin:' }}
                                                </span>
                                            <button 
                                                type="button"
                                                @click="openRatingModal({{ $reply->id }}, {{ $reply->rating ?? 0 }})"
                                                class="flex items-center gap-1 hover:opacity-80 transition-opacity group"
                                            >
                                                @php
                                                    $userRating = $reply->rating ?? 0;
                                                @endphp
                                                @foreach(range(1, 5) as $star)
                                                    @if($star <= $userRating)
                                                        {{-- Filled Star (Yellow) --}}
                                                        <svg 
                                                            class="w-5 h-5" 
                                                            style="color: #facc15;"
                                                            xmlns="http://www.w3.org/2000/svg" 
                                                            viewBox="0 0 24 24" 
                                                            fill="#facc15" 
                                                            aria-hidden="true"
                                                        >
                                                            <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                                        </svg>
                                                    @else
                                                        {{-- Empty Star (Gray) --}}
                                                        <svg 
                                                            class="w-5 h-5 text-gray-300 dark:text-gray-600 group-hover:text-gray-400 transition-colors" 
                                                            xmlns="http://www.w3.org/2000/svg" 
                                                            viewBox="0 0 24 24" 
                                                            fill="currentColor" 
                                                            aria-hidden="true"
                                                        >
                                                            <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                                        </svg>
                                                    @endif
                                                @endforeach
                                            </button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                @endforelse

                @if($record->status === \VisioSoft\Support\Enums\SupportStatus::CLOSED && $record->closed_by == auth()->id())
                    <div class="p-4 bg-gray-50 dark:bg-gray-900/50 text-center border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center justify-center gap-2">
                            <x-filament::icon icon="heroicon-o-x-circle" class="w-4 h-4" />
                            Talep sizin tarafınızdan kapatıldı.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Reply Form --}}
            @if($record->isOpen())
                <div class="p-4 border-t-2 border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                    <form wire:submit="sendMessage">
                        <div class="space-y-3">
                            <div>
                                <label for="newMessage" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Yanıt ekle
                                </label>
                                <textarea
                                    id="newMessage"
                                    wire:model="newMessage"
                                    rows="3"
                                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Mesajınızı buraya yazın..."
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
                                    <span wire:loading.remove>Yanıt Gönder</span>
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
                        Bu talep kapalı. Yeniden açmanız gerekirse lütfen
                        <a href="{{ \VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource::getUrl('create') }}" class="text-primary-600 hover:text-primary-500 font-medium">yeni bir bildirim açınız</a>.
                    </p>
                </div>
            @endif
        </div>

        {{-- Combined Ticket Details --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
    <div class="p-6 flex flex-col md:flex-row">
        <!-- Left column: ticket details -->
        <div class="md:w-1/3 space-y-4">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Talep #{{ $record->id }}</span>
                <x-filament::badge :color="$record->status->getColor()">
                    {{ $record->status->getLabel() }}
                </x-filament::badge>
                <x-filament::badge :color="$record->priority->getColor()">
                    {{ $record->priority->getLabel() }}
                </x-filament::badge>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                <p><strong>Durum:</strong> {{ $record->status->getLabel() }}</p>
                <p><strong>Öncelik:</strong> {{ $record->priority->getLabel() }}</p>
                <p><strong>Müşteri:</strong> {{ $record->user->name }}</p>
                <p><strong>Atanan Kişi:</strong> {{ $record->assignedTo ? $record->assignedTo->name : 'Atanmamış' }}</p>
                <p><strong>Park ID:</strong> #{{ $record->park_id ?? '' }}</p>
                <p><strong>Oluşturma Tarih Saat:</strong> {{ $record->created_at->format('d.m.Y H:i') }}</p>
                <p><strong>Son Güncelleme:</strong> {{ $record->updated_at->diffForHumans() }}</p>
            </div>

        </div>
        <!-- Right column: ticket content -->
        <div class="md:w-2/3 md:pl-6 border-t md:border-t-0 md:border-l border-gray-200 dark:border-gray-700 mt-6 md:mt-0 pt-4 md:pt-0 md:pl-4">
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

        {{-- Rating Modal --}}
        <div
            x-show="ratingModalOpen"
            style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title"
            role="dialog"
            aria-modal="true"
        >
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div
                    x-show="ratingModalOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    @click="closeRatingModal()"
                    aria-hidden="true"
                ></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    x-show="ratingModalOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                >
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                    Yanıtı Değerlendir
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                        Bu yanıtın size ne kadar yardımcı olduğunu değerlendirin.
                                    </p>

                                    <div class="flex justify-center sm:justify-start gap-2">
                                        <template x-for="star in [1, 2, 3, 4, 5]">
                                            <button 
                                                type="button"
                                                @click="setRating(star)"
                                                @mouseenter="hoverValue = star"
                                                @mouseleave="hoverValue = 0"
                                                class="focus:outline-none transition-transform hover:scale-110 p-1"
                                            >
                                                <svg 
                                                    class="w-8 h-8 transition-colors duration-200" 
                                                    x-bind:class="(hoverValue > 0 ? hoverValue >= star : ratingValue >= star) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'"
                                                    x-bind:style="(hoverValue > 0 ? hoverValue >= star : ratingValue >= star) ? 'color: #facc15' : ''"
                                                    xmlns="http://www.w3.org/2000/svg" 
                                                    viewBox="0 0 24 24" 
                                                    fill="currentColor" 
                                                    aria-hidden="true"
                                                >
                                                    <path fill-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </template>
                                    </div>
                                    <p class="mt-2 text-sm font-medium text-yellow-600 dark:text-yellow-400 h-5">
                                        <span x-show="(hoverValue || ratingValue) === 1">Çok Kötü</span>
                                        <span x-show="(hoverValue || ratingValue) === 2">Kötü</span>
                                        <span x-show="(hoverValue || ratingValue) === 3">Orta</span>
                                        <span x-show="(hoverValue || ratingValue) === 4">İyi</span>
                                        <span x-show="(hoverValue || ratingValue) === 5">Çok İyi</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button 
                            type="button" 
                            @click="submitRating()"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto"
                            x-bind:disabled="ratingValue === 0"
                        >
                            Değerlendir
                        </button>
                        <button 
                            type="button" 
                            @click="closeRatingModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:w-auto"
                        >
                            İptal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
