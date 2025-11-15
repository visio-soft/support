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

    protected static ?string $navigationLabel = 'Support Tickets';

    protected static ?string $modelLabel = 'Support Ticket';

    protected static ?string $pluralModelLabel = 'Support Tickets';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ticket Information')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('park_id')
                            ->label('Park ID')
                            ->numeric(),

                        Forms\Components\Select::make('status')
                            ->options(SupportStatus::toSelectArray())
                            ->default(SupportStatus::OPEN->value)
                            ->required(),

                        Forms\Components\Select::make('priority')
                            ->options(SupportPriority::toSelectArray())
                            ->default(SupportPriority::NORMAL->value)
                            ->required(),

                        Forms\Components\Select::make('assigned_to')
                            ->label('Assigned To')
                            ->relationship('assignedTo', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select admin user'),

                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->label('Description')
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
                    ->label('Ticket #')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (SupportStatus $state) => $state->getLabel())
                    ->color(fn (SupportStatus $state) => $state->getColor())
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->formatStateUsing(fn (SupportPriority $state) => $state->getLabel())
                    ->color(fn (SupportPriority $state) => $state->getColor())
                    ->sortable(),

                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('Assigned To')
                    ->placeholder('Unassigned')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('replies_count')
                    ->counts('replies')
                    ->label('Replies')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(SupportStatus::toSelectArray())
                    ->multiple(),

                Tables\Filters\SelectFilter::make('priority')
                    ->options(SupportPriority::toSelectArray())
                    ->multiple(),

                Tables\Filters\SelectFilter::make('assigned_to')
                    ->label('Assigned To')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('unassigned')
                    ->label('Unassigned Tickets')
                    ->placeholder('All tickets')
                    ->trueLabel('Show only unassigned')
                    ->falseLabel('Show only assigned')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNull('assigned_to'),
                        false: fn (Builder $query) => $query->whereNotNull('assigned_to'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('assign')
                    ->icon('heroicon-o-user-plus')
                    ->form([
                        Forms\Components\Select::make('assigned_to')
                            ->label('Assign To')
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
                        ->label('Assign Selected')
                        ->icon('heroicon-o-user-plus')
                        ->form([
                            Forms\Components\Select::make('assigned_to')
                                ->label('Assign To')
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
