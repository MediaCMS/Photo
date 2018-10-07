<?php
/**
 * Класс контролера
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     Varianty\Photo\Controller
 * @link        https://варіанти.укр
 * @copyright   Всі права застережено (c) 2018 Варіанти
 */

namespace Varianty\Photo;

class Controller {

    /** array Об'єкт роботи з зображенням Image */
    protected $image;

    /** array Об'єкт роботи з виводом Response */
    protected $response;


    /**
     * Конструктор
     *
     * @param Response $response Об'єкт відповіді сервера зображень
     */
    public function __construct(Response $response) {

        $this->response = $response;
    }

    /**
     * Створює зменшене зображення
     *
     * @throws Exception Missing source image
     * @throws Exception Missing title
     * @throws Exception Missing subtitle
     */
    public function create() {

        if (!isset($_POST['image']))

            throw new Exception('Missing source image', 121);

        if (!isset($_POST['title']))

            throw new Exception('Missing title', 122);

        if (!isset($_POST['subtitle']))

            throw new Exception('Missing subtitle', 124);

        $thumbnail = new Thumbnail($_POST['image'], $_POST['title'], $_POST['subtitle']);

        $thumbnail->create();
    }

    /**
     * Завантажує файл зображення на сервер
     *
     * @throws Exception Missing upload image
     * @throws \Exception Execute parent exception
     */
    public function upload() {

        if (!isset($_FILES['images']))

            throw new Exception('Missing upload image', 120);

        $imagesFiles = array();

        foreach($_FILES['images'] as $key1 => $value1) {

            foreach($value1 as $key2 => $value2)

                $imagesFiles[$key2][$key1] = $value2;
        }

        try {

            foreach($imagesFiles as $imageFile) {

                $image = new Image($imageFile);

                $this->response->setImage($image->getUri());
            }

        } catch (\Exception $exception) {

            $images = $this->response->getImages();

            if (isset($images)) {
                
                $this->delete($images);
            }
            
            $this->response->unsetImages();

            throw $exception;
        }
    }

    /**
     * Видаляє файли зображення з сервера (зі зменшеними зображеннями)
     *
     * @param array $imagesUri Відносні адреси зображень
     * @throws Exception Missing images
     * @throws \Exception Unknown exception
     */
    public function delete($imagesUri = null) {

        if (isset($_POST['images'])) {
            
            $imagesUri = $_POST['images'];
        }
        
        if (!isset($imagesUri)) {
            
            throw new Exception('Missing images', 121);
        }
        
        foreach($imagesUri as $imageUri) {

            $image = new Image($imageUri);

            $image->delete();
        }
    }

    /**
     * Конвертує зображення зі старого сайту
     */
    public function import() {
/*
        $import = new Import();

        $import->run();

        echo 'images: ' . $import->getImages() . "\n";
*/
    }
}
