<?php

namespace VisioSoft\Support\Filament\Admin\Resources\PartnerSupportResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use VisioSoft\Support\Filament\Admin\Resources\PartnerSupportResource;

class EditPartnerSupport extends EditRecord
{
    protected static string $resource = PartnerSupportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
