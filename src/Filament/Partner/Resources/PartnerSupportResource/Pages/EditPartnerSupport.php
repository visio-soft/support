<?php

namespace VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource\Pages;

use Filament\Resources\Pages\EditRecord;
use VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource;

class EditPartnerSupport extends EditRecord
{
    protected static string $resource = PartnerSupportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ViewAction::make(),
            \Filament\Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
