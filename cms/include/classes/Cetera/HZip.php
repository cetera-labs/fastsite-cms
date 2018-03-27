<?php
namespace Cetera;

class HZip
{
  /**
   * Add files and sub-directories in a folder to zip file.
   *
   * @param string $folder
   * @param \ZipArchive $zipFile
   * @param int $exclusiveLength Number of text to be exclusived from the file path.
   */
  public static function folderToZip($folder, &$zipFile, $exclusiveLength, $exclude = array('.git'), $prefix = '') {
	if (!file_exists($folder)) return;
    $handle = opendir($folder);
    while (false !== $f = readdir($handle)) {
      if ($f != '.' && $f != '..') {
		  
		if (in_array($f, $exclude)) continue;
		  
        $filePath = "$folder/$f";
        // Remove prefix from file path before add to zip.
        $localPath = $prefix.substr($filePath, $exclusiveLength);
        if (is_file($filePath)) {
          $zipFile->addFile($filePath, $localPath);
        } elseif (is_dir($filePath)) {
          // Add sub-directory.
          $zipFile->addEmptyDir($localPath);
          self::folderToZip($filePath, $zipFile, $exclusiveLength, $exclude, $prefix);
        }
      }
    }
    closedir($handle);
  }

  /**
   * Zip a folder (include itself).
   * Usage:
   *   HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip');
   *
   * @param string $sourcePath Path of directory to be zip.
   * @param string $outZipPath Path of output zip file.
   */
  public static function zipDir($sourcePath, $outZipPath, $exclude = array('.git') )
  {
    $pathInfo = pathInfo($sourcePath);
    $parentPath = $pathInfo['dirname'];
    $dirName = $pathInfo['basename'];

    $z = new \ZipArchive();
    $z->open($outZipPath, \ZipArchive::CREATE);
    $z->addEmptyDir($dirName);
    self::folderToZip($sourcePath, $z, strlen("$parentPath/"), $exclude);
    $z->close();
  }
} 