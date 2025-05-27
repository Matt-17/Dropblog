<?php
namespace PainBlog\Utils;

use DateTime;
use PainBlog\Config;

class DateUtils
{
    private static array $monthNames = [
        1 => 'Januar',
        2 => 'Februar',
        3 => 'März',
        4 => 'April',
        5 => 'Mai',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'August',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Dezember'
    ];

    public static function getMonthNames(): array
    {
        return self::$monthNames;
    }

    public static function getPreviousMonth(int $month, int $year): array
    {
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        return ['month' => $prevMonth, 'year' => $prevYear];
    }

    public static function getNextMonth(int $month, int $year): array
    {
        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }
        return ['month' => $nextMonth, 'year' => $nextYear];
    }

    public static function isFutureMonth(int $month, int $year): bool
    {
        $currentYear = date('Y');
        $currentMonth = date('n');
        return $year > $currentYear || ($year == $currentYear && $month > $currentMonth);
    }

    public static function formatDate(DateTime $date): string
    {
        $formatted = $date->format(Config::DATE_FORMAT);
        $month = (int)$date->format('n');
        return str_replace($date->format('F'), self::$monthNames[$month], $formatted);
    }
}
