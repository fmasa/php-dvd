<?php

namespace DVD;

class DVDFactory
{
    /**
     * @var Configuration
     **/
    protected $config;

    protected $mapping = array();

    protected static $instance;

    /**
     * Creates a new DVDFactory instance.
     *
     * @param Configuration $config
     */
    protected function __construct($config = null)
    {
        $this->config = $config ?: $this->getOrCreate('DVD\Configuration');
    }

    /**
     * @return Videorecorder
     */
    protected function createDVDVideorecorder()
    {
        return new Videorecorder(
            $this->config,
            $this->getOrCreate('DVD\Util\HttpClient'),
            $this
        );
    }

    protected function createStorage($cassetteName)
    {
        $dsn = $this->config->getCassettePath();
        $class = $this->config->getStorage();

        return new $class($dsn, $cassetteName);
    }

    protected function createDVDLibraryHooksSoapHook()
    {
        return new LibraryHooks\SoapHook();
    }

    protected function createDVDLibraryHooksCurlHook()
    {
        return new LibraryHooks\CurlHook();
    }

    /**
     * Returns the same DVDFactory instance on ever call (singleton).
     *
     * @param  Configuration $config (Optional) configuration.
     *
     * @return DVDFactory
     */
    public static function getInstance(Configuration $config = null)
    {
        if (!self::$instance) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * Returns an instance for specified class name and parameters.
     *
     * @param string $className Class name to get a instance for.
     * @param array $params Constructor arguments for this class.
     *
     * @return mixed An instance for specified class name and parameters.
     */
    public static function get($className, $params = array())
    {
        return self::getInstance()->getOrCreate($className, $params);
    }

    /**
     * Returns an instance for specified classname and parameters.
     *
     * @param string $className Class name to get a instance for.
     * @param array $params Constructor arguments for this class.
     *
     * @return mixed
     */
    public function getOrCreate($className, $params = array())
    {
        $key = $className . join('-', $params);

        if (isset($this->mapping[$key])) {
            return $this->mapping[$key];
        }

        if (method_exists($this, $this->getMethodName($className))) {
            $callback = array($this, $this->getMethodName($className));
            $instance =  call_user_func_array($callback, $params);
        } else {
            $instance = new $className;
        }

        return $this->mapping[$key] = $instance;
    }

    /**
     *
     * Example:
     *
     *   ClassName: \Tux\Foo\Linus
     *   Returns: createTuxFooLinus
     *
     * @param string $className
     *
     * @return string
     */
    protected function getMethodName($className)
    {
        return 'create' . str_replace('\\', '', $className);
    }
}
