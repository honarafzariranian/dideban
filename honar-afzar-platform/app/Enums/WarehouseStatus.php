<?php

namespace App\Enums;

enum WarehouseStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match($this) {
            self::Active => 'فعال',
            self::Inactive => 'غیرفعال',
            self::Maintenance => 'در تعمیر',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'green',
            self::Inactive => 'gray',
            self::Maintenance => 'yellow',
        };
    }
}
