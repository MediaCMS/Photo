<?php
/**
 * Клас роботи з зображеннями
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     Varianty\Photo\Image
 * @link        https://варіанти.укр
 * @copyright   Всі права застережено (c) 2018 Варіанти
 */

namespace Varianty\Photo;

use Exception;

class Image {

    /** @var string Директорія файла зображення */
    protected $directory;

    /** @var string Хеш-код файла зображення */
    protected $hash;

    /** @var string Відносний шлях файла зображення */
    protected $uri;

    /** @var string Абсолютний шлях до файлу зображення */
    protected $file;

    /** @var string Назва завантаженого файлу зображення */
    protected $name;

    /** @var string Назва файлу зображення в тимчасовій теці */
    protected $temporary;

    /** @var string Тип файла зображення */
    protected $type;

    /** @var integer Розмір файлу */
    protected $size;

    /** @var integer Мінімально дозволений розмір файлу */
    protected $sizeMinimum = 16384;

    /** @var integer Максимально дозволений розмір файлу */
    protected $sizeMaximum = 10485760;

    /** @var array Перелік ширин зменшених зображень */
    protected $widths = array('0320', '0480', '0640', '0960', '1280', '1600', '1920', '2560', '3840');

    /** @var integer Помилка файлу */
    protected $error;

    /** @var array Перелік кодів та опису помилок завантаження файлів */
    protected $errors = array(

        UPLOAD_ERR_INI_SIZE => 'Розмір зображення більший за допустимий в налаштуваннях сервера',

        UPLOAD_ERR_FORM_SIZE => 'Розмір зображення більший за значення MAX_FILE_SIZE, вказаний в HTML-формі',

        UPLOAD_ERR_PARTIAL => 'Зображення завантажено тільки частково',

        UPLOAD_ERR_NO_FILE => 'Зображення не завантажено',

        UPLOAD_ERR_NO_TMP_DIR => 'Відсутня тимчасова тека',

        UPLOAD_ERR_CANT_WRITE => 'Не вдалось записати зображення на диск',

        UPLOAD_ERR_EXTENSION => 'Сервер зупинив завантаження зображення',
    );


    /**
     * Конструктор
     *
     * @param string|array $image Відносна адреса файлу зображення або
     *                            масив з даними про файл зображення $_FILES['image']

     * @throws Exception Unknown exception
     */
    public function __construct($image) {

        if (is_array($image)) {

            $this->setFile($image);

            $this->upload();

        } else {

            $this->setUri($image);
        }
    }

    /**
     * Встановлює відносний та абсолютний шлях з шляху файла зображення
     *
     * @param string $uri Відносна адреса файлу зображення
     * @throws Exception Bad image path
     */
    protected function setUri($uri) {

        $pattern = '\/[a-f0-9]{2}\/[a-f0-9]{2}\/[a-f0-9]{2}';

        $pattern = "/(($pattern)\/([a-f0-9]{32}))\.\d{4}\.jpg/";

        if (!preg_match($pattern, $uri, $matches)) {

            throw new Exception(sprintf("Bad image path '%s'", $uri), 130);
        }

        $this->hash = $matches[3];

        $this->directory = $matches[2];

        $this->uri = $this->directory . '/' . $this->hash . '.original.jpg';

        $this->file = _PATH_STORAGE . $this->uri;

        if (!file_exists($this->file)) {

            throw new Exception(sprintf("Missing image file '%s'", $this->uri), 131);
        }
    }

    /**
     * Повертає відносний шлях до файла зображення
     *
     * @return string Відносний шлях до файла зображення
     */
    public function getUri() {

        return $this->uri;
    }

    /**
     * Перевіряє та зберігає завантажений файл зображення
     *
     * @param array $image Масив з даними про файл зображення $_FILES['image']
     * @throws Exception Upload error
     * @throws Exception File not uploaded
     * @throws Exception Not allowed image type
     * @throws Exception Image size is too small
     * @throws Exception Image size is too big
     * @throws Exception Missing temporary name
     * @throws Exception Missing temporary file
     */
    protected function setFile($image) {

        if (isset($image['name'])) $this->name = $image['name'];

        if (isset($image['tmp_name'])) $this->temporary = $image['tmp_name'];

        if (isset($image['type'])) $this->type = $image['type'];

        if (isset($image['error'])) $this->error = $image['error'];

        if (isset($image['size'])) $this->size = $image['size'];

        if ($this->error !== 0) {

            $error = $this->error;

            $params =  array($this->errors[$error], $this->name);

            throw new Exception(vsprintf("Error '%s' ('%s')", $params), $this->error);
        }

        if (!is_uploaded_file($this->temporary)) {

            throw new Exception(sprintf("File '%s' not uploaded", $this->name), 132);
        }

        if ($this->type != 'image/jpeg') {

            $exception = "Not allowed image type '%s' ('%s')";

            $params =  array($this->type, $this->name);

            throw new Exception(vsprintf($exception, $params), 133);
        }

        if ($this->size < $this->sizeMinimum) {

            $exception = "Image size is too small (%d B < %d B, '%s')";

            $params =  array($this->size, $this->sizeMinimum, $this->name);

            throw new Exception(vsprintf($exception, $params), 135);
        }

        if ($this->size > $this->sizeMaximum) {

            $exception = "Image size is too big (%d B > %d B, '%s')";

            $params =  array($this->size, $this->sizeMaximum, $this->name);

            throw new Exception(vsprintf($exception, $params), 136);
        }

        if (!isset($this->temporary)) {

            throw new Exception(sprintf("Missing temporary name '%s'", $this->name), 137);
        }

        if (!file_exists($this->temporary)) {

            throw new Exception(sprintf("Missing temporary file '%s'", $this->name), 138);
        }

        $this->hash = hash_file('md5', $this->temporary);
    }

    /**
     * Завантажує зображення

     * @throws Exception Uploaded image is present
     * @throws Exception Move file error
     */
    protected function upload() {

        $file = $this->hash . '.original.jpg';

        $directory = '';

        for($i = 1; $i <= 3; $i ++) {

            $start = ($i - 1) * 2;

            $directory .= '/' . substr($file, $start, 2);

            $path = _PATH_STORAGE . $directory;

            if (!file_exists($path) || !is_dir($path)) {

                mkdir($path);

                chmod($path, 0775);
            }
        }

        $this->uri = $directory . '/' . $file;

        $this->file = _PATH_STORAGE . $this->uri;

        $this->directory = $directory;

        if (file_exists($this->file)) {

            $exception = "Uploaded image is present '%s' ('%s')";

            $params =  array($this->name, $this->uri);

            throw new Exception(vsprintf($exception, $params), 139);
        }

        if (!move_uploaded_file($this->temporary, $this->file)) {

            $exception = "Move file error ('%s', '%s', '%s')";

            $params =  array($this->name, $this->uri, $this->temporary);

            throw new Exception(vsprintf($exception, $params), 140);
        }

        $size = getimagesize($this->file);

        foreach($this->widths as $width) {

            if($width > $size[0]) break;

            $this->uri = $directory . '/' . $this->hash . '.' . $width . '.jpg';;
        }
    }

    /**
     * Видаляє зображення
     *
     * @throws Exception Delete image error
     */
    public function delete() {

        $directory = _PATH_STORAGE . $this->directory;

        $images = array_slice(scandir($directory), 2);

        foreach($images as $image) {

            if (substr($image, 0, 32) != $this->hash) continue;

            if (!unlink($directory . '/' . $image)) {

                throw new Exception(sprintf("Delete image error '%s'", $image), 140);
            }
        }

        for($i = 1; $i <= 3; $i ++) {

            if (count(scandir($directory)) == 2) rmdir($directory);

            $directory = dirname($directory);
        }
    }
}
