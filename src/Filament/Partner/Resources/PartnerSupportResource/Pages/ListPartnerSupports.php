<?php

namespace VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource\Pages;

use Filament\Resources\Pages\ListRecords;
use VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource;

class ListPartnerSupports extends ListRecords
{
    protected static string $resource = PartnerSupportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
