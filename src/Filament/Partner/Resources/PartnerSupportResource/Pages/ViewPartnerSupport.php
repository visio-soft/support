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
            Actions\EditAction::make()
                ->visible(fn () => $this->record->isOpen()),
        ];
    }

    public function sendMessage(): void
    {
        $this->validate();

        if (empty(trim($this->newMessage))) {
            Notification::make()
                ->danger()
                ->title('Message cannot be empty')
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
            ->title('Reply sent successfully')
            ->send();

        // Reset form
        $this->newMessage = '';
        $this->attachments = [];

        // Refresh the record to show new reply
        $this->record->refresh();
    }
}
