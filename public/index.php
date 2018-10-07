<?php
/**
 * Головний файл в загальному доступі
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     Varianty\Photo
 * @link        https://варіанти.укр
 * @copyright   Всі права застережено (c) 2018 Варіанти
 */

/** Час початку виконання скриптів */
define('_TIME', microtime(true));

/** Кількість використовуваної пам’яті */
define('_MEMORY', memory_get_usage());

/** Ознака режиму відлагодження та його код */
define('_DEBUG', (isset($_GET['debug'])) ? $_GET['debug'] : 0);

/** Шлях до головної теки сайту */
define('_PATH_ROOT', dirname(__DIR__));

/** Шлях до файлів сайту з загальним доступом */
define('_PATH_PUBLIC', _PATH_ROOT .'/public');

/** Шлях до файлів зображень */
define('_PATH_STORAGE', _PATH_PUBLIC .'/storage');

/** Шлях до файлів сайту з обмеженим доступом */
define('_PATH_PRIVATE', _PATH_ROOT .'/private');

spl_autoload_register('autoload');

set_error_handler('exceptionErrorHandler');

setlocale(LC_ALL, 'uk_UA.utf8');

mb_internal_encoding('UTF-8');

require_once(_PATH_PRIVATE . '/index.php');


/**
 * Створює автозавантажувач об’єктів
 *
 * @param string $object Назва об’єкту
 */
function autoload($object) {

    $class = str_replace('Varianty\\Photo\\', '/', $object);

    require_once(_PATH_PRIVATE . "/$class.php");
}

/**
 * Перетворює помилки у винятки
 *
 * @param string $number Номер помилки
 * @param string $string Опис помилки
 * @param string $file Файл, в якому виникла помилка
 * @param string $line Рядок файлу, в якому виникла помилка
 * @throws \ErrorException
 */
function exceptionErrorHandler($number, $string, $file, $line) {

    throw new \ErrorException($string, 0, $number, $file, $line);
}

/**
 * Викликає виняток у випадку фатальної помилки
 *
 * @throws \ErrorException Any error
 */
function fatalErrorShutdownHandler() {

    $error = error_get_last();

    if ($error['type'] === E_ERROR) {

        exceptionErrorHandler(E_ERROR, $error['message'], $error['file'], $error['line']);
    }
}
