<?php

namespace Leedch\Filehandler;

/**
 * Folder operations
 *
 * @author leed
 */
class Folder
{
    /**
     * Returns $path if folder exists, creates it if not
     * @param string $path
     * @return string
     */
    public static function getFolder(string $path): string
    {
        $arrFolders = explode(DIRECTORY_SEPARATOR, $path);
        $fullPath = "";
        if ($arrFolders[0] !== "") {
            $fullPath = $arrFolders[0];
        }
        unset($arrFolders[0]);
        foreach ($arrFolders as $folder) {
            $fullPath .= DIRECTORY_SEPARATOR . $folder;
            if (file_exists($fullPath) && is_dir($fullPath)) {
                continue;
            }
            mkdir($fullPath, 0775);
        }
        return $path;
    }
}
