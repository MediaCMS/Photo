<?php
/**
 * Клас для роботи з логом
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     Varianty\Photo\Log
 * @link        https://варіанти.укр
 * @copyright   Всі права застережено (c) 2018 Варіанти
 */

namespace Varianty\Photo;

class Log {

    /**
     * Додає запис в файл лога
     *
     * @param   string $message Повідомлення, що повинно записатись в лог-файл
     * @return  boolean Результат виконання операції
     */
    public static function append($message) {

        $file = _PATH_PRIVATE . '/exceptions.log';

        $string['time'] = date('Y-m-d H:i:s');

        $string['ip'] = sprintf("[%21s]", self::getIP() . ':' . $_SERVER['REMOTE_PORT']);

        $string['description'] = '"' . $message . '"';

        if (isset($_SERVER['HTTP_USER_AGENT']))

            $string['agent'] = '"' . $_SERVER['HTTP_USER_AGENT'] . '"';

        $string = implode('  ', $string) . "\n";

        $result = file_put_contents($file, $string, FILE_APPEND | LOCK_EX);

        return ($result !== false) ? true : false;
    }

    /**
     * Повертає ip-адресу користувача
     *
     * @return string IP-адреса
     */
    protected static function getIP() {

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {

            $ip = $_SERVER['HTTP_CLIENT_IP'];

        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

        } else {

            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}