<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Partial = 'partial';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'پیش‌نویس',
            self::Pending => 'در انتظار تأیید',
            self::Approved => 'تأیید شده',
            self::Partial => 'دریافت جزئی',
            self::Received => 'دریافت شده',
            self::Cancelled => 'لغو شده',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft => 'gray',
            self::Pending => 'yellow',
            self::Approved => 'blue',
            self::Partial => 'orange',
            self::Received => 'green',
            self::Cancelled => 'red',
        };
    }
}
