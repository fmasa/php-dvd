<?php

namespace VCR\Storage;

use VCR\Util\Assertion;
use VCR\VCRException;

/**
 * Abstract base for reading and storing records.
 *
 * A Storage can be iterated using standard loops.
 * New recordings can be stored.
 */
abstract class AbstractStorage implements Storage, \IteratorAggregate
{
    /**
     * @var string Path to storage file.
     */
    protected $filePath;

    /**
     * @var boolean If the cassette file is new.
     */
    protected $isNew = false;

    /**
     * Creates a new file store.
     *
     * If the cassetteName contains PATH_SEPARATORs, subfolders of the
     * cassettePath are autocreated when not existing.
     *
     * @param string  $cassettePath   Path to the cassette directory.
     * @param string  $cassetteName   Path to the cassette file, relative to the path.
     * @param string  $defaultContent Default data for this cassette if its not existing
     */
    public function __construct($cassettePath, $cassetteName, $defaultContent = '[]')
    {
        Assertion::directory($cassettePath, "Cassette path '{$cassettePath}' is not existing or not a directory");

        $this->filePath = rtrim($cassettePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $cassetteName;

        if (!is_dir(dirname($this->filePath))) {
            mkdir(dirname($this->filePath), 0777, true);
        }

        if (!file_exists($this->filePath) || 0 === filesize($this->filePath)) {
            file_put_contents($this->filePath, $defaultContent);
            $this->isNew = true;
        }

        Assertion::file($this->filePath, "Specified path '{$this->filePath}' is not a file.");
        Assertion::readable($this->filePath, "Specified file '{$this->filePath}' must be readable.");
    }

    /**
     * Returns true if the file did not exist and had to be created.
     *
     * @return boolean TRUE if created, FALSE if not
     */
    public function isNew()
    {
        return $this->isNew;
    }
}
