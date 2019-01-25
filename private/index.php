<?php
/**
 * Головний файл з обмеженим доступом
 *
 * /?action=upload       $_POST=array('key');  $_FILES['image']
 * /?action=delete       $_POST=array('image', 'key')
 *
 * image = '/1/c/8/1c8dc3d168a18a47e14231db4e861b4a/0320.jpg'
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     MediaCMS\Photo
 * @link        https://медіа.укр
 * @copyright   GNU General Public License v3
 */

use MediaCMS\Photo\Controller;
use MediaCMS\Photo\Response;
use MediaCMS\Photo\Exception;

spl_autoload_register('autoload');

set_error_handler('exceptionErrorHandler');

setlocale(LC_ALL, 'uk_UA.utf8');

mb_internal_encoding('UTF-8');

require_once(PATH_PRIVATE . '/settings.php');

$response = new Response();

try {

    define('DEBUG', $_GET['debug'] ?? 0);

    if (isset($_GET['debug'])) {

        if (!preg_match('/^[012]$/', $_GET['debug']))

            throw new Exception('Unknown debug value', 104);
    }

    if (count($_GET) == 0)

        throw new Exception('Access denied', 100);

    if (!isset($_POST['key']))

        throw new Exception('Missing access key', 101);

    if ($_POST['key'] != KEY)

        throw new Exception('Unknown access key', 102);

    if (!isset($_GET['action']))

        throw new Exception('Missing action', 103);

    $controller = new Controller($response);

    $action = $_GET['action'];

    if (!method_exists($controller, $action)) {

         throw new Exception(sprintf("Unknown action '%s'", $_GET['action']), 111);
    }

    call_user_func(array($controller, $action));

} catch (\Exception $exception) {

    /** Додаємо вілагоджувальну інформацію для виводу */

    $message = $exception->getMessage();

    if (DEBUG)

        $message .= sprintf(' (%s, %s)', $exception->getFile(), $exception->getLine());

    $response->setError($message, $exception->getCode());

    if (DEBUG)

        foreach($exception->getTrace() as $trace) $response->setTrace($trace);

    header('HTTP/1.x 404 Not Found');
}

if (DEBUG) {

    $response->setDebug('time',

        sprintf('%01.3f', (microtime(true) - TIME) * 1000) . ' ms');

    $response->setDebug('memory',

        sprintf('%01.3f', ((memory_get_usage() - MEMORY) / 1024)) . ' kB');

    $response->setDebug('memoryPeak',

        sprintf('%01.3f', (memory_get_peak_usage() / 1024)) . ' kB');
}

if (DEBUG == 2) {

    echo '<pre>' . print_r($response->get(), true) . '</pre>';

} else {

    header('Content-Type: application/json; charset=utf-8');

    print json_encode($response->get());
}



/**
 * Створює автозавантажувач об’єктів
 *
 * @param string $object Назва об’єкту
 */
function autoload($object) {

    $class = str_replace('MediaCMS\\Photo\\', '/', $object);

    require_once(PATH_PRIVATE . "/$class.php");
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
