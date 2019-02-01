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
     * Додає адресу файла зображення
     *
     * @param string $uri Адреса файла
     */
    public function setURI($uri) {

        $this->data['uri'] = $uri;
    }

    /**
     * Повертає відносну адресу завантаженого файла зображення
     *
     * @return string|null Адреса зображення
     */
    public function getURI() {

        return (isset($this->data['uri'])) ? $this->data['uri'] : null;
    }

    /**
     * Видаляє адресу завантаженого зображення
     */
    public function unsetURI() {

        unset($this->data['uri']);
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
