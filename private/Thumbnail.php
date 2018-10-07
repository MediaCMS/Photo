<?php
/**
 * Клас роботи зі зменшенним зображенням
 *
 * @author      Артем Висоцький <a.vysotsky@gmail.com>
 * @package     Varianty\Photo\Thumbnail
 * @link        https://варіанти.укр
 * @copyright   Всі права застережено (c) 2018 Варіанти
 */

namespace Varianty\Photo;

class Thumbnail {

    /** @var string Відносний шлях до файла зображення з якого роблять зменшене зображення */
    protected $sourceUri;

    /** @var string Абсолютний шлях до файл зображення з якого роблять зменшене зображення */
    protected $sourceFile;

    /** @var resource Ресурс зображення з якого роблять зменшене зображення */
    protected $sourceResource;

    /** @var string Абсолютний шлях до файлу зменшеного зображення */
    protected $destinationFile;

    /** @var resource Ресурс зменшеного зображення */
    protected $destinationResource;

    /** @var integer Ширина зменшеного зображення */
    protected $destinationWidth = 960;

    /** @var integer Висота зменшеного зображення */
    protected $destinationHeight = 540;

    /** @var string Директорії з файлом зображення */
    protected $directory;

    /** @var string Хеш-код файла зображення */
    protected $hash;

    /** @var string Текст заголовку */
    protected $titleText;

    /** @var string Назва шрифта для заголовка */
    protected $titleFontTitle = 'OpenSans-Bold.ttf';

    /** @var string Розмір шрифта для заголовка */
    protected $titleFontSize = 48;

    /** @var integer Колір тексту зменшеного зображення */
    protected $titleFontColor;

    /** @var integer Колір тіні зменшеного зображення */
    protected $titleFontShadowColor;

    /** @var string Текст підзаголовка */
    protected $subtitleText;

    /** @var string Назва шрифта для підзаголовка */
    protected $subtitleFontTitle = 'OpenSans-Regular.ttf';

    /** @var string Розмір шрифта для підзаголовка */
    protected $subtitleFontSize = 21;

    /** @var integer Колір тексту зменшеного зображення */
    protected $subtitleFontColor;

    /** @var integer Колір тіні зменшеного зображення */
    protected $subtitleFontShadowColor;

    /** @var string Назва сайта як лого для зовнішнього зменшеного зображення */
    protected $logoText = 'Варіанти';

    /** @var string Назва шрифта для підзаголовка */
    protected $logoFontTitle = 'OpenSans-Bold.ttf';

    /** @var string Розмір шрифта для підзаголовка */
    protected $logoFontSize = 21;

    /** @var integer Колір тексту зменшеного зображення */
    protected $logoFontColor;

    /** @var integer Колір тіні зменшеного зображення */
    protected $logoFontShadowColor;

    /** array Шлях до теки зі шрифтами */
    protected $fonts;


    /**
     * Конструктор
     *
     * @throws Exception Unknown exception
     */
    public function __construct($image, $title, $subtitle) {

        $this->setSourceFile($image);

        $this->setDestinationFile();

        $this->setTitleText($title);

        $this->setSubtitleText($subtitle);

        $this->fonts = _PATH_PRIVATE . '/fonts';

        $colorText = imagecolorallocate($this->sourceResource, 255, 255, 255);

        $this->titleFontColor = $this->subtitleFontColor = $colorText;

        $colorShadow = imagecolorallocate($this->sourceResource, 0, 0, 0);

        $this->titleFontShadowColor = $this->subtitleFontShadowColor = $colorShadow;

        $this->logoFontColor = imagecolorallocate($this->sourceResource, 180, 230, 0);

        $this->logoFontShadowColor = $colorShadow;
    }

    /**
     * Встановлює файл джерела зменшеного зображення
     *
     * @param string $file Відносна адреса файлу зображення
     * @throws Exception Bad source image name
     * @throws Exception Source image file no exists
     * @throws Exception Can`t read source image file
     */
    protected function setSourceFile($image) {

        $pattern = '\/[a-f0-9]{2}\/[a-f0-9]{2}\/[a-f0-9]{2}';

        $pattern = "/($pattern)\/([a-f0-9]{32})\.\d{4}\.jpg/";

        if (!preg_match($pattern, $image, $matches)) {

            throw new Exception(sprintf("Bad source image name '%s'", $image), 130);
        }

        $this->directory = $matches[1];

        $this->hash = $matches[2];

        $this->sourceUri = $this->directory . '/' . $this->hash . '.original.jpg';

        $this->sourceFile = _PATH_STORAGE . $this->sourceUri;

        if (!file_exists($this->sourceFile)) {

            $exception = "Source image file no exists '%s'";

            throw new Exception(sprintf($exception, $this->sourceUri), 131);
        }

        $size = getimagesize($this->sourceFile);

        if (!$size) {

            $exception = "Can`t read source image file '%s'";

            throw new Exception(sprintf($exception, $this->sourceUri), 141);
        }

        $this->sourceResource = imagecreatefromjpeg($this->sourceFile);
    }

    /**
     * Встановлює файл зменшеного зображення
     */
    protected function setDestinationFile() {

        $this->destinationFile = '/' . $this->hash . '.thumbnail.jpg';

        $this->destinationFile = _PATH_STORAGE . $this->directory . $this->destinationFile;
    }

    /**
     * Встановлює заголовок для зовнішнього файла зображення
     *
     * @param string $title Назва заголовка для зменшеного зображення
     * @throws Exception Bad image title
     */
    protected function setTitleText($title) {

        if (!preg_match('/^.{3,128}$/u', $title)) {

            throw new Exception(sprintf("Bad image title '%s'", $title), 123);
        }

        $this->titleText = $title;
    }

    /**
     * Встановлює підзаголовок для зовнішнього файла зображення
     *
     * @param string $subtitle Назва підзаголовка для зменшеного зображення
     * @throws Exception Bad image subtitle
     */
    protected function setSubtitleText($subtitle) {

        if (!preg_match('/^.{3,128}$/u', $subtitle)) {

            throw new Exception(sprintf("Bad image subtitle '%s'", $subtitle), 125);
        }

        $this->subtitleText = $subtitle;
    }

    /**
     * Створює зменшене зображення
     *
     * @throws Exception Thumbnail creating error
     */
    public function create() {

        $this->crop();

        $this->addFilters();

        $this->addTitle();

        $this->addSubtitle();

        $this->addLogo();

        if (!imagejpeg($this->sourceResource, $this->destinationFile, 88)) {

            $exception = "Thumbnail creating error '%s'";

            throw new Exception(sprintf($exception, $this->sourceUri), 151);
        }
    }

    /**
     * Обрізає зовнішнє зображення
     */
    protected function crop() {

        $sourceWidth = imagesx($this->sourceResource);

        $sourceHeight = imagesy($this->sourceResource);

        $sourceHeightOffset = round(($sourceHeight - $this->destinationHeight) / 2);

        $destinationResource = imagecreatetruecolor($this->destinationWidth, $this->destinationHeight);

        imagecopyresampled($destinationResource, $this->sourceResource, 0, 0, 0, $sourceHeightOffset,

            $this->destinationWidth, $this->destinationHeight,

            $this->destinationWidth, $this->destinationHeight);

        $this->sourceResource = $destinationResource;
    }

    /**
     * Додає фільтри
     */
    protected function addFilters() {

        imagefilter($this->sourceResource, IMG_FILTER_BRIGHTNESS, -96);

        imagefilter($this->sourceResource, IMG_FILTER_CONTRAST, 19);
    }

    /**
     * Додає заголовок
     */
    protected function addTitle() {

        $fontFile = $this->fonts . '/' . $this->titleFontTitle;

        $top = ($this->destinationHeight - $this->titleFontSize) / 2 + (int) $this->titleFontSize;

        $box = imagettfbbox($this->titleFontSize, 0, $fontFile, $this->titleText);

        $left = ($this->destinationWidth - abs($box[4] - $box[0])) / 2;

        imagettftext($this->sourceResource, $this->titleFontSize, 0, $left + 1, $top + 1,

            $this->titleFontShadowColor, $fontFile, $this->titleText);

        imagettftext($this->sourceResource, $this->titleFontSize, 0, $left, $top,

            $this->titleFontColor, $fontFile, $this->titleText);
    }

    /**
     * Додає підзаголовок
     */
    protected function addSubtitle() {

        $fontFile = $this->fonts . '/' . $this->subtitleFontTitle;

        $box = imagettfbbox($this->subtitleFontSize, 0, $fontFile, $this->subtitleText);

        $left = ($this->destinationWidth - abs($box[4] - $box[0])) / 2;

        imagettftext($this->sourceResource, $this->subtitleFontSize, 0, $left + 1, 171,

            $this->subtitleFontShadowColor, $fontFile, $this->subtitleText);

        imagettftext($this->sourceResource, $this->subtitleFontSize, 0, $left, 170,

            $this->subtitleFontColor, $fontFile, $this->subtitleText);
    }

    /**
     * Додає лого
     */
    protected function addLogo() {

        $fontFile = $this->fonts . '/' .  $this->logoFontTitle;;

        $top = 410;

        $box = imagettfbbox($this->logoFontSize, 0, $fontFile, $this->logoText);

        $left = ($this->destinationWidth - abs($box[4] - $box[0])) / 2;

        imagettftext($this->sourceResource, $this->logoFontSize, 0, $left + 1, $top + 1,

            $this->logoFontShadowColor, $fontFile, $this->logoText);

        imagettftext($this->sourceResource, $this->logoFontSize, 0, $left, $top,

            $this->logoFontColor, $fontFile, $this->logoText);
    }
}
