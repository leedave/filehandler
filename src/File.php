<?php

namespace Leedch\Filehandler;

use Leedch\Filehandler\Folder;

/**
 * Folders are simpler types of files
 * Here I add additional methods designed specifically for files
 *
 * @author leed
 */
class File extends Folder
{
    /**
     * Saves a File and returns $fullPath , use to prevent duplicate names
     * @param string $fullPath
     * @return string
     */
    public static function saveFile(string $fullPath, string $content): string
    {
        if (file_exists($fullPath)) {
            $arrFilePath = explode(DIRECTORY_SEPARATOR, $fullPath);
            $fileName = array_pop($arrFilePath);
            $newFileName = self::iterateFileName($fileName);
            $arrFilePath[] = $newFileName;
            $newFullPath = implode(DIRECTORY_SEPARATOR, $arrFilePath);
            return self::saveFile($newFullPath, $content);
        }
        $file = fopen($fullPath, "w");
        fputs($file, $content);
        fclose($file);
        chmod($fullPath, 0775);
        return $fullPath;
    }

    /**
     * Puts a number on the end of a file name, prevents overwriting
     * @param string $fileName
     * @return string
     */
    protected static function iterateFileName(string $fileName): string
    {
        //First remove Extension
        $arrFileParts = explode(".", $fileName);
        $extension = array_pop($arrFileParts);
        $tempFileName = implode(".", $arrFileParts);
        $arrFileName = explode("_", $tempFileName);
        if (is_numeric($arrFileName[(count($arrFileName) - 1)])) {
            $iterator = (int) array_pop($arrFileName);
            $iterator++;
            $arrFileName[] = $iterator;
            $newFileName = implode("_", $arrFileName);
            return $newFileName . "." . $extension;
        }
        return $tempFileName."_1." . $extension;
    }
}
