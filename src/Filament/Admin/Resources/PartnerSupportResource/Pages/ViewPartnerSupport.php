<?php

namespace VisioSoft\Support\Filament\Admin\Resources\PartnerSupportResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use VisioSoft\Support\Enums\SupportStatus;
use VisioSoft\Support\Filament\Admin\Resources\PartnerSupportResource;
use VisioSoft\Support\Models\PartnerSupportReply;

class ViewPartnerSupport extends ViewRecord
{
    protected static string $resource = PartnerSupportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('assignToMe')
                ->label('Assign to Me')
                ->icon('heroicon-o-user-plus')
                ->color('info')
                ->visible(fn () => $this->record->assigned_to !== auth()->id())
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
            Actions\Action::make('addReply')
                ->label('Add Reply')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->visible(fn () => $this->record->isOpen())
                ->form([
                    Forms\Components\RichEditor::make('content')
                        ->required()
                        ->label('Your Reply')
                        ->placeholder('Type your reply here...'),

                    Forms\Components\Toggle::make('is_internal_note')
                        ->label('Internal Note (Not visible to customer)')
                        ->default(false)
                        ->helperText('Internal notes are only visible to admin users'),

                    Forms\Components\FileUpload::make('attachments')
                        ->label('Attachments')
                        ->multiple()
                        ->disk(config('support.attachments.disk', 'public'))
                        ->directory(config('support.attachments.path', 'support-attachments'))
                        ->maxSize(config('support.attachments.max_size', 10240))
                        ->acceptedFileTypes(array_map(fn ($type) => ".$type", config('support.attachments.allowed_types', [])))
                        ->visibility('private')
                        ->helperText('Max file size: ' . (config('support.attachments.max_size', 10240) / 1024) . 'MB'),

                    Forms\Components\Select::make('update_status')
                        ->label('Update Ticket Status')
                        ->options(SupportStatus::toSelectArray())
                        ->default($this->record->status->value)
                        ->required(),
                ])
                ->action(function (array $data) {
                    PartnerSupportReply::create([
                        'partner_support_id' => $this->record->id,
                        'user_id' => auth()->id(),
                        'content' => $data['content'],
                        'is_admin_reply' => true,
                        'is_internal_note' => $data['is_internal_note'] ?? false,
                        'attachments' => $data['attachments'] ?? null,
                    ]);

                    // Update ticket status
                    $this->record->update([
                        'status' => $data['update_status'],
                    ]);

                    Notification::make()
                        ->title('Reply added successfully')
                        ->success()
                        ->send();

                    return redirect()->to(static::getResource()::getUrl('view', ['record' => $this->record]));
                }),
            Actions\Action::make('close')
                ->label('Close Ticket')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->record->isOpen())
                ->action(function () {
                    $this->record->close(auth()->id());

                    Notification::make()
                        ->title('Ticket closed successfully')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('reopen')
                ->label('Reopen Ticket')
                ->icon('heroicon-o-arrow-path')
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

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Ticket Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('Ticket #'),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Customer'),

                        Infolists\Components\TextEntry::make('subject')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(fn (SupportStatus $state) => $state->getLabel())
                            ->color(fn (SupportStatus $state) => $state->getColor()),

                        Infolists\Components\TextEntry::make('priority')
                            ->badge()
                            ->formatStateUsing(fn ($state) => $state->getLabel())
                            ->color(fn ($state) => $state->getColor()),

                        Infolists\Components\TextEntry::make('park_id')
                            ->label('Park ID')
                            ->placeholder('N/A'),

                        Infolists\Components\TextEntry::make('assignedTo.name')
                            ->label('Assigned To')
                            ->placeholder('Not assigned'),

                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime(),

                        Infolists\Components\TextEntry::make('closed_at')
                            ->dateTime()
                            ->placeholder('Not closed'),

                        Infolists\Components\TextEntry::make('closedBy.name')
                            ->label('Closed By')
                            ->placeholder('N/A'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Description')
                    ->schema([
                        Infolists\Components\TextEntry::make('content')
                            ->html()
                            ->hiddenLabel(),
                    ]),

                Infolists\Components\Section::make('Public Replies')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('publicReplies')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('From')
                                    ->badge()
                                    ->color(fn (PartnerSupportReply $record) => $record->is_admin_reply ? 'success' : 'info'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Date')
                                    ->dateTime(),

                                Infolists\Components\TextEntry::make('content')
                                    ->html()
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('attachments')
                                    ->label('Attachments')
                                    ->formatStateUsing(function ($state) {
                                        if (empty($state)) {
                                            return null;
                                        }
                                        $disk = config('support.attachments.disk', 'public');
                                        $html = '<div class="space-y-1">';
                                        foreach ($state as $attachment) {
                                            $url = \Storage::disk($disk)->url($attachment);
                                            $filename = basename($attachment);
                                            $html .= '<div><a href="' . $url . '" target="_blank" class="text-primary-600 hover:underline">' . $filename . '</a></div>';
                                        }
                                        $html .= '</div>';
                                        return $html;
                                    })
                                    ->html()
                                    ->visible(fn ($state) => !empty($state))
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn () => $this->record->publicReplies()->count() > 0)
                    ->collapsible(),

                Infolists\Components\Section::make('Internal Notes')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('internalNotes')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('user.name')
                                    ->label('Admin')
                                    ->badge()
                                    ->color('warning'),

                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('Date')
                                    ->dateTime(),

                                Infolists\Components\TextEntry::make('content')
                                    ->html()
                                    ->columnSpanFull(),

                                Infolists\Components\TextEntry::make('attachments')
                                    ->label('Attachments')
                                    ->formatStateUsing(function ($state) {
                                        if (empty($state)) {
                                            return null;
                                        }
                                        $disk = config('support.attachments.disk', 'public');
                                        $html = '<div class="space-y-1">';
                                        foreach ($state as $attachment) {
                                            $url = \Storage::disk($disk)->url($attachment);
                                            $filename = basename($attachment);
                                            $html .= '<div><a href="' . $url . '" target="_blank" class="text-primary-600 hover:underline">' . $filename . '</a></div>';
                                        }
                                        $html .= '</div>';
                                        return $html;
                                    })
                                    ->html()
                                    ->visible(fn ($state) => !empty($state))
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn () => $this->record->internalNotes()->count() > 0)
                    ->collapsible(),
            ]);
    }
}
