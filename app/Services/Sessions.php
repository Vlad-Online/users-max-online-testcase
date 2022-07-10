<?php

namespace App\Services;

use App\Interfaces\Services\SessionsInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class Sessions implements SessionsInterface
{

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function getMaxOnline(Carbon $date): array
    {
        $startOfDay = $date->startOfDay()->toDateTimeString();
        $endOfDay = $date->endOfDay()->toDateTimeString();
        $result = DB::select(DB::raw("SELECT count(s.user_id) as total_users, points.time_point
FROM sessions s
         LEFT JOIN (SELECT s.login_time as time_point
                    FROM sessions s
                    WHERE s.login_time <@ '[$startOfDay, $endOfDay]'::tsrange
                    UNION
                    SELECT s.logout_time as time_point
                    FROM sessions s
                    WHERE s.logout_time <@ '[$startOfDay, $endOfDay]'::tsrange
                    UNION
                    SELECT '$startOfDay' as time_point
                    UNION
                    SELECT '$endOfDay' as time_point
                    ) points ON s.login_range @> points.time_point
GROUP BY time_point
HAVING count(s.user_id) > 0
ORDER BY total_users desc , time_point;"));
        $sessionRanges = [];
        if (!empty($result)) {
            $totalResults = count($result);
            if (count($result) % 2 != 0) {
                throw new Exception("Query error");
            }

            for ($i = 0; $i < $totalResults; $i += 2) {
                $sessionRange = new SessionsRange();
                $sessionRange->setFrom(Carbon::parse($result[$i]->time_point));
                $sessionRange->setTo(Carbon::parse($result[$i + 1]->time_point));
                $sessionRange->setTotal($result[$i]->total_users);
                $sessionRanges[] = $sessionRange;

                if ( !isset($result[$i+2]) || $result[$i+2]->total_users < $result[$i]->total_users) {
                    break;
                }
            }
        }
        return $sessionRanges;
    }
}
