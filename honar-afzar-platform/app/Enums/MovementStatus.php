<?php

namespace App\Enums;

enum MovementStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'در انتظار',
            self::Approved => 'تأیید شده',
            self::Cancelled => 'لغو شده',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending => 'yellow',
            self::Approved => 'green',
            self::Cancelled => 'red',
        };
    }
}
