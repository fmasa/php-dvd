<?php

namespace DVD\Storage;

use ArrayIterator;

/**
 * Json based storage for records.
 *
 * This storage can be iterated while keeping the memory consumption to the
 * amount of memory used by the largest record.
 */
class Json extends AbstractStorage
{
    /**
     * @inheritDoc
     */
    public function storeRecording(array $recording)
    {
        $recordings = iterator_to_array($this);
        $recordings[] = $recording;


        file_put_contents(
            $this->filePath,
            json_encode($recordings, JSON_PRETTY_PRINT)
        );
    }

    public function getIterator()
    {
        return new ArrayIterator(json_decode(file_get_contents($this->filePath), true));
    }
}
