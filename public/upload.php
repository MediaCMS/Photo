<?php

/** Шлях до головної теки сайту */
define('PATH_ROOT', dirname(__DIR__));

/** Шлях до файлів сайту з загальним доступом */
define('PATH_PUBLIC', PATH_ROOT .'/public');

/** Шлях до файлів сайту з обмеженим доступом */
define('PATH_PRIVATE', PATH_ROOT .'/private');

require_once(PATH_PRIVATE . '/settings.php');

header('Content-Type: text/plain;charset=UTF-8');
header('Access-Control-Allow-Origin: ' . ORIGIN);
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
//header('Access-Control-Allow-Credentials: true');

if (isset($_SERVER['REQUEST_METHOD'])

    && ($_SERVER['REQUEST_METHOD'] == 'OPTIONS')) exit('{}');

try {

    reset ($_FILES);
    $temp = current($_FILES);
    // Notify editor that the upload failed
    if (!is_uploaded_file($temp['tmp_name']))

        throw new Exception('Файл не завантажено');

    if (isset($_SERVER['HTTP_ORIGIN'])) {

        // same-origin requests won't set an origin. If the origin is set, it must be valid.
        if ($_SERVER['HTTP_ORIGIN'] == ORIGIN) {

            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);

        } else {

            header("HTTP/1.1 403 Origin Denied");

            return;
        }
    }

/*
    If your script needs to receive cookies, set images_upload_credentials : true in
    the configuration and enable the following two headers.
*/
//    header('Access-Control-Allow-Credentials: true');
//    header('P3P: CP="There is no P3P policy."');

    require_once(PATH_PRIVATE . '/Image.php');

    $image = new MediaCMS\Photo\Image();

    $uri = $image->upload($temp);

    echo json_encode(array('location' => $uri));

} catch (Exception $exception) {

    header('HTTP/1.x 404 Not Found');

    exit($exception->getMessage());
}