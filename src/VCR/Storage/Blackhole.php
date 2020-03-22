<?php

namespace VCR\Storage;

use Exception;
use Traversable;

/**
 * Backhole storage, the storage that looses everything.
 */
class Blackhole implements Storage, \IteratorAggregate
{
    /**
     * {@inheritDoc}
     */
    public function storeRecording(array $recording)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function isNew()
    {
        return true;
    }

    public function getIterator()
    {
        return new \EmptyIterator();
    }
}
