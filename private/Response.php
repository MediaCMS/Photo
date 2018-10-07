<?php
/**
 * Вивід
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     Varianty\Photo\Response
 * @link        https://варіанти.укр
 * @copyright   Всі права застережено (c) 2018 Варіанти
 */

namespace Varianty\Photo;

class Response {

    /** array Масив для відповіді */
    protected $data = array('status' => 1);


    /**
     * Додає адресу зображення
     *
     * @param string $image Назва файлу
     */
    public function setImage($image) {

        $this->data['images'][] = $image;
    }

    /**
     * Повертає відносні адреси завантажених файлів зображень
     *
     * @return string Відносний адреси завантажених файлів зображень
     */
    public function getImages() {

        return (isset($this->data['images'])) ? $this->data['images'] : null;
    }

    /**
     * Видаляє адреси завантажених зображень
     */
    public function unsetImages() {

        unset($this->data['images']);
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