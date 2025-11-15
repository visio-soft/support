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
                ->label('Assign')
                ->icon('heroicon-m-user-plus')
                ->form([
                    Forms\Components\Select::make('assigned_to')
                        ->label('Assign to')
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
                        ->title('Ticket assigned successfully')
                        ->send();
                })
                ->visible(fn (): bool => $this->record->assigned_to === null),

            Actions\Action::make('assignToMe')
                ->label('Assign to Me')
                ->icon('heroicon-m-user-plus')
                ->color('info')
                ->visible(fn () => $this->record->assigned_to !== auth()->id() && $this->record->isOpen())
                ->action(function () {
                    $this->record->update([
                        'assigned_to' => auth()->id(),
                        'status' => SupportStatus::IN_PROGRESS,
                    ]);

                    Notification::make()
                        ->title('Ticket assigned to you')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('close')
                ->label('Close Ticket')
                ->icon('heroicon-m-lock-closed')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->isOpen())
                ->action(function () {
                    $this->record->close(auth()->id());

                    Notification::make()
                        ->success()
                        ->title('Ticket closed successfully')
                        ->send();
                }),

            Actions\Action::make('reopen')
                ->label('Reopen Ticket')
                ->icon('heroicon-m-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->isClosed())
                ->action(function () {
                    $this->record->reopen();

                    Notification::make()
                        ->title('Ticket reopened successfully')
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
                ->title('Message cannot be empty')
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
            ->title($this->isInternalNote ? 'Internal note added' : 'Reply sent successfully')
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
            ->title('Status updated successfully')
            ->send();
    }
}
