<?php

namespace App\Services;

use App\Interfaces\Services\SessionsRangeInterface;
use Carbon\Carbon;

class SessionsRange implements SessionsRangeInterface
{
    protected Carbon $from;
    protected Carbon $to;
    protected int $total = 0;

    /**
     * @inheritDoc
     */
    public function getFrom(): Carbon
    {
        return $this->from;
    }

    /**
     * @inheritDoc
     */
    public function getTo(): Carbon
    {
        return $this->to;
    }

    /**
     * @inheritDoc
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param Carbon $from
     */
    public function setFrom(Carbon $from): void
    {
        $this->from = $from;
    }

    /**
     * @param Carbon $to
     */
    public function setTo(Carbon $to): void
    {
        $this->to = $to;
    }

    /**
     * @param int $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }
}
