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
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn () => auth()->id()),

                        Forms\Components\Hidden::make('park_id')
                            ->default(fn () => \Filament\Facades\Filament::getTenant()->id),

                        Forms\Components\TextInput::make('subject')
                            ->label('Konu')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Sorununuzla ilgili kısa bir açıklama'),

                        Forms\Components\Select::make('priority')
                            ->label('Öncelik')
                            ->options(SupportPriority::toSelectArray())
                            ->default(SupportPriority::NORMAL->value)
                            ->required(),

                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->label('Açıklama')
                            ->placeholder('Lütfen sorununuz hakkında detaylı bilgi verin')
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Görüntüle'),
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
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->forUser(auth()->id())
            ->withCount('replies');
    }
}
