<?php

namespace VisioSoft\Support\Filament\Admin\Resources\PartnerSupportResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use VisioSoft\Support\Enums\SupportStatus;
use VisioSoft\Support\Filament\Admin\Resources\PartnerSupportResource;

class CreatePartnerSupport extends CreateRecord
{
    protected static string $resource = PartnerSupportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!isset($data['status'])) {
            $data['status'] = SupportStatus::OPEN->value;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
