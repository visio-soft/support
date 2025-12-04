<?php

namespace VisioSoft\Support\Enums;

enum SupportPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function getLabel(): string
    {
        return match ($this) {
            self::LOW => 'Düşük',
            self::NORMAL => 'Normal',
            self::HIGH => 'Yüksek',
            self::URGENT => 'Acil',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::LOW => 'gray',
            self::NORMAL => 'info',
            self::HIGH => 'warning',
            self::URGENT => 'danger',
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
