<?php

declare(strict_types=1);

namespace App\Enum;

enum BookingStep: string
{
    case None = 'none';
    case ChooseStartDate = 'choose_start_date';
    case ChooseDaysAmount = 'choose_days_amount';
    case SetComment = 'set_comment';
    case Confirm = 'confirm';
    case Done = 'done';
}
