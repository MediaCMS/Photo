<?php
/**
 * Клас для роботи з логом
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     Varianty\Photo\Exception
 * @link        https://варіанти.укр
 * @copyright   Всі права застережено (c) 2018 Варіанти
 */

namespace Varianty\Photo;

use \Varianty\Photo\Log;

class Exception extends \Exception {

    /**
     * Конструктор класу
     *
     * @param string $message Опис винятка
     * @param string|array $params Додаткові параметри опису
     */
    public function __construct($message, $code = null, $preview = null) {

        Log::append($message);

        parent::__construct($message, $code, $preview);
    }
}