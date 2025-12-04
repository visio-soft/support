<?php

namespace VisioSoft\Support\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use VisioSoft\Support\Enums\SupportPriority;
use VisioSoft\Support\Enums\SupportStatus;
use VisioSoft\Support\Filament\Admin\Resources\PartnerSupportResource\Pages;
use VisioSoft\Support\Models\PartnerSupport;

class PartnerSupportResource extends Resource
{
    protected static ?string $model = PartnerSupport::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationLabel = 'Destek Talepleri';

    protected static ?string $modelLabel = 'Destek Talebi';

    protected static ?string $pluralModelLabel = 'Destek Talepleri';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Talep Bilgileri')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Konu')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('user_id')
                            ->label('Kullanıcı')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('park_id')
                            ->label('Park')
                            ->relationship('park', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Select::make('status')
                            ->label('Durum')
                            ->options(SupportStatus::toSelectArray())
                            ->default(SupportStatus::OPEN->value)
                            ->required(),

                        Forms\Components\Select::make('priority')
                            ->label('Öncelik')
                            ->options(SupportPriority::toSelectArray())
                            ->default(SupportPriority::NORMAL->value)
                            ->required(),

                        Forms\Components\Select::make('assigned_to')
                            ->label('Atanan Kişi')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Yönetici seçin'),

                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->label('Açıklama')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Talep #')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Müşteri')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Konu')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn (SupportStatus $state) => $state->getLabel())
                    ->color(fn (SupportStatus $state) => $state->getColor())
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Öncelik')
                    ->badge()
                    ->formatStateUsing(fn (SupportPriority $state) => $state->getLabel())
                    ->color(fn (SupportPriority $state) => $state->getColor())
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Atanan Kişi')
                    ->placeholder('Atanmamış')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('replies_count')
                    ->counts('replies')
                    ->label('Yanıtlar')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma Tarihi')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Güncellenme Tarihi')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Durum')
                    ->options(SupportStatus::toSelectArray())
                    ->multiple(),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('Öncelik')
                    ->options(SupportPriority::toSelectArray())
                    ->multiple(),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Atanan Kişi')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('unassigned')
                    ->label('Atanmamış Talepler')
                    ->placeholder('Tüm talepler')
                    ->trueLabel('Sadece atanmamışları göster')
                    ->falseLabel('Sadece atanmışları göster')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('assigned_to'),
                        false: fn (Builder $query) => $query->whereNotNull('assigned_to'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('assign')
                    ->label('Ata')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Forms\Components\Select::make('assigned_to')
                            ->label('Kime Ata')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])
                    ->action(function (PartnerSupport $record, array $data) {
                        $record->update([
                            'assigned_to' => $data['assigned_to'],
                            'status' => SupportStatus::IN_PROGRESS,
                        ]);
                    }),
                Tables\Actions\Action::make('close')
                    ->label('Kapat')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (PartnerSupport $record) => $record->isOpen())
                    ->action(function (PartnerSupport $record) {
                        $record->close(auth()->id());
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('assignBulk')
                        ->label('Seçilenleri Ata')
                        ->icon('heroicon-o-user-plus')
                        ->form([
                            Forms\Components\Select::make('assigned_to')
                                ->label('Kime Ata')
                                ->relationship('assignedTo', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'assigned_to' => $data['assigned_to'],
                                    'status' => SupportStatus::IN_PROGRESS,
                                ]);
                            }
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartnerSupports::route('/'),
            'create' => Pages\CreatePartnerSupport::route('/create'),
            'view' => Pages\ViewPartnerSupport::route('/{record}'),
            'edit' => Pages\EditPartnerSupport::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('replies');
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::open()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
