<?php
/**
 * 描述：
 * Created at 2021/5/14 9:09 by Temple Chan
 */

namespace wenshizhengxin\image_synthesizer\libs;


class AddBackground extends BaseTool
{
    protected $imagePath = null;
    protected $backgroundImagePath = null;
    protected $startPosition = [];
    protected $endPosition = [];
    protected $fixedAspectRatio = true; // 锁定纵横比
    protected $baseWidth = null;

    /**
     * 功能：添加背景图片路径
     * Created at 2021/5/14 9:18 by Temple Chan
     * @param $path
     * @return $this
     * @throws \Exception
     */
    public function addBackgroundImage($path)
    {
        if (is_file($path) === false) {
            throw new \Exception('背景图片不存在');
        }
        $this->backgroundImagePath = $path;
        return $this;
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
        if (is_file($path) === false) {
            throw new \Exception('图片不存在');
        }
        $this->imagePath = $path;
        return $this;
    }

    /**
     * 功能：设置主图在背景图中的起始位点
     * Created at 2021/5/14 9:18 by Temple Chan
     * @param $x
     * @param $y
     * @return $this
     */
    public function setStartPosition($x, $y)
    {
        $this->startPosition = [$x, $y];
        return $this;
    }

    /**
     * 功能：设置主图在背景图中的终止位点
     * Created at 2021/5/14 9:18 by Temple Chan
     * @param $x
     * @param $y
     * @return $this
     */
    public function setEndPosition($x, $y)
    {
        $this->endPosition = [$x, $y];
        return $this;
    }

    /**
     * 功能：是否固定主图纵横比
     * Created at 2021/5/14 9:18 by Temple Chan
     * @param $value
     * @return $this
     */
    public function setFixedAspectRatio($value)
    {
        $this->fixedAspectRatio = boolval($value);
        return $this;
    }

    /**
     * 功能：设置主图在背景图中的实际宽度（高度也会随之更改）
     * Created at 2021/5/14 9:19 by Temple Chan
     * @param $baseWidth
     * @return $this
     */
    public function setBaseWidth($baseWidth)
    {
        $this->baseWidth = $baseWidth;
        return $this;
    }

    /**
     * 功能：检查基础配置
     * Created at 2021/5/14 9:19 by Temple Chan
     * @return string
     */
    protected function checkConfig()
    {
        if (self::$saveDir === null) {
            return '图片保存路径未设置，请使用::setSaveDirectory()';
        }
        if (is_file($this->imagePath) === false) {
            return '主图片未找到，请使用->addImage()';
        }
        if (is_file($this->backgroundImagePath) === false) {
            return '背景图片未找到，请使用->addBackgroundImage()';
        }
        if (count($this->startPosition) !== 2) {
            return '起始位点未设置，请使用->setStartPosition()';
        }
        return '';
    }

    /**
     * 功能：生成决定图片
     * Created at 2021/5/14 9:49 by Temple Chan
     * @param string $format
     * @return string|null
     * @throws \Exception
     */
    public function make($format = Constant::EXTENSION_JPEG)
    {
        $res = $this->checkConfig();
        if ($res !== '') {
            throw new \Exception($res);
        }

        // 获取图片们的基本信息
        $imageInfo = self::getImageInfo($this->imagePath);
        $backgroundImageInfo = self::getImageInfo($this->backgroundImagePath);

        $bgCanvas = self::getCanvas($this->backgroundImagePath, $backgroundImageInfo['extension']);
        $canvas = self::getCanvas($this->imagePath, $imageInfo['extension']);

        $baseHeight = $this->baseWidth / $imageInfo['aspect_rate'];
//        var_dump($this->baseWidth);
//        var_dump($baseHeight);

        imagecopyresized($bgCanvas, $canvas, $this->startPosition[0], $this->startPosition[1], 0, 0, $this->baseWidth, $baseHeight, imagesx($canvas), imagesy($canvas));


        if ($format === Constant::FORMAT_JPEG) {
            imagejpeg($bgCanvas, $basename = rtrim(self::$saveDir, '/') . '/' . mt_rand(100, 999) . '.' . Constant::EXTENSION_JPEG);
        } else if ($format === Constant::FORMAT_PNG) {
            imagepng($bgCanvas, $basename = rtrim(self::$saveDir, '/') . '/' . mt_rand(100, 999) . '.' . Constant::EXTENSION_PNG);
        }

        return $basename ?? null;
    }
}