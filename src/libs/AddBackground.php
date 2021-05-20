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
    protected $baseHeight = null;

    /**
     * 功能：添加背景图片路径
     * Created at 2021/5/14 9:18 by Temple Chan
     * @param $path
     * @return $this
     * @throws \Exception
     */
    public function addBackgroundImage($path)
    {
//        if (is_file($path) === false) {
//            throw new \Exception('背景图片不存在');
//        }
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
//        if (is_file($path) === false) {
//            throw new \Exception('图片不存在');
//        }
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
//        if (is_file($this->imagePath) === false) {
//            return '主图片未找到，请使用->addImage()';
//        }
//        if (is_file($this->backgroundImagePath) === false) {
//            return '背景图片未找到，请使用->addBackgroundImage()';
//        }
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
    public function make($format = Constant::FORMAT_JPEG)
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

        $this->baseWidth = $this->endPosition[0] - $this->startPosition[0];
        $this->baseHeight = $this->endPosition[1] - $this->startPosition[1];

        // 自动调整
        $realWidth = $imageInfo['width'];
        $realHeight = $imageInfo['height'];
        if ($realWidth < $this->baseWidth && $realHeight < $this->baseHeight) { // 能框住，那原图就行
        } else {
            if ($imageInfo['aspect_rate'] < $backgroundImageInfo['aspect_rate']) { // 高度过分了，缩
                $r = $realHeight / $this->baseHeight;
                $realHeight = $this->baseHeight;
                $realWidth = $realWidth / $r;
            } else { // 宽度过分了，缩
                $r = $realWidth / $this->baseWidth;
                $realWidth = $this->baseWidth;
                $realHeight = $realHeight / $r;
            }
        }

        // 选开始位点
        $startP[0] = $this->startPosition[0] + ($this->baseWidth - $realWidth) / 2;
        $startP[1] = $this->startPosition[1] + ($this->baseHeight - $realHeight) / 2;
//        var_dump($this->baseWidth);
//        var_dump($baseHeight);

        imagecopyresized($bgCanvas, $canvas, $startP[0], $startP[1], 0, 0, $realWidth, $realHeight, imagesx($canvas), imagesy($canvas));

        if ($format === Constant::FORMAT_JPEG) {
            imagejpeg($bgCanvas, $basename = rtrim(self::$saveDir, '/') . '/' . time() . mt_rand(100, 999) . '.' . Constant::EXTENSION_JPEG);
        } else if ($format === Constant::FORMAT_PNG) {
            imagepng($bgCanvas, $basename = rtrim(self::$saveDir, '/') . '/' . time() . mt_rand(100, 999) . '.' . Constant::EXTENSION_PNG);
        }

        return $basename ?? null;
    }
}