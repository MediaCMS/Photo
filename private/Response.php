<?php
/**
 * Вивід
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     MediaCMS\Photo
 * @link        https://медіа.укр
 * @copyright   GNU General Public License v3
 */

namespace MediaCMS\Photo;

class Response {

    /** array Масив для відповіді */
    protected $data = array('status' => 1);


    /**
     * Додає адресу зображення
     *
     * @param string $image Назва файлу
     */
    public function setImage($image) {

        $this->data['image'] = $image;
    }

    /**
     * Повертає відносну адресу завантаженого файла зображення
     *
     * @return string Адреса зображення
     */
    public function getImage() {

        return (isset($this->data['image'])) ? $this->data['image'] : null;
    }

    /**
     * Видаляє адресу завантаженого зображення
     */
    public function unsetImage() {

        unset($this->data['image']);
    }

    /**
     * Додає помилку
     *
     * @param string $error Текст помилки
     * @param integer $code Код помилки
     */
    public function setError($error, $code = null) {

        $this->data['error']['message'] = $error;

        if (isset($code)) $this->data['error']['code'] = $code;

        $this->data['status'] = 0;
    }

    /**
     * Додає трейс
     *
     * @param array $trace Текст трейс
     */
    public function setTrace($trace) {

        $this->data['trace'][] = $trace;
    }

    /**
     * Додає інформацію відлагодження
     *
     * @param string $name Назва зиінної
     * @param string $value Значенн змінної
     */
    public function setDebug($name, $value) {

        $this->data['debug'][$name] = $value;
    }

    /**
     * Повертає json
     */
    public function get() {

        return $this->data;
    }
}