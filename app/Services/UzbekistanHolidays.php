<?php

namespace App\Services;

use Carbon\Carbon;

class UzbekistanHolidays
{
    public static function getHolidays(int $year): array
    {
        return [
            "{$year}-01-01" => "Yangi Yil",
            "{$year}-01-14" => "Vatan Himoyachilari Kuni",
            "{$year}-03-08" => "Xalqaro Xotin-qizlar Kuni",
            "{$year}-03-21" => "Navro'z Bayrami",
            "{$year}-05-09" => "Xotira va Qadrlash Kuni",
            "{$year}-09-01" => "Mustaqillik Kuni",
            "{$year}-10-01" => "O'qituvchilar va Murabbiylar Kuni",
            "{$year}-12-08" => "Konstitutsiya Kuni",
            // Note: Hayit dates change every year based on the moon.
            // Ideally, fetch these from an API or admin settings.
            // These are estimated for 2025:
            "2025-03-31" => "Ramazon Hayiti (Estimated)",
            "2025-06-07" => "Qurbon Hayiti (Estimated)",
        ];
    }

    public static function isHoliday(Carbon $date): bool
    {
        $holidays = self::getHolidays($date->year);
        return array_key_exists($date->format('Y-m-d'), $holidays);
    }
}
