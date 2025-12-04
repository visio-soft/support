<?php

namespace VisioSoft\Support\Filament\Admin\Resources\PartnerSupportResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Livewire\Attributes\Validate;
use VisioSoft\Support\Enums\SupportStatus;
use VisioSoft\Support\Filament\Admin\Resources\PartnerSupportResource;
use VisioSoft\Support\Models\PartnerSupportReply;

class ViewPartnerSupport extends ViewRecord
{
    protected static string $resource = PartnerSupportResource::class;

    protected static string $view = 'support::filament.admin.pages.view-partner-support';

    #[Validate('required|string|min:3')]
    public $newMessage = '';

    public $isInternalNote = false;

    public $attachments = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('assign')
                ->label('Ata')
                ->icon('heroicon-m-user-plus')
                ->form([
                    Forms\Components\Select::make('assigned_to')
                        ->label('Kime Ata')
                        ->options(\App\Models\User::pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'assigned_to' => $data['assigned_to'],
                        'status' => SupportStatus::IN_PROGRESS,
                    ]);

                    Notification::make()
                        ->success()
                        ->title('Talep başarıyla atandı')
                        ->send();
                })
                ->visible(fn (): bool => $this->record->assigned_to === null),

            Actions\Action::make('assignToMe')
                ->label('Bana Ata')
                ->icon('heroicon-m-user-plus')
                ->color('info')
                ->visible(fn () => $this->record->assigned_to !== auth()->id() && $this->record->isOpen())
                ->action(function () {
                    $this->record->update([
                        'assigned_to' => auth()->id(),
                        'status' => SupportStatus::IN_PROGRESS,
                    ]);

                    Notification::make()
                        ->title('Talep size atandı')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('close')
                ->label('Talebi Kapat')
                ->icon('heroicon-m-lock-closed')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->isOpen())
                ->action(function () {
                    $this->record->close(auth()->id());

                    Notification::make()
                        ->success()
                        ->title('Talep başarıyla kapatıldı')
                        ->send();
                }),

            Actions\Action::make('reopen')
                ->label('Talebi Yeniden Aç')
                ->icon('heroicon-m-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->isClosed())
                ->action(function () {
                    $this->record->reopen();

                    Notification::make()
                        ->title('Talep başarıyla yeniden açıldı')
                        ->success()
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
            'is_admin_reply' => true,
            'is_internal_note' => $this->isInternalNote,
            'attachments' => !empty($this->attachments) ? $this->attachments : null,
        ]);

        // Update ticket status if it's a public reply
        if (!$this->isInternalNote && $this->record->status->value === SupportStatus::WAITING_ADMIN->value) {
            $this->record->update([
                'status' => SupportStatus::IN_PROGRESS,
            ]);
        }

        Notification::make()
            ->success()
            ->title($this->isInternalNote ? 'Dahili not eklendi' : 'Yanıt başarıyla gönderildi')
            ->send();

        // Reset form
        $this->newMessage = '';
        $this->isInternalNote = false;
        $this->attachments = [];

        // Refresh the record to show new reply
        $this->record->refresh();
    }

    public function changeStatus($status): void
    {
        $this->record->update(['status' => $status]);

        Notification::make()
            ->success()
            ->title('Durum başarıyla güncellendi')
            ->send();
    }
}
