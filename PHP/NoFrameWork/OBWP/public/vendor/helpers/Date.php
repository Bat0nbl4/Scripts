<?php

namespace vendor\helpers;

abstract class Date
{
    private static function update_date(string $dateString) : ?\DateTime {
        try {
            $date = new \DateTime($dateString);
        } catch (\Exception $e) {
            return null; // Возвращаем null в случае ошибки парсинга даты
        }
        return $date;
    }

    public static function day_name(string $dateString) : ?string {
        $dayNumber = self::update_date($dateString)->format('N'); // Получаем номер дня недели (1-7)

        $days = [
            1 => 'Понедельник',
            2 => 'Вторник',
            3 => 'Среда',
            4 => 'Четверг',
            5 => 'Пятница',
            6 => 'Суббота',
            7 => 'Воскресенье'
        ];

        return $days[$dayNumber];
    }

    public static function normal_time(string $dateString) : ?string {
        return self::update_date($dateString)->format("H:i");
    }

    public static function noraml_date(string $dateString) : ?string {
        return self::update_date($dateString)->format("d.m.Y");
    }
}