<?php

namespace App\Interfaces\Services;

use Carbon\Carbon;

interface SessionsRangeInterface
{
    /**
     * @return Carbon Дата и время начала диапазона
     */
    public function getFrom(): Carbon;
    /**
     * @return Carbon Дата и время окончания диапазона
     */
    public function getTo(): Carbon;

    /**
     * @return int Количество сессий
     */
    public function getTotal(): int;
}
