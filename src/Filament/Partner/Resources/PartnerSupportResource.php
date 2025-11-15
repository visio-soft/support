<?php

namespace VisioSoft\Support\Filament\Partner\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use VisioSoft\Support\Enums\SupportPriority;
use VisioSoft\Support\Enums\SupportStatus;
use VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource\Pages;
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
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => auth()->id()),

                        Forms\Components\TextInput::make('park_id')
                            ->label('Park ID')
                            ->numeric()
                            ->placeholder('Enter park ID if applicable'),

                        Forms\Components\TextInput::make('subject')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Brief description of your issue'),

                        Forms\Components\Select::make('priority')
                            ->options(SupportPriority::toSelectArray())
                            ->default(SupportPriority::NORMAL->value)
                            ->required(),

                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->label('Description')
                            ->placeholder('Please provide detailed information about your issue')
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (PartnerSupport $record) => $record->isOpen()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->forUser(auth()->id()));
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
            ->forUser(auth()->id())
            ->withCount('replies');
    }
}
