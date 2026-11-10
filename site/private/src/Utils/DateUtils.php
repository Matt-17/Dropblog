<?php
namespace Dropblog\Utils;

use DateTime;
use Dropblog\Config;
use Dropblog\Utils\Localization;

class DateUtils
{
    public static function getMonthNames(): array
    {
        return [
            1 => Localization::t('months.january'),
            2 => Localization::t('months.february'),
            3 => Localization::t('months.march'),
            4 => Localization::t('months.april'),
            5 => Localization::t('months.may'),
            6 => Localization::t('months.june'),
            7 => Localization::t('months.july'),
            8 => Localization::t('months.august'),
            9 => Localization::t('months.september'),
            10 => Localization::t('months.october'),
            11 => Localization::t('months.november'),
            12 => Localization::t('months.december')
        ];
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
        $formatted = $date->format(Config::dateFormat());
        $month = (int)$date->format('n');
        $monthNames = self::getMonthNames();
        return str_replace($date->format('F'), $monthNames[$month], $formatted);
    }
}
