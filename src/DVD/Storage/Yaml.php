<?php

namespace DVD\Storage;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

/**
 * Yaml based storage for records.
 *
 * This storage can be iterated while keeping the memory consumption to the
 * amount of memory used by the largest record.
 */
class Yaml extends AbstractStorage
{
    /**
     * @var Parser Yaml parser.
     */
    protected $yamlParser;

    /**
     * @var  Dumper Yaml writer.
     */
    protected $yamlDumper;

    /**
     * Creates a new YAML based file store.
     *
     * @param string $cassettePath Path to the cassette directory.
     * @param string $cassetteName Path to a file, will be created if not existing.
     * @param Parser $parser Parser used to decode yaml.
     * @param Dumper $dumper Dumper used to encode yaml.
     */
    public function __construct($cassettePath, $cassetteName, Parser $parser = null, Dumper $dumper = null)
    {
        parent::__construct($cassettePath, $cassetteName, '');

        $this->yamlParser = $parser ?: new Parser();
        $this->yamlDumper = $dumper ?: new Dumper();
    }

    /**
     * @inheritDoc
     */
    public function storeRecording(array $recording)
    {
        file_put_contents($this->filePath, "\n" . $this->yamlDumper->dump(array($recording), 4), FILE_APPEND);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->yamlParser->parseFile($this->filePath) ?? []);
    }
}
