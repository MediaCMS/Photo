<?php
/**
 * Створення зменшених зображень
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     Varianty\Photo
 * @link        https://варіанти.укр
 * @copyright   Всі права застережено (c) 2018 Варіанти
 */

/** Час початку виконання скриптів */
define('_TIME', microtime(true));

/** Кількість використовуваної пам’яті на початку виконання скриптів */
define('_MEMORY', memory_get_usage());

/** Шлях до файлів зображень */
define('_STORAGE', $_SERVER['DOCUMENT_ROOT'] .'/storage');

/** Ключ дозволу запуску скрипта в режимі відлагодження */
define('_KEY', 'Wg7sNJV44V6zDh5t5zwAMgWqpny78SVE');

/** Ознака виконанння скрипта в режимі відлагодження */
define('_DEBUG', (isset($_GET['debug']) && ($_GET['debug'] == _KEY)) ? true : false);

try {

    $widths = array('0320', '0480', '0640', '0960', '1280', '1600', '1920', '2560', '3840');

    $pattern = '\/[0-9a-f]{2}\/[0-9a-f]{2}\/[0-9a-f]{2}\/[0-9a-f]{32}';

    $pattern = "/^\/storage($pattern)\.(\d{4})\.jpg(\?debug=\w{32})?$/i";

    if (!preg_match($pattern, $_SERVER['REQUEST_URI'], $matches))

        throw new Exception(

            sprintf('Bad request "%s"', $_SERVER['REQUEST_URI'])
        );

    $source['uri'] = $matches[1] . '.original.jpg';

    $source['file'] = _STORAGE . $source['uri'];

    if (!file_exists($source['file']))

        throw new Exception(

            sprintf('Source file not found "%s"', $source['file'])
        );

    $destination['width'] = (integer) $matches[2];

    if (!in_array($destination['width'], $widths))

        throw new Exception(

            sprintf('Not allowed image width "%dpx"', $destination['width'])
        );

    $destination['uri'] = $matches[1] . '.' . $matches[2] . '.jpg';

    $destination['file'] = _STORAGE . $destination['uri'];

    $source['resource'] = imagecreatefromjpeg($source['file']);

    $source['width'] = imagesx($source['resource']);

    $source['height'] = imagesy($source['resource']);
/*
    if ($destination['width'] > $source['width']) {

        $message = 'Source image width too small "%dpx < %dpx"';

        throw new Exception(sprintf($message, $source['width'], $destination['width']));
    }
*/
    $destination['height'] = round($destination['width'] / $source['width'] * $source['height']);

    $destination['resource'] = imagecreatetruecolor($destination['width'], $destination['height']);

    imagecopyresampled($destination['resource'], $source['resource'], 0, 0, 0, 0,

                       $destination['width'], $destination['height'],

                       $source['width'], $source['height']);

    if (!imagejpeg($destination['resource'], $destination['file'], 88))

        throw new Exception(

            sprintf('Error creating small image "%s"', $destination['uri'])
        );

    chmod($destination['file'], 0775);

    if (_DEBUG) {

        debug($source, $destination);

    } else {

        $destination['name'] = basename($destination['file']);

        $destination['size'] = filesize($destination['file']);

        header("Content-Type: image/jpeg");

        header('Expires: 0');

        header('Cache-Control: must-revalidate');

        header('Pragma: public');

        header(sprintf('Content-Length: %s', $destination['size']));

        readfile($destination['file']);
    }

} catch (\Exception $exception) {

    header('HTTP/1.x 404 Not Found');

    if (_DEBUG) echo 'Error: ' . $exception->getMessage();

    exit();
}

imagedestroy($source['resource']);

imagedestroy($destination['resource']);

unset($source, $destination);



function debug($source, $destination) {

    $debug[] = 'Source: ' . print_r($source, true);

    $debug[] = 'Destination: ' . print_r($destination, true);

    $debug[] = sprintf('Time: %01.3f ms', (microtime(true) - _TIME) * 1000);

    $debug[] = sprintf('Memory: %01.3f kB', ((memory_get_usage() - _MEMORY) / 1024));

    $debug[] = sprintf('Memory Peak: %01.3f kB', (memory_get_peak_usage() / 1024));

    $debug = implode("\n", $debug);

    echo "<pre>\n$debug\n</pre>";
}