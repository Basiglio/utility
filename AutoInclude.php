<?php

namespace classes;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * The AutoInclude class is responsible for including all PHP files within a given folder.
 */
class AutoInclude {

    private string $folderPath;

    public function __construct($folderPath)
    {

        $this->folderPath = $folderPath;

    }

    public function performInclude(): void
    {

        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->folderPath), RecursiveIteratorIterator::SELF_FIRST);

        foreach ($files as $file) {

            if (is_file($file)) {

                include $file;

            }

        }

    }

}
