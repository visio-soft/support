<?php

namespace VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use VisioSoft\Support\Enums\SupportStatus;
use VisioSoft\Support\Filament\Partner\Resources\PartnerSupportResource;

class CreatePartnerSupport extends CreateRecord
{
    protected static string $resource = PartnerSupportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['status'] = SupportStatus::OPEN->value;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
