<?php
/**
 * Конвертування зображень зі старого сайту
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     Varianty\Photo\Import
 * @link        https://варіанти.укр
 * @copyright   Всі права застережено (c) 2018 Варіанти
 */

namespace Varianty\Photo;

class Import {

    /** @var array Параметри доступу до БД */
    protected $db = array(

        'host' => 'localhost',

        'name' => 'mandry',

        'user' => 'mandry',

        'password' => 'tvDjUTXDGnyzKKdW',

        'connection' => null
    );

    /** @var array Дозволені ширини зображень */
    protected $widths = array('0320', '0480', '0640', '0960', '1280', '1600', '1920', '2560', '3840');

    /** @var string Адреса сервера зображень */
    protected $host = 'https://фото.мандри.укр';

    /** @var string Підзаголовок статті */
    protected $subtitle = 'Львівська область';

    /** @var integer Кількість імпортованих зображень */
    protected $images = 0;

    /** @var array Статті з зображеннями */
    protected $articles = array();

    /**
     * Імпортує файли зображень та створює файл змін для БД
      */
    public function run() {

        $this->setArticles();

        $sql = fopen(_PATH_PUBLIC . '/convert.sql', 'w+');

        foreach ($this->articles as $key => &$article) {

            $source = '/images/' . $article['id'] . '/' . $article['image'] . '.jpg';

            $article['image'] = $this->convert($source);

            $this->createThumbnail($article['image'], $article['title']);

            $blocks = simplexml_load_string($article['text']);

            foreach ($blocks as $block) {

                switch ($block['type']) {

                    case 'image': {

                        $block['url'] = $this->host . $this->convert($block['url']);

                    }; break;

                    case 'gallery': {

                        foreach ($block->image as $image) {

                            $image['url'] = $this->host . $this->convert($image['url']);
                        }

                    }; break;
                }
            }

            $article['text'] = $blocks->asXML();

            $query = "UPDATE `article` SET `image` = '%s', `text` = '%s' WHERE `id` = '%d';\n\r";

            $params = array(

                $this->db['connection']->real_escape_string($article['image']),

                $this->db['connection']->real_escape_string($article['text']),

                $this->db['connection']->real_escape_string($article['id'])
            );

            fwrite($sql, vsprintf($query, $params));
        }

        fclose($sql);
    }

    /**
     * Зчитує статті з зображеннями з БД та зберігає їх
     */
    protected function setArticles(){

        $this->db['connection'] = new \mysqli(

            $this->db['host'], $this->db['user'], $this->db['password'], $this->db['name']);

        $this->db['connection']->set_charset('utf8');

        $query = '
        
                SELECT `id`, `title`, `image`, `text` FROM `article_old` 
                 
                WHERE LENGTH(`text`) > 0 AND `id` <> 1146';

        $result = $this->db['connection']->query($query);

        if ($result === false) {

            $message = sprintf('SQL error "%s"', $this->db['connection']->error);

            throw new Exception($message);
        }

        while ($article = $result->fetch_assoc()) $this->articles[] = $article;
    }

    /**
     * Повертає кількість імпортованих зображень
     *
     * @return integer Кількість імпортованих зображень
     */
    public function getImages() {

        return $this->images;
    }

    /**
     * Створює зменшене зображення
     *
     * @param string $image Відносна нова адреса оригінального зображення
     * @param string $title Найменування статті
     */
    protected function createThumbnail($image, $title) {

        $thumbnail = new Thumbnail($image, $title, $this->subtitle);

        $thumbnail->create();
    }

    /**
     * Імпортує файли зображень та створює файл змін для БД
     *
     * @param string $source Стара відносна адреса зображення
     * @throws Exception Image missing
     * @throws Exception Different hash
     * @throws Exception Cant make directory
     * @throws Exception Cant copy image
     * @return string Нова відносна адреса зображення
     */
    protected function convert($source) {

        $source = _PATH_PUBLIC . '/.import' . $source;

        if (!file_exists($source)) {

            $message = sprintf('Image missing: %s', $source);

            throw new Exception($message);
        }

        preg_match('/\/([a-f0-9]{32})\.jpg$/', $source, $matches);

        $hash = $matches[1];

        if ($hash != hash_file('md5', $source)) {

            $message = sprintf('Different hash for : %s', $source);

            throw new Exception($message);
        }

        $path = $directory = '';

        for ($i = 1; $i <= 3; $i++) {

            $start = ($i - 1) * 2;

            $directory .= '/' . substr($hash, $start, 2);

            $path = _PATH_PUBLIC . $directory;

            if (!file_exists($path) || !is_dir($path)) {

                if (!mkdir($path)) {

                    $message = sprintf('Cant make directory %s', $path);

                    throw new Exception($message);
                }
            }
        }

        $widthReal = getimagesize($source);

        $widthReal = $widthMax = $widthReal[0];

        foreach ($this->widths as $widthAllowed) {

            if ($widthAllowed > $widthReal) break;

            $widthMax = $widthAllowed;
        }

        $destination = $path . '/' . $hash . '.original.jpg';

        if (!copy($source, $destination)) {

            $message = sprintf('Cant copy image %s', $destination);

            throw new Exception($message);
        }

        $this->images ++;

        return $directory . '/' . $hash . '.' . $widthMax . '.jpg';
    }
}