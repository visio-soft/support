<?php

namespace VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\Validate;
use VisioSoft\Support\Enums\SupportStatus;
use VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource;
use VisioSoft\Support\Models\PartnerSupportReply;

class ViewPartnerSupport extends ViewRecord
{
    protected static string $resource = PartnerSupportResource::class;

    protected static string $view = 'support::filament.partner.pages.view-partner-support';

    #[Validate('required|string|min:3')]
    public $newMessage = '';

    public $attachments = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('closeTicket')
                ->label('Talebi Kapat')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalHeading('Talebi Kapat')
                ->modalDescription('Bu destek talebini kapatmak istediğinize emin misiniz? Kapatılan taleplere yeni yanıt eklenemez.')
                ->modalSubmitActionLabel('Evet, Kapat')
                ->visible(fn () => $this->record->isOpen())
                ->action(function () {
                    $this->record->update([
                        'status' => SupportStatus::CLOSED,
                        'closed_at' => now(),
                        'closed_by_id' => auth()->id(),
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Talep Kapatıldı')
                        ->body('Destek talebi başarıyla kapatıldı.')
                        ->send();
                }),
        ];
    }

    public function sendMessage(): void
    {
        $this->validate();

        if (empty(trim($this->newMessage))) {
            Notification::make()
                ->danger()
                ->title('Mesaj boş olamaz')
                ->send();
            return;
        }

        PartnerSupportReply::create([
            'partner_support_id' => $this->record->id,
            'user_id' => auth()->id(),
            'content' => $this->newMessage,
            'is_admin_reply' => false,
            'is_internal_note' => false,
            'attachments' => !empty($this->attachments) ? $this->attachments : null,
        ]);

        // Update ticket status to waiting for admin
        $this->record->update([
            'status' => SupportStatus::WAITING_ADMIN,
        ]);

        Notification::make()
            ->success()
            ->title('Yanıt başarıyla gönderildi')
            ->send();

        // Reset form
        $this->newMessage = '';
        $this->attachments = [];

        // Refresh the record to show new reply
        $this->record->refresh();
        $this->dispatch('reply-sent');
    }

    public function rateReply(int $replyId, int $rating): void
    {
        $reply = \VisioSoft\Support\Models\PartnerSupportReply::find($replyId);

        if (!$reply || !$reply->is_admin_reply) {
            return;
        }

        $reply->update(['rating' => $rating]);

        // Force parent update to trigger Livewire re-render
        $this->record->touch();
        $this->record->refresh();
        $this->record->unsetRelation('publicReplies');

        Notification::make()
            ->success()
            ->title('Teşekkürler')
            ->body('Geri bildiriminiz için teşekkür ederiz.')
            ->send();
    }
}
