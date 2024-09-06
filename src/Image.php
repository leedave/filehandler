<?php

namespace Leedch\Filehandler;

use Exception;
use Leedch\Filehandler\File;
use Leedch\Message\Message;
use Leedch\Translate\Translate as T;

/**
 * Handle Image Uploads
 * @author leed
 */
abstract class Image extends File
{
    protected $storageDirectory;
    protected $webFolder;
    protected $adminFolder;
    protected $webPath;
    protected $adminWebPath;
    protected $tmpFile;
    protected $width;
    protected $height;

    /**
     *
     * @param string $identifier Field name in $_FILES
     * @return bool
     */
    public static function isValidImageFormatUpload(string $identifier): bool
    {
        if (!isset($_FILES[$identifier]) || !isset($_FILES[$identifier]['tmp_name'])) {
            return false;
        }
        try {
            static::detectImageType($_FILES[$identifier]['tmp_name']);
        } catch (Exception $ex) {
            Message::addErrorMessage(T::__("error.image.notSaved.invalidFormat"));
            return false;
        }
        return true;
    }

    protected static function detectImageType(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new Exception(T::__("error.image.notSaved.fileNotFound"));
        }
        $type = exif_imagetype($filePath);
        $allowedTypes = [IMAGETYPE_GIF => "GIF", IMAGETYPE_JPEG => "JPG", IMAGETYPE_PNG => "PNG"];
        if (!in_array($type, array_keys($allowedTypes))) {
            throw new Exception(T::__("error.image.notSaved.invalidFormat"));
        }
        return $allowedTypes[$type];
    }


    protected function cropImage(
        string $tmpFile,
        string $target
    ): string
    {
        $aspectRatio = $this->width / $this->height;
        list($srcWidth, $srcHeight, $mime) = @getimagesize($tmpFile);
        $srcRatio = $srcWidth / $srcHeight;
        if ($aspectRatio <= $srcRatio) {
            $srcHeightCalculated = $srcHeight;
            $srcWidthCalculated = $srcHeight * $aspectRatio;
            $srcHeightTop = 0;
            if ($srcWidthCalculated > $srcWidth) {
                $srcWidthCalculated = $srcWidth;
                $srcHeightCalculated = $srcWidth / $aspectRatio;
            }
            $srcWidthLeft = round(($srcWidth - $srcWidthCalculated) / 2);
        } else {
            $srcWidthCalculated = $srcWidth;
            $srcHeightCalculated = $srcWidth / $aspectRatio;
            $srcWidthLeft = 0;
            if ($srcHeightCalculated > $srcHeight) {
                $srcHeightCalculated = $srcHeight;
                $srcWidthCalculated = $srcHeight * $aspectRatio;
            }
            $srcHeightTop = round(($srcHeight - $srcHeightCalculated) / 2);
        }
        $type = static::detectImageType($tmpFile);
        $savedTo = static::saveGeneratedImage((string) $target, (string) $tmpFile, (string) $type, (int) $this->width, (int) $this->height, (int) $srcWidthCalculated, (int) $srcHeightCalculated, 0, 0, (int) $srcWidthLeft, (int) $srcHeightTop);
        return $savedTo;
    }

    protected function saveGeneratedImage(
        string $target,
        string $original,
        string $type,
        int $newWidth,
        int $newHeight,
        int $srcWidth,
        int $srcHeight,
        int $leftTarget = 0,
        int $topTarget = 0,
        int $leftSrc = 0,
        int $topSrc = 0,
        bool $transparency = true
    ): string
    {
        $arrTypes = ["JPG", "PNG", "GIF"];
        if (!in_array($type, $arrTypes)) {
            throw new Exception(T::__("error.image.notSaved.invalidFormat"));
        }

        if (!$transparency || $type !== "PNG") {
            $canvas = static::createWhiteImageCanvas($type, $newWidth, $newHeight);
        } else {
            $canvas = static::createTransparentImageCanvas($newWidth, $newHeight);
        }

        $image = static::prepareOriginalForResampling($type, $original, $transparency);

        imagecopyresampled($canvas, $image, $leftTarget, $topTarget, $leftSrc, $topSrc, $newWidth, $newHeight, $srcWidth, $srcHeight);
        $done = self::saveNewImage($canvas, $type, $target);
        if ($done) {
            imagedestroy($image);
            imagedestroy($canvas);
            return $target;
        }
        throw new Exception(T::__("error.image.notSaved.saveError"));
    }//END saveGeneratedImage($newCanvas, $)

    protected static function saveNewImage(&$canvas, string $type, string $filePath): bool
    {
        if ($type === "JPG") {
            $done = imagejpeg($canvas, $filePath, 80);
        } elseif ($type === "PNG"){
            $done = imagepng($canvas, $filePath);
        } else {
            $done = imagegif($canvas, $filePath);
        }
        chmod($filePath, 0775);
        return $done;
    }

    protected static function prepareOriginalForResampling(string $type, string $pathOriginal, bool $useTransparency = true)
    {
        if ($type === "JPG") {
            $image=@imagecreatefromjpeg($pathOriginal);
        } elseif ($type === "PNG") {
            $image=@imagecreatefrompng($pathOriginal);
            if ($useTransparency) {
                imagealphablending($image, false);
                imagesavealpha($image, true);
            }
        } else {
            $image=@imagecreatefromgif($original);
        }
        return $image;
    }

    protected static function createWhiteImageCanvas(string $type, int $width, int $height)
    {
        if (in_array($type, ["JPG", "PNG"])) {
            $canvas = imagecreatetruecolor($width, $height);
        } else {
            $canvas = imagecreate($width, $height);
        }
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilltoborder($canvas, 0,0, $white, $white);
        return $canvas;
    }

    protected static function createTransparentImageCanvas(int $width, int $height)
    {
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        return $canvas;
    }
}
