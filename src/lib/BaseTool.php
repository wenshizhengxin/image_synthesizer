<?php
namespace wenshizhengxin\image_synthesizer;

class BaseTool
{
    public static $saveDir = null;

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
}