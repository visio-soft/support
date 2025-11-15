<?php

namespace VisioSoft\Support\Filament\Admin\Resources\PartnerSupportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use VisioSoft\Support\Enums\SupportStatus;
use VisioSoft\Support\Filament\Admin\Resources\PartnerSupportResource;

class ListPartnerSupports extends ListRecords
{
    protected static string $resource = PartnerSupportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Tickets'),

            'open' => Tab::make('Open')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SupportStatus::OPEN->value))
                ->badge(fn () => static::getResource()::getModel()::where('status', SupportStatus::OPEN->value)->count()),

            'in_progress' => Tab::make('In Progress')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SupportStatus::IN_PROGRESS->value))
                ->badge(fn () => static::getResource()::getModel()::where('status', SupportStatus::IN_PROGRESS->value)->count()),

            'waiting_admin' => Tab::make('Waiting for Admin')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', SupportStatus::WAITING_ADMIN->value))
                ->badge(fn () => static::getResource()::getModel()::where('status', SupportStatus::WAITING_ADMIN->value)->count())
                ->badgeColor('danger'),

            'my_tickets' => Tab::make('My Tickets')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('assigned_to', auth()->id()))
                ->badge(fn () => static::getResource()::getModel()::where('assigned_to', auth()->id())->count()),

            'unassigned' => Tab::make('Unassigned')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('assigned_to'))
                ->badge(fn () => static::getResource()::getModel()::whereNull('assigned_to')->count())
                ->badgeColor('warning'),

            'closed' => Tab::make('Closed')
                ->modifyQueryUsing(fn (Builder $query) => $query->closed()),
        ];
    }
}
