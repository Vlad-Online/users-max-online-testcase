<?php

namespace App\Interfaces\Services;

use Carbon\Carbon;

interface SessionsInterface
{
    /**
     * @return SessionsRangeInterface[] В какое время за отдельно взятые сутки в системе находилось одновременно
     * максимальное число пользователей
     */
    public function getMaxOnline(Carbon $date): array;
}
