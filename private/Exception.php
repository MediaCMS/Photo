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

class Exception extends \Exception {

    /**
     * Конструктор класу
     *
     * @param string $message Опис винятка
     * @param integer|null $code Код винятка
     * @param Exception|null $preview Попередній виняток
     */
    public function __construct($message, $code = null, $preview = null) {

        Log::append($message, $code);

        parent::__construct($message, $code, $preview);
    }
}