<?php
/**
 * 描述：
 * Created at 2021/5/21 9:19 by 陈庙琴
 */

namespace wenshizhengxin\image_synthesizer\libs;


class AddFrame extends BaseTool
{
    protected $ltImagePath = null;
    protected $tImagePath = null;
    protected $rtImagePath = null;
    protected $rImagePath = null;
    protected $rbImagePath = null;
    protected $bImagePath = null;
    protected $lbImagePath = null;
    protected $lImagePath = null;

    protected $fillType = Constant::FILL_TYPE_INNER;

    protected $width = 20;

    public function addFrameImage($direction, $path)
    {
        $this->{$direction . 'ImagePath'} = $path;
    }

    public function setFillType($fillType)
    {
        $this->fillType = $fillType;
    }

    public function fillFrame(&$canvas, $frameImagePath, $startPosition, $endPosition, $direction = Constant::DIRECTION_HORIZONTAL)
    {
        $frameImageInfo = self::getImageInfo($frameImagePath);
        $realWidth = $frameImageInfo['width'];
        $realHeight = $frameImageInfo['height'];
        $fullWidth = $endPosition[0] - $startPosition[0];
        $fullHeight = $endPosition[1] - $startPosition[1];
        $this->logIt('-----start-----' . "\n");
        $this->logIt('总宽' . $fullWidth . '总高' . $fullHeight . "\n");
        $frameCanvas = self::getCanvas($frameImagePath, $frameImageInfo['extension']);
        if ($direction === Constant::DIRECTION_HORIZONTAL) { // 横向填充，先纵向适配
            $rate = $frameImageInfo['height'] / $this->width;
            $this->logIt('缩放倍数' . $rate . "\n");
            $realHeight = $this->width;
            $realWidth = $frameImageInfo['width'] / $rate;
            $this->logIt('第一次调整后大小:' . $realWidth . ' x ' . $realHeight . "\n");
            $num = intval($fullWidth / $realWidth);
            if ($num < 1) {
                $num = 1;
            }
            $this->logIt('填充数量:' . $num . "\n");
            $rate2 = $fullWidth / ($realWidth * $num); // 再拉伸
            $this->logIt('扩放倍数' . $rate . "\n");
            $realWidth *= $rate2;
            $realWidth = ceil($realWidth); // 向上取整，宁可多，也不能不够
            $realHeight *= $rate2;
            $this->logIt('第二次调整后大小:' . $realWidth . ' x ' . $realHeight . "\n");

            for ($i = 0; $i < $num; $i++) {
                imagecopyresized($canvas, $frameCanvas, $startPosition[0], $startPosition[1], 0, 0, $realWidth, $this->width, imagesx($frameCanvas), imagesy($frameCanvas));
                $startPosition[0] += $realWidth; // 填充完了右移啊
            }
        } else { // 纵向填充，先横向适配
            $rate = $frameImageInfo['width'] / $this->width;
            $this->logIt('缩放倍数' . $rate . "\n");
            $realWidth = $this->width;
            $realHeight = $frameImageInfo['width'] / $rate;
            $this->logIt('第一次调整后大小:' . $realWidth . ' x ' . $realHeight . "\n");

            $num = intval($fullHeight / $realHeight);
            if ($num < 1) {
                $num = 1;
            }
            $this->logIt('填充数量:' . $num . "\n");
            $rate2 = $fullHeight / ($realHeight * $num); // 再稍微拉伸
            $this->logIt('扩放倍数' . $rate . "\n");
            $realHeight *= $rate2;
            $realHeight = ceil($realHeight); // 向上取整，宁可多，也不能不够
            $realWidth *= $rate2;
            $this->logIt('第二次调整后大小:' . $realWidth . ' x ' . $realHeight . "\n");

            for ($i = 0; $i < $num; $i++) {
                imagecopyresized($canvas, $frameCanvas, $startPosition[0], $startPosition[1], 0, 0, $this->width, $realHeight, imagesx($frameCanvas), imagesy($frameCanvas));
                $startPosition[1] += $realHeight; // 填充完了下移啊
            }
        }
        $this->logIt('-----end-----');
    }

    public function make($format = Constant::FORMAT_JPEG)
    {
        $imageInfo = self::getImageInfo($this->imagePath);
        $finalWidth = $imageInfo['width'];
        $finalheight = $imageInfo['height'];
        if ($this->fillType === Constant::FILL_TYPE_OUTER) { // 外填充，图片得扩展
            $finalWidth += $this->width * 2;
            $finalheight += $this->width * 2;
        }

        $canvas = imagecreatetruecolor($finalWidth, $finalheight);
        $imageCanvas = self::getCanvas($this->imagePath, $imageInfo['extension']);
        $imgStartP = [0, 0];
        $imgEndP = [$finalWidth, $finalheight];
        if ($finalWidth > $imageInfo['width']) { // 是外扩充的，得内移
            $imgStartP = [$this->width, $this->width];
            $imgEndP = [$finalWidth - $this->width, $finalheight - $this->width];
        }

        imagecopyresized($canvas, $imageCanvas, $imgStartP[0], $imgStartP[1], 0, 0, $imgEndP[0] - $imgStartP[0], $imgEndP[1] - $imgStartP[1], imagesx($imageCanvas), imagesy($imageCanvas));

        // 先糊框
        $this->fillFrame($canvas, $this->tImagePath, [$this->width, 0], [$finalWidth - $this->width, $this->width]);
        $this->fillFrame($canvas, $this->rImagePath, [$finalWidth - $this->width, $this->width], [$finalWidth - $this->width, $finalheight - $this->width], Constant::DIRECTION_VERTICAL);
        $this->fillFrame($canvas, $this->bImagePath, [$this->width, $finalheight - $this->width], [$finalWidth - $this->width, $finalheight]);
        $this->fillFrame($canvas, $this->lImagePath, [0, $this->width], [$this->width, $finalheight - $this->width], Constant::DIRECTION_VERTICAL);

        // 再糊角
        $this->fillFrame($canvas, $this->ltImagePath, [0, 0], [$this->width, $this->width]);
        $this->fillFrame($canvas, $this->rtImagePath, [$finalWidth - $this->width, 0], [$finalWidth, $this->width]);
        $this->fillFrame($canvas, $this->lbImagePath, [0, $finalheight - $this->width], [$this->width, $finalheight]);
        $this->fillFrame($canvas, $this->rbImagePath, [$finalWidth - $this->width, $finalheight - $this->width], [$finalWidth, $finalheight]);


        if ($format === Constant::FORMAT_JPEG) {
            imagejpeg($canvas, $basename = rtrim(self::$saveDir, '/') . '/' . time() . mt_rand(100, 999) . '.' . Constant::EXTENSION_JPEG);
        } else if ($format === Constant::FORMAT_PNG) {
            imagepng($canvas, $basename = rtrim(self::$saveDir, '/') . '/' . time() . mt_rand(100, 999) . '.' . Constant::EXTENSION_PNG);
        }

        return $basename ?? null;

//        imagecopyresized($bgCanvas, $canvas, $startP[0], $startP[1], 0, 0, $realWidth, $realHeight, imagesx($canvas), imagesy($canvas));
    }
}