<?php

namespace VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource\Pages;

use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use VisioSoft\Support\Enums\SupportStatus;
use VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource;
use VisioSoft\Support\Models\PartnerSupportReply;

class ViewPartnerSupport extends ViewRecord
{
    protected static string $resource = PartnerSupportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->isOpen()),
            Actions\Action::make('addReply')
                ->label('Add Reply')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->visible(fn () => $this->record->isOpen())
                ->form([
                    Forms\Components\RichEditor::make('content')
                        ->required()
                        ->label('Your Reply')
                        ->placeholder('Type your reply here...'),

                    Forms\Components\FileUpload::make('attachments')
                        ->label('Attachments')
                        ->multiple()
                        ->disk(config('support.attachments.disk', 'public'))
                        ->directory(config('support.attachments.path', 'support-attachments'))
                        ->maxSize(config('support.attachments.max_size', 10240))
                        ->acceptedFileTypes(array_map(fn ($type) => ".$type", config('support.attachments.allowed_types', [])))
                        ->visibility('private')
                        ->helperText('Max file size: ' . (config('support.attachments.max_size', 10240) / 1024) . 'MB'),
                ])
                ->action(function (array $data) {
                    PartnerSupportReply::create([
                        'partner_support_id' => $this->record->id,
                        'user_id' => auth()->id(),
                        'content' => $data['content'],
                        'is_admin_reply' => false,
                        'is_internal_note' => false,
                        'attachments' => $data['attachments'] ?? null,
                    ]);

                    // Update ticket status to waiting for admin
                    $this->record->update([
                        'status' => SupportStatus::WAITING_ADMIN,
                    ]);

                    Notification::make()
                        ->title('Reply added successfully')
                        ->success()
                        ->send();

                    return redirect()->to(static::getResource()::getUrl('view', ['record' => $this->record]));
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
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Description')
                    ->schema([
                        Infolists\Components\TextEntry::make('content')
                            ->html()
                            ->hiddenLabel(),
                    ]),

                Infolists\Components\Section::make('Replies')
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
            ]);
    }
}
