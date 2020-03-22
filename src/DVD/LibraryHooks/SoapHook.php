<?php

namespace DVD\LibraryHooks;

use DVD\Util\Assertion;
use DVD\DVDException;
use DVD\Request;

/**
 * Library hook for curl functions.
 */
class SoapHook implements LibraryHook
{
    /**
     * @var callable
     */
    private static $requestCallback;

    /**
     * @var string
     */
    private $status = self::DISABLED;

    public function __construct()
    {
        if (!class_exists('\SoapClient')) {
            throw new \BadMethodCallException('For soap support you need to install the soap extension.');
        }

        if (!class_exists('\DOMDocument')) {
            throw new \BadMethodCallException('For soap support you need to install the xml extension.');
        }
    }

    /**
     * @param string $request
     * @param string $location
     * @param string $action
     * @param integer $version
     * @param int $one_way
     *
     * @return string SOAP response.
     *@throws \DVD\DVDException It this method is called although DVD is disabled.
     *
     */
    public function doRequest($request, $location, $action, $version, $one_way = 0, $options = array())
    {
        if ($this->status === self::DISABLED) {
            throw new DVDException('Hook must be enabled.', DVDException::LIBRARY_HOOK_DISABLED);
        }

        $dvdRequest = new Request('POST', $location);

        if ($version === SOAP_1_1) {
            $dvdRequest->setHeader('Content-Type', 'text/xml; charset=utf-8;');
            $dvdRequest->setHeader('SOAPAction', $action);
        } else { // >= SOAP_1_2
            $dvdRequest->setHeader(
                'Content-Type',
                sprintf('application/soap+xml; charset=utf-8; action="%s"', $action)
            );
        }

        $dvdRequest->setBody($request);

        if (!empty($options['login'])) {
            $dvdRequest->setAuthorization($options['login'], $options['password']);
        }

        /* @var \DVD\Response $response */
        $requestCallback = self::$requestCallback;
        $response = $requestCallback($dvdRequest);

        return $response->getBody();
    }

    /**
     * @inheritDoc
     */
    public function enable(\Closure $requestCallback)
    {
        Assertion::isCallable($requestCallback, 'No valid callback for handling requests defined.');
        self::$requestCallback = $requestCallback;

        if ($this->status == self::ENABLED) {
            return;
        }

        $this->status = self::ENABLED;
    }

    /**
     * @inheritDoc
     */
    public function disable()
    {
        if (!$this->isEnabled()) {
            return;
        }

        self::$requestCallback = null;

        $this->status = self::DISABLED;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled()
    {
        return $this->status == self::ENABLED;
    }

    /**
     * Cleanup.
     *
     * @return  void
     */
    public function __destruct()
    {
        self::$requestCallback = null;
    }
}
