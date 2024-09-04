<?php

namespace Leedch\Filehandler;

/**
 * General File handling, also used for uploads
 * @author leed
 */
abstract class File
{
    protected $storageDirectory;
    protected $webFolder;
    protected $adminFolder;

    public abstract function initFile();

    protected function createItemFolders()
    {
        static::createFolderIfNotExists($this->storageDirectory);
        static::createFolderIfNotExists($this->webFolder);
        static::createFolderIfNotExists($this->adminFolder);
    }

    public static function createFolderIfNotExists(string $folderPath)
    {
        if (file_exists($folderPath) && is_dir($folderPath)) {
            return;
        }
        if (substr((string) $folderPath, 0, 1) === "/") {
            $folderPath = substr((string) $folderPath, 1);
        }
        $arrPath = explode("/", $folderPath);
        $bottomLevel = array_pop($arrPath);
        $parentPath = implode("/", $arrPath);
        if ($parentPath !== "") {
            static::createFolderIfNotExists("/".$parentPath);
        }
        mkdir("/".$parentPath . "/" .$bottomLevel);
    }

    /**
     * Prevent files from being overwritten. Checks for existing files, if the
     * name is taken, a new name is returned
     * @param string $requestedPath
     * @return string
     */
    protected static function getSaveName(string $requestedPath): string
    {
        if (!file_exists($requestedPath)) {
            return $requestedPath;
        }
        $arrFolders = explode("/", $requestedPath);
        $fileName = array_pop($arrFolders);
        $folder = implode("/", $arrFolders);
        $arrFileName = explode(".", $fileName);
        $suffix = array_pop($arrFileName);
        $fileNameWithoutSuffix = implode(".", $arrFileName);
        for ($i = 1;$i < 1000;$i++) {
            $fileNameSuggestion = $fileNameWithoutSuffix."_".$i.".".$suffix;
            if (!file_exists($folder . "/" . $fileNameSuggestion)) {
                break;
            }
        }
        return $folder . "/" . $fileNameSuggestion;
    }

    public static function replaceFilePath(string $source, string $webFolder): string
    {
        $arrSource = explode("/", $source);
        $fileName = array_pop($arrSource);
        return $webFolder . "/" . $fileName;
    }
}
