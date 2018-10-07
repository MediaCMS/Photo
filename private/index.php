<?php
/**
 * Головний файл з обмеженим доступом
 *
 * /?action=create       $_POST=array('image', 'title', 'subtitle', 'key')
 * /?action=upload       $_POST=array('title', 'subtitle', 'key');  $_FILES['image']
 * /?action=delete       $_POST=array('image', 'key')
 * /?action=import       $_POST=array('key')
 *
 * image = '/1c/8d/c3/1c8dc3d168a18a47e14231db4e861b4a.0320.jpg'
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     Varianty\Photo
 * @link        https://варіанти.укр
 * @copyright   Всі права застережено (c) 2018 Варіанти
 */

use \Varianty\Photo\Controller;
use \Varianty\Photo\Response;
use \Varianty\Photo\Exception;

/** Ключ доступу */
define('_KEY', '1c8dc3d168a18a47e14231db4e861b4a');

$response = new Response();

try {

    if (count($_GET) == 0) throw new Exception('Access denied', 100);

    if (!isset($_POST['key'])) throw new Exception('Missing access key', 101);

    if ($_POST['key'] != _KEY) throw new Exception('Unknown access key', 102);

    if (!isset($_GET['action'])) throw new Exception('Missing action', 110);

    $controller = new Controller($response);

    $action = $_GET['action'];

    if (!method_exists($controller, $action)) {

         throw new Exception(sprintf("Unknown action '%s'", $_GET['action']), 111);
    }

    call_user_func(array($controller, $action));

} catch (\Exception $exception) {

    /** Додаємо вілагоджувальну інформацію для виводу */

    $message = $exception->getMessage();

    if (_DEBUG)

        $message .= sprintf(' (%s, %s)', $exception->getFile(), $exception->getLine());

    $response->setError($message, $exception->getCode());

    if (_DEBUG)

        foreach($exception->getTrace() as $trace) $response->setTrace($trace);

    header('HTTP/1.x 404 Not Found');
}

if (_DEBUG) {

    $response->setDebug('time',

        sprintf('%01.3f', (microtime(true) - _TIME) * 1000) . ' ms');

    $response->setDebug('memory',

        sprintf('%01.3f', ((memory_get_usage() - _MEMORY) / 1024)) . ' kB');

    $response->setDebug('memoryPeak',

        sprintf('%01.3f', (memory_get_peak_usage() / 1024)) . ' kB');
}

if (_DEBUG == 2) {

    echo '<pre>' . print_r($response->get(), true) . '</pre>';

} else {

    header('Content-Type: application/json; charset=utf-8');

    print json_encode($response->get());
}


