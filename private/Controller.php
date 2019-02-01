<?php
/**
 * Класс контролера
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     MediaCMS\Photo
 * @link        https://медіа.укр
 * @copyright   GNU General Public License v3
 */

namespace MediaCMS\Photo;

class Controller {

    /** @var Image Об'єкт для роботи з зображенням */
    protected $image;

    /** @var Response Об'єкт для роботи з виводом */
    protected $response;


    /**
     * Конструктор
     *
     * @param Response $response Об'єкт відповіді сервера зображень
     */
    public function __construct(Response $response) {

        $this->response = $response;

        $this->image = new Image();
    }

    /**
     * Завантажує файл зображення на сервер
     *
     * @param array $files Дані завантаженого файлу ($_FILES)
     */
    public function upload(): void {

        if (!isset($_FILES['image']))

            throw new Exception('Відсутній файл зображення', 120);

        try {

            $uri = $this->image->upload($_FILES['image']);

            $this->response->setURI($uri);

        } catch (\Exception $exception) {

            $uri = $this->response->getURI();

            if (isset($uri)) $this->remove($uri);

            $this->response->unsetURI();

            throw $exception;
        }
    }

    /**
     * Видаляє файл зображення з сервера
     *
     * @param string $uri Відносна адреса зображення
     */
    public function remove($uri = null) {

        if (!isset($uri) && isset($_POST['uri'])) $uri = $_POST['uri'];

        if (!isset($uri))
            
            throw new Exception('Відсутня адреса файлу зображення', 121);

        $this->image->remove($uri);
    }
}
