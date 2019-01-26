<?php
/**
 * Клас роботи з зображеннями
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     MediaCMS\Photo
 * @link        https://медіа.укр
 * @copyright   GNU General Public License v3
 */

namespace MediaCMS\Photo;

use Exception;

class Image {

    /** @var string Шлях до теки з файлами зображень */
    protected $storage = PATH_STORAGE;

    /** @var string Дозволений тип файла зображення */
    protected $type = 'image/jpeg';

    /** @var integer Обмеження розміру файла зображення*/
    protected $size = ['min' => 16384, 'max' => 10485760];

    /** @var string Назва оригінального файла зображення */
    protected $original = 'original.jpg';

    /** @var array Перелік ширин зменшених зображень */
    protected $widths = ['0320', '0480', '0640', '0960', '1280', '1600', '1920', '2560', '3840'];

    /** @var integer Степінь стиснення зменшених зображень */
    protected $quality = 88;

    /** @var integer Помилка файлу */
    protected $error;

    /** @var array Перелік кодів та опису помилок завантаження файлів */
    protected $errors = array(

        UPLOAD_ERR_INI_SIZE

            => 'Розмір зображення більший за допустимий в налаштуваннях сервера',

        UPLOAD_ERR_FORM_SIZE

            => 'Розмір зображення більший за значення MAX_FILE_SIZE, вказаний в HTML-формі',

        UPLOAD_ERR_PARTIAL => 'Зображення завантажено тільки частково',

        UPLOAD_ERR_NO_FILE => 'Зображення не завантажено',

        UPLOAD_ERR_NO_TMP_DIR => 'Відсутня тимчасова тека',

        UPLOAD_ERR_CANT_WRITE => 'Не вдалось записати зображення на диск',

        UPLOAD_ERR_EXTENSION => 'Сервер зупинив завантаження зображення',
    );


    /**
     * Завантажує зображення

     * @param array $image Дані про файл зображення ($_FILES['image'])
     * @return string Відносний шлях до файла зображення
     */
    public function upload(array $image): string {

        $this->validate($image);

        $hash = hash_file('md5', $image['tmp_name']);

        $directory = '';

        for($i = 1; $i <= 3; $i ++) {

            $directory .= '/' . $hash[$i-1];

            $path = $this->storage . $directory;

            if (file_exists($path) && is_dir($path)) continue;

            mkdir($path);

            chmod($path, 0775);
        }

        $directory .= '/' . $hash;

        $path = $this->storage . '/' . $directory;

        if (file_exists($path) && is_dir($path)) {

            $exception = "Файл вже існує ('%s', '%s')";

            throw new Exception(sprintf($exception, $image['name'], $path), 139);
        }

        mkdir($path);

        chmod($path, 0775);

        $uri = $directory . '/' . $this->original;

        $file = $this->storage . $uri;

        if (!move_uploaded_file($image['tmp_name'], $file)) {

            $exception = "Помилка при переміщені файла ('%s', '%s', '%s')";

            $params =  array($image['name'], $uri, $image['tmp_name']);

            throw new Exception(vsprintf($exception, $params), 140);
        }

        return $this->resize($directory);
    }


    /**
     * Перевіряє завантажений файл зображення
     *
     * @param array $image Дані про файл зображення
     */
    protected function validate(array $image) {

        if ($image['error'] !== 0) {

            $error = $image['error'];

            $params = [$image['name'], $this->errors[$error]];

            $exception = vsprintf("Помилка при завантаженні файлу ('%s', '%s')", $params);

            throw new Exception($exception, $image['error']);
        }

        if (!is_uploaded_file($image['tmp_name'])) {

            $exception = sprintf("Файл не був завантажений ('%s')", $image['name']);

            throw new Exception($exception, 132);
        }

        if ($image['type'] != $this->type) {

            $exception = "Не дозволений формат зображення ('%s', '%s')";

            throw new Exception(sprintf($exception, $image['name'], $image['type']), 133);
        }

        if ($image['size'] < $this->size['min']) {

            $exception = "Замалий розмір файлу зображення ('%s', %d B < %d B)";

            $params = [$image['name'], $image['size'], $this->size['min']];

            throw new Exception(vsprintf($exception, $params), 135);
        }

        if ($image['size'] > $this->size['max']) {

            $exception = "Завеликий розмір файлу зображення ('%s', %d B > %d B)";

            $params = [$image['name'], $image['size'], $this->size['max']];

            throw new Exception(vsprintf($exception, $params), 136);
        }
    }


    /**
     * Створює зменшені зображення
     *
     * @param string $directory Новостворена тека для зображення
     * @return string Відносний шлях до файла зображення
     */
    protected function resize(string $directory): string {

        $source['uri'] = $directory . '/' . $this->original;

        $source['file'] = $this->storage . $source['uri'];

        $size = getimagesize($source['file']);

        $width = $size[0];

        $widthMin = (integer) $this->widths[0];

        if ($width < $widthMin) {

            $exception = "Замала ширина зображення (%dpx < %dpx)";

            throw new Exception(sprintf($exception, $width, $widthMin), 140);
        }

        $uri = null;

        foreach($this->widths as $width) {

            if ($width > $size[0]) break;

            $uri = $directory . '/' . $width . '.jpg';

            $destination['width'] = (integer) $width;

            $destination['uri'] = $uri;

            $destination['file'] = $this->storage . $destination['uri'];

            $source['resource'] = imagecreatefromjpeg($source['file']);

            $source['width'] = imagesx($source['resource']);

            $source['height'] = imagesy($source['resource']);

            $destination['height']

                = round($destination['width'] / $source['width'] * $source['height']);

            $destination['resource']

                = imagecreatetruecolor($destination['width'], $destination['height']);

            imagecopyresampled(

                $destination['resource'], $source['resource'], 0, 0, 0, 0,

                $destination['width'], $destination['height'], $source['width'], $source['height']
            );

            if (!imagejpeg($destination['resource'], $destination['file'], $this->quality)) {

                $exception = "Не можу створити зменшене зображення ('%s')";

                throw new Exception(sprintf($exception, $destination['uri']), 141);
            }

            chmod($destination['file'], 0775);
        }

        return $uri;
    }


    /**
     * Видаляє зображення
     *
     * @param string $uri Відносна адреса файлу зображення
     */
    public function delete($uri): void {

        $pattern = "/^(\/[0-9a-f]\/[0-9a-f]\/[0-9a-f]\/[0-9a-f]{32})\/\d{4}\.jpg$/";

        if (!preg_match($pattern, $uri, $matches))

            throw new Exception(

                sprintf("Погана адреса файла зображення '%s'", $uri), 130
            );

        $directory = $this->storage . '/' . $matches[1];

        if (!file_exists($directory) || !is_dir($directory))

            throw new Exception(

                sprintf("Відсутній файл зображення '%s'", $uri), 131
            );

        $images = array_slice(scandir($directory), 2);

        foreach($images as $image) {

            if (!unlink($directory . '/' . $image))

                throw new Exception(

                    sprintf("Помилка при видаленні файла зображення '%s'", $image), 132
                );
        }

        for($i = 1; $i <= 4; $i ++) {

            if (count(scandir($directory)) == 2) rmdir($directory);

            $directory = dirname($directory);
        }
    }
}
