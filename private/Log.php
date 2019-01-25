<?php
/**
 * Клас для роботи з логом
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     MediaCMS\Photo
 * @link        https://медіа.укр
 * @copyright   GNU General Public License v3
 */

namespace MediaCMS\Photo;

class Log {

    /**
     * Додає запис в файл лога
     *
     * @param   string $message Повідомлення, що повинно записатись в лог-файл
     * @param   integer|null $code Код винятка
     * @return  boolean Результат виконання операції
     */
    public static function append($message, $code = null): bool {

        if (isset($code)) $message .= ' (' . $code . ')';

        $file = PATH_PRIVATE . '/exceptions.log';

        $string['time'] = date('Y-m-d H:i:s');

        $string['ip'] = sprintf("[%21s]", self::getIP() . ':' . $_SERVER['REMOTE_PORT']);

        $string['description'] = '"' . $message . '"';

        if (isset($_SERVER['HTTP_USER_AGENT']))

            $string['agent'] = '"' . $_SERVER['HTTP_USER_AGENT'] . '"';

        $string = implode('  ', $string) . "\r\n";

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