<?php

namespace App\Enums;

enum MovementType: string
{
    case Receipt = 'receipt';
    case Issue = 'issue';
    case Transfer = 'transfer';
    case Adjustment = 'adjustment';
    case Return = 'return';

    public function label(): string
    {
        return match($this) {
            self::Receipt => 'رسید',
            self::Issue => 'حواله',
            self::Transfer => 'انتقال',
            self::Adjustment => 'تعدیل',
            self::Return => 'مرجوعی',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Receipt => 'green',
            self::Issue => 'red',
            self::Transfer => 'blue',
            self::Adjustment => 'yellow',
            self::Return => 'purple',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::Receipt => 'arrow-down',
            self::Issue => 'arrow-up',
            self::Transfer => 'arrows-exchange',
            self::Adjustment => 'pencil',
            self::Return => 'arrow-undo',
        };
    }
}
