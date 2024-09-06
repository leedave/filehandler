<?php

namespace Leedch\Filehandler;

use Leedch\Filehandler\Image;
use chillerlan\QRCode\QRCode as QrLib;
use chillerlan\QRCode\QROptions;

/**
 * Description of QrCode
 *
 * @author leed
 */
abstract class QrCode extends Image
{

    /* Abstract Example
    public function initFile()
    {
        $baseDir = __DIR__ . "/../../../uploads";
        $this->storageDirectory = $baseDir . "/wochenplan/qr";
        $this->webFolder = $baseDir . "/../web/data/img/wochenplan/qr";
        $this->adminFolder = $baseDir . "/../admin/data/img/wochenplan/qr";
        $this->webPath = "data/img/wochenplan/rezept/qr";
        $this->adminWebPath = "/data/img/wochenplan/qr";
        $this->createItemFolders();
    }*/

    public function outputSvg(string $filename)
    {
        if (!file_exists($this->storageDirectory . '/' . $filename)) {
            return "";
        }
        return file_get_contents($this->storageDirectory . '/' . $filename);
    }

    public function generateFile(string $content, string $filename)
    {
        if (file_exists($this->storageDirectory . '/' . $filename)) {
            return;
        }
        $options = new QROptions([
            'version'    => 3,
            'outputType' => QrLib::OUTPUT_MARKUP_SVG,
            'eccLevel'   => QrLib::ECC_L,
        ]);
        $qrCode = new QrLib($options);
        $qrCode->render($content, $this->storageDirectory . '/' . $filename);
    }
}
