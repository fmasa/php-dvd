<?php

namespace DVD\Util;

use Assert\Assertion as BaseAssertion;
use DVD\DVDException;

class Assertion extends BaseAssertion
{
    protected static $exceptionClass = DVDException::class;

    const INVALID_CALLABLE = 910;

    /**
     * Assert that the value is callable.
     *
     * @param  mixed  $value Variable to check for a callable.
     * @param  string $message Exception message to show if value is not a callable.
     * @param  null   $propertyPath
     * @return void
     *@throws \DVD\DVDException If specified value is not a callable.
     *
     */
    public static function isCallable($value, $message = null, $propertyPath = null)
    {
        if (! is_callable($value)) {
            throw new DVDException($message, self::INVALID_CALLABLE, $propertyPath);
        }
    }
}
