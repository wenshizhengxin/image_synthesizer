<?php
namespace wenshizhengxin\image_synthesizer\libs;

class BaseTool
{
    protected static $saveDir = null;
    protected $imagePath = null;

    public static function setSaveDirectory($directory)
    {
        if (is_dir($directory) === false) {
            mkdir($directory, 0777, true);
        }
        self::$saveDir = $directory;
    }

    public static function getImageInfo($path)
    {
        $data = getimagesize($path);
        $info = [
            'width' => $data[0],
            'height' => $data[1],
            'mime' => $data['mime'],
            'aspect_rate' => $data[0] / $data[1],
        ];
        $info['extension'] = ltrim(pathinfo($path, PATHINFO_EXTENSION), '.');

        return $info;
    }

    public static function getCanvas($path, $extension)
    {
        if ($extension === Constant::EXTENSION_JPEG) {
            $canvas = imagecreatefromjpeg($path);
        } else if ($extension === Constant::EXTENSION_PNG) {
            $canvas = imagecreatefrompng($path);
        }

        return $canvas;
    }

    /**
     * 功能：添加主图路径
     * Created at 2021/5/14 9:18 by Temple Chan
     * @param $path
     * @return $this
     * @throws \Exception
     */
    public function addImage($path)
    {
        $this->imagePath = $path;
        return $this;
    }

    public function logIt($text)
    {
        file_put_contents('image_synthesizer.log', $text, FILE_APPEND);
    }
}