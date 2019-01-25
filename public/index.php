<?php
/**
 * Головний файл в загальному доступі
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     MediaCMS\Photo
 * @link        https://медіа.укр
 * @copyright   GNU General Public License v3
 */

/** Час початку виконання скриптів */
define('TIME', microtime(true));

/** Кількість використовуваної пам’яті */
define('MEMORY', memory_get_usage());

/** Шлях до головної теки сайту */
define('PATH_ROOT', dirname(__DIR__));

/** Шлях до файлів сайту з загальним доступом */
define('PATH_PUBLIC', PATH_ROOT .'/public');

/** Шлях до файлів сайту з обмеженим доступом */
define('PATH_PRIVATE', PATH_ROOT .'/private');

require_once(PATH_PRIVATE . '/index.php');
