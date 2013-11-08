<?php

class pure_fs {

    /**
     * Get the directory size
     * @param directory $directory
     * @return integer
     */
    public static function getDirSize($directory) {
        $size = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            $size+=$file->getSize();
        }
        return $size;
    }

    /**
     * Removes a directory recursively
     * @param string $dir 
     * @param boolean $removeTopFolder 
     */
    public static function rm($dir, $removeTopFolder = true) {
        $files = glob($dir . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (substr($file, -1) == '/') {
                self::rm($file, true);
            } else {
                unlink($file);
            }
        }
        if (($removeTopFolder == true) and is_dir($dir)) {
            rmdir($dir);
        }
    }

    /**
     * Gets file metadata inside first comment (as ini file format)
     * @param string $sourceFile
     * @param int $maxLen
     * @return array
     */
    public static function getFileMetadata($sourceFile, $maxLen = null) {
        $metadata = array();
        $content = file_get_contents($sourceFile, false, null, -1, $maxLen);
        preg_match("/\/\*\*?(.*?)\*\*?\//s", $content, $comments);
        if (count($comments) > 1) {
            $comments = explode("\n", trim($comments[1], "* \n "));
            foreach ($comments as $j => $c) {
                $comments[$j] = trim($c, " * ; .");
            }
            if (count($comments) > 1) {
                $metadata = pure_str::parseIniString(implode("\n", $comments));
            }
        }
        return $metadata;
    }

    /**
     * Copy a file, or recursively copy a folder and its contents
     * @param       string   $source    Source path
     * @param       string   $dest      Destination path
     * @param       string   $permissions New folder creation permissions
     * @return      bool     Returns true on success, false on failure
     */
    public static function copy($source, $dest, $permissions = 0775) {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            self::copy("$source/$entry", "$dest/$entry");
        }

        // Clean up
        $dir->close();
        return true;
    }

    /**
     * Search for files in a folder recursively
     * @param string $path The folder path
     * @return array The full paths of the files
     */
    public static function getFileList($path) {
        $files = array();
        if (is_dir($path)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as $key => $value) {
                $file_path = realpath($key);
                if (is_file($file_path)) {
                    $files[] = $file_path;
                }
            }
        }
        return $files;
    }

    /**
     * Return the children folder names from the given path
     * @param string $path
     * @return array 
     */
    public static function getFolderList($path) {
        $folders = array();
        if (is_dir($path)) {
            $iterator = scandir($path);
            foreach ($iterator as $f) {
                if (is_dir($path . $f) && ($f != '.') && ($f != '..')) {
                    $folders[] = $f;
                }
            }
        }
        return $folders;
    }

    /**
     * Joins different files into a single one
     * @param array $source_files Array of file paths
     * @param string $destination_file Destination file path
     * @param string $separator Separator text. Default: line break
     * @param array $vars Variables to expose
     */
    public static function joinFiles($source_files, $destination_file, $separator = "\n", $vars = array()) {
        ob_start();
        extract($vars);
        foreach ($source_files as $f) {
            if (is_readable($f)) {
                include $f;
                echo $separator;
            }
        }
        $data = ob_get_clean();
        file_put_contents($destination_file, $data);
    }

    public static function getMimetype($file) {
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $type = finfo_file($finfo, $file);
            finfo_close($finfo);
        }

        if (!$type || $type == 'application/octet-stream') {
            $secondOpinion = @exec('file -b --mime-type ' . escapeshellarg($file), $foo, $returnCode);
            if (($returnCode == '0') && $secondOpinion) {
                $type = $secondOpinion;
            }
        }

        return $type;
    }

    public static function getExtension($filename) {
        $arr = explode('.', $filename);
        return array_pop($arr);
    }

    /**
     * 
     * @param string $filename File path or URL
     * @param int $bufferSize Read buffer size in bytes. Default: 10240 bytes (10KB)
     * @param mixed $onBufferRead Callback (callable function)
     * @return int Number of read bytes
     */
    public static function fileRead($filename, $bufferSize = 10240, $onBufferRead = null) {
        $handle = fopen($filename, 'r');
        $readbytes = 0;
        if ($handle) {
            while (!feof($handle)) {
                $data = fread($handle, $bufferSize);
                $readbytes += $bufferSize;
                if (is_callable($onBufferRead)) {
                    $onBufferRead($data);
                } else {
                    echo $data;
                }
            }
            if (isset($data)) {
                unset($data);
            }
            fclose($handle);
        }
        return $readbytes;
    }

    /**
     * 
     * @param string $filename File path or URL
     * @param mixed $onLineRead Callback (callable function)
     * @return int Number of read lines
     */
    public static function fileReadByLines($filename, $onLineRead = null) {
        $handle = fopen($filename, 'r');
        $readlines = 0;
        if ($handle) {
            while (!feof($handle)) {
                $data = fgets($handle);
                ++$readlines;
                if (is_callable($onLineRead)) {
                    $onLineRead($data);
                } else {
                    echo $data;
                }
            }
            if (isset($data)) {
                unset($data);
            }
            fclose($handle);
        }
        return $readlines;
    }

}