<?php

namespace SES\CalendarManager;

class Logger
{
    private static $logPath = __DIR__ . "/../logs/";

    public static function log($prefix, $logType, $message, $status = 'error')
    {
        if (!file_exists(self::$logPath)) {
            if (!mkdir(self::$logPath, 0777, true)) {
                return;
            }
        }

        $filename = self::$logPath . $prefix . '_' . $logType . '_' . $status . '.log';

        $date = new \DateTime();
        $timestamp = $date->format('Y-m-d H:i:s');

        // Проверка, является ли $message массивом
        if (is_array($message)) {
            $message = print_r($message, true); // Преобразование массива в строку
        }

        $logEntry = $timestamp . ' - ' . $message . PHP_EOL;

        file_put_contents($filename, $logEntry, FILE_APPEND);
    }

    public static function setLogPath($path) {
        self::$logPath = $path;
    }
}
