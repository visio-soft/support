<?php

namespace VisioSoft\Support\Enums;

enum SupportStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case WAITING_CUSTOMER = 'waiting_customer';
    case WAITING_ADMIN = 'waiting_admin';
    case RESOLVED = 'resolved';
    case CLOSED = 'closed';

    public function getLabel(): string
    {
        return match ($this) {
            self::OPEN => 'Açık',
            self::IN_PROGRESS => 'İşlemde',
            self::WAITING_CUSTOMER => 'Müşteri Yanıtı Bekleniyor',
            self::WAITING_ADMIN => 'Yönetici Yanıtı Bekleniyor',
            self::RESOLVED => 'Çözüldü',
            self::CLOSED => 'Kapalı',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::OPEN => 'info',
            self::IN_PROGRESS => 'warning',
            self::WAITING_CUSTOMER => 'warning',
            self::WAITING_ADMIN => 'danger',
            self::RESOLVED => 'success',
            self::CLOSED => 'gray',
        };
    }

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function toSelectArray(): array
    {
        $array = [];
        foreach (self::cases() as $case) {
            $array[$case->value] = $case->getLabel();
        }
        return $array;
    }
}
